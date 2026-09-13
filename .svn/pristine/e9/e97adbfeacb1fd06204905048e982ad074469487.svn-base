<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
if(!defined('QUEUE')){
	die('Hacking attempt!');
}

class TelSimp
{
    protected $socket = null;
    protected $host;
    protected $port;
    protected $timeout = 10;
    protected $eol = "\n";

    public function connect(string $host, int $port = 23): static
    {
        $this->host = $host;
        $this->port = $port;

        if (filter_var($this->host, FILTER_VALIDATE_IP)) {
			$ip = $this->host;
		} else {
			$ip = gethostbyname($this->host);
			if ($ip === $this->host) {
				throw new Exception("Cannot resolve {$this->host}");
			}
		}
		$this->host = $ip;

        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
		if (!$this->socket) {
			throw new Exception("Cannot connect to {$this->host} on port {$this->port}");
		}

        return $this;
    }

    public function close(): static
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
        return $this;
    }

    public function send(string $text, bool $newLine = true): static
    {
        if ($this->socket === null) {
            throw new Exception("No connection. Call connectToHost() first.");
        }

        if ($newLine) {
            $text .= $this->eol;
        }

        fwrite($this->socket, $text);

        return $this;
    }
	public function readAll(int $timeout = 10): string
	{
		if ($this->socket === null) {
			throw new Exception("No connection.");
		}

		stream_set_timeout($this->socket, $timeout);
		$result = '';
		$startTime = time();

		while (!feof($this->socket)) {
			$c = fgetc($this->socket);
			if ($c === false) {
				break;
			}

			$result .= $c;

			if ((time() - $startTime) > $timeout) {
				throw new Exception("Timeout reached while reading from the socket.");
			}
		}

		return $result;
	}


    public function read(string $textToWait, int $timeout = 10): string
    {
        if ($this->socket === null) {
            throw new Exception("No connection.");
        }

        stream_set_timeout($this->socket, $timeout);
        $result = '';

        $start = time();
        while (true) {
            $c = fgetc($this->socket);
            if ($c === false) {
                break;
            }
            $result .= $c;

            if (strpos($result, $textToWait) !== false) {
                break;
            }

            if ((time() - $start) > $timeout) {
                throw new Exception("Timeout waiting for '$textToWait'");
            }
        }

        return $result;
    }
/*
    public function loginWithCredentials(string $username, string $password, string $userPrompt = 'login:', string $passPrompt = 'Password:', string $endPrompt = '#'): static
    {
        $this->readUntil($userPrompt);
        $this->sendText($username);
        $this->readUntil($passPrompt);
        $this->sendText($password);
        $this->readUntil($endPrompt);
        return $this;
    }
	*/
}
?>
