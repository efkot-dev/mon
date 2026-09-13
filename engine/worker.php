<?php
$logDir = ROOT_DIR . '/export/log/';
$logFile = $logDir . 'worker_' . date('Y_m_d') . '.log';
ini_set('log_errors', 1);
ini_set('error_log', $logFile);
require ROOT_DIR . '/vendor/autoload.php';
use Enqueue\Redis\RedisConnectionFactory;
use Enqueue\Consumption\QueueConsumer;
use Enqueue\Consumption\Result;
use Enqueue\Consumption\ChainExtension;
use Enqueue\Consumption\Extension\SignalExtension;
use Interop\Queue\Processor;
use Interop\Queue\Message;
use Interop\Queue\Context;

class TaskProcessor implements Processor
{
    private const BASE_PROCESS_LIMIT = 25;
    private const MIN_CPU_LOAD = 0.5;
    private const MAX_CPU_LOAD = 0.8;
    private const SLEEP_TIME = 1;

    public function __construct()
    {
    }

    private function calculateDynamicLimit(): int
    {
        $cpuCount = $this->getCpuCoreCount();
        $loadAvg = $this->getSystemLoadAvg();
        $activeProcesses = $this->getActiveProcessesCount();
        $remainingCapacity = max(0, $cpuCount - ceil($loadAvg[0])) + 15;
        if ($loadAvg[0] < self::MIN_CPU_LOAD) {
            $remainingCapacity += 5;
        }
        if ($loadAvg[0] > self::MAX_CPU_LOAD) {
            $remainingCapacity -= 5;
        }
        return max(1, min(self::BASE_PROCESS_LIMIT, $remainingCapacity));
    }

    private function canExecuteMoreProcesses(): bool
    {
        $dynamicLimit = $this->calculateDynamicLimit();
        $activeProcesses = $this->getActiveProcessesCount();
        if ($activeProcesses >= $dynamicLimit) {
            return false;
        }
        return true;
    }

    private function getActiveProcessesCount(): int
    {
        if (function_exists('posix_getpgid')) {
            $pid = getmypid();
            return posix_getpgid($pid) ? 1 : 0;
        }
        $command = "pgrep -f 'php poller.php' | wc -l";
        $processes = intval(shell_exec($command));
        return max(0, $processes);
    }

    private function getCpuCoreCount(): int
    {
        if (function_exists('shell_exec')) {
            $cores = intval(shell_exec("nproc"));
            if ($cores > 0) {
                return $cores;
            }
        }
        return 4;
    }

    private function getSystemLoadAvg(): array
    {
        if (function_exists('sys_getloadavg')) {
            return sys_getloadavg();
        }
        return [0, 0, 0];
    }

    private function buildCommand(string $type, int $id, ?int $switch): ?string
    {
        switch ($type) {
            case 'system':
                return "php poller.php --jobid $id";
            case 'monitor':
                return "php poller.php --jobid $id --switch $switch";
            case 'backup':
                return "php poller.php --jobid $id --switch $switch";
            case 'signal':
            case 'status':
            case 'worker':
                return "php poller.php --jobid $id --switch $switch";
            default:
                error_log("Unknown task type: $type");
                return null;
        }
    }

    private function executeCommand(string $command): void
    {
        echo $command . PHP_EOL;
        shell_exec($command . " > /dev/null 2>&1 &");
    }

    public function process(Message $message, Context $context): Result
    {
        try {
            $taskData = json_decode($message->getBody(), true);
            if (!isset($taskData['workid'], $taskData['type'])) {
                error_log('Invalid data received: ' . $message->getBody());
                return Result::reject('Invalid data received');
            }
            $workid = $taskData['workid'];
            $type = $taskData['type'];
            $device_id = $taskData['device_id'] ?? null;
            if (empty($workid)) {
                error_log('Empty or invalid "workid" field in message: ' . $message->getBody());
                return Result::reject('Empty or invalid "workid" field');
            }
            while (!$this->canExecuteMoreProcesses()) {
                sleep(self::SLEEP_TIME);
            }
            $command = $this->buildCommand($type, $workid, $device_id);
			if($command) {
               $this->executeCommand($command);
            } else {
                error_log("Unknown task type: $type for ID $id");
                return Result::reject("Unknown task type: $type");
            }
            return Result::ack();
        } catch (\Throwable $e) {
            error_log('Task processing error: ' . $e->getMessage());
            return Result::reject('Processing error: ' . $e->getMessage());
        }
    }
}
$redisIp = defined('REDIS_IP') ? REDIS_IP : '127.0.0.1';
$redisPort = defined('REDIS_PORT') ? REDIS_PORT : 6379;
$connectionFactory = new RedisConnectionFactory(['host' => $redisIp, 'port' => $redisPort]);
$context = $connectionFactory->createContext();
$queue = $context->createQueue('pmon_queue');
$consumer = new QueueConsumer($context, new ChainExtension([
    new SignalExtension(),
]));
$consumer->bind($queue, new TaskProcessor());
$consumer->consume();
?>
