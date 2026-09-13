<?php
if (!defined('PONMONITOR') && !defined('CURL')) {
    die("Hacking attempt!");
}

class CurlPool {
    private int $maxConcurrent;
    private $multiHandle;
    private array $pool = [];
    private array $active = [];
    private array $responses = [];

    public function __construct(int $maxConcurrent = 20) {
        $this->maxConcurrent = $maxConcurrent;
        $this->multiHandle = curl_multi_init();
    }

    public function addRequest(string $url, array $postData, int|string $key): void {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
        ]);

        $this->pool[$key] = $ch;
    }

    public function executeAll(): array {
        $this->responses = [];

        // Додаємо початкові maxConcurrent запити
        while (count($this->active) < $this->maxConcurrent && !empty($this->pool)) {
            $key = array_key_first($this->pool);
            $ch = $this->pool[$key];
            curl_multi_add_handle($this->multiHandle, $ch);
            $this->active[$key] = $ch;
            unset($this->pool[$key]);
        }

        $running = null;
        do {
            do {
                curl_multi_exec($this->multiHandle, $running);
            } while (curl_multi_select($this->multiHandle, 1) > 0);

            // обробка завершених
            while ($info = curl_multi_info_read($this->multiHandle)) {
                $ch = $info['handle'];
                $key = array_search($ch, $this->active, true);

                if ($key !== false) {
                    $response = curl_multi_getcontent($ch);
                    $this->responses[$key] = json_decode($response, true);

                    curl_multi_remove_handle($this->multiHandle, $ch);
                    curl_close($ch);
                    unset($this->active[$key]);

                    // Підставляємо наступний запит
                    if (!empty($this->pool)) {
                        $nextKey = array_key_first($this->pool);
                        $nextCh = $this->pool[$nextKey];
                        curl_multi_add_handle($this->multiHandle, $nextCh);
                        $this->active[$nextKey] = $nextCh;
                        unset($this->pool[$nextKey]);
                    }
                }
            }
        } while (!empty($this->active));

        return $this->responses;
    }

    public function closeAll(): void {
        foreach ($this->active as $ch) {
            curl_multi_remove_handle($this->multiHandle, $ch);
            curl_close($ch);
        }
        foreach ($this->pool as $ch) {
            curl_close($ch);
        }
        curl_multi_close($this->multiHandle);

        $this->active = [];
        $this->pool = [];
    }
}
?>
