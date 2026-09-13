<?php
if(!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class PMonTelnet{
    private $use_usleep = 0;
    private $sleeptime = 500;
	private $switch_password;
    private $fp = NULL;
    public $err_num = 0; 
    private $show_rez = true;
    private $cmd_open_connect = array('ff', 'fd', '03', 'ff', 'fb', '18', 'ff', 'fb', '1f',
        'ff', 'fb', '20', 'ff', 'fb', '21', 'ff', 'fb', '22',
        'ff', 'fb', '27', 'ff', 'fd', '05', 'ff', 'fb', '23');
    private $cmd_term_type = array('ff', 'fa', '18', '00', '78', '74', '65', '72',
        '6d', '2d', '32', '35', '36', '63', '6f', '6c', '6f', '72', 'ff', 'f0');
    private $cmd_establ_connect = array('ff', 'fd', '03', 'ff', 'fb', '01');
    private $telnet;
    private $temp = [];
    private $logger;
    private $lang;
    private $id;
	
    public function __construct($switch) {
		global $logger, $lang;
		$this->logger = $logger;
		$this->lang = $lang;
		$this->id = $switch['id'];
		$this->switch_password = $switch['password'];
        if (strlen($switch['netip'])) {
            if (preg_match('/[^0-9.]/',$switch['netip'])) {
                $ip = gethostbyname($switch['netip']);
				if($ip == $switch['netip']) {
                    if (strpos($ip, ':') !== false) {
						list($ip, $port_temp) = explode(':', $ip);
					} else {
						$this->err_num = 2;
					}                    
                }
            } else {
                $ip = $switch['netip'];
            }
        } else {
            $ip = '127.0.0.1';
            $this->err_num = 1;
        }
		$port = (!empty($switch['telnet_port']) ? $switch['telnet_port'] : 23 );
        $this->connect($ip,$port);
        if (!$this->err_num) {
			$login = $this->do_comand($switch['username']."\r", true);
			if (preg_match('/name:/', $login)) {
				$login = $this->do_comand($switch['username']."\r", true);
			}
            $aut = $this->do_comand($switch['password']."\r", true);
			$this->temp[] = $aut;
			$pattern = '/Authentication\s+failed/i';
			$zte6 = '/password\s+error/i';
			$zte6lock = '/User\s+is\s+locked/i';
			if(preg_match($zte6lock,$aut)) {
				$this->err_num = 8;
			}elseif(preg_match($pattern,$aut) || preg_match($zte6,$aut)) {
				$this->err_num = 3;
			}else{
				return true;
			}
        }
    }
    public function getTemp() {
		return $this->temp;
	}
    public function descr($cmd) {
		return match($cmd) {
			1 => 'host not found',
			2 => 'connetion abort',
			3 => 'authentication failed',
			4 => 'end session',
			8 => 'user is locked',
			9 => 'connection close',
			default => die('not support '.$cmd),
		};
	}
    public function do_comand($cmd, $return = false) {
		if (!$this->fp) {
			$this->err_num = 2;
			return;
		}
		if (feof($this->fp)) {
			return;
		}
		$result = @fputs($this->fp, $cmd);
		if ($result === false) {
			$this->err_num = 4;
			return;
		}
		sleep(1);
		if ($this->show_rez && $return) {
			return $this->get_response();
		}
	}   
	public function do_lite($cmd, $return = false) {
        if (!$this->fp) {
            $this->err_num = 2;
            return;
        }
        fputs($this->fp, $cmd);
        if ($this->show_rez && $return) {
            return $this->get_response();
        }
    }
    public function get_response($do_decode = false) {
        $r = '';
        do {
            $r .= fread($this->fp, 16384);
            $s = socket_get_status($this->fp);
        } while ($s['unread_bytes']);
        if ($do_decode) {
            return $this->decode_comand($r);
        } else {
			$this->temp[] = $r;
            return $r;
        }
    }
	private function decode_comand($response) {
		$lines = explode("\n", $response);
		$this->temp[] = $lines;
		$r = [];
		foreach ($lines as $line) {
			$r[] = bin2hex($line);
		}
		return $r;
	}
	private function decode_comand_($response) {
		$n = 1;
		$r = [];
		for ($i = 0, $length = strlen($response); $i < $length; $i++) {
			$tmp = dechex(ord($response[$i]));
			if ($tmp === 'ff') {
				$n++;
				$r[$n] = '';
			} else {
				if (!isset($r[$n])) {
					$r[$n] = '';
				}
				$r[$n] .= $tmp;
			}
		}
		return $r;
	}
    private function decode_comandos($response) {
        $n = 1;
        $r = [];
		$r[1] = [];
        for ($i = 0; $i < strlen($response); $i++) {
            $tmp = dechex(ord($response[$i]));
            if ($tmp == 'ff') {
                $n++;
		$r[$n] = '';
            } else {
                $r[$n] .= $tmp;
            }
        }
        return $r;
    }
    public function code_comand($code) {
        $cmd = '';
        foreach ($code as $value) {
            $cmd .= chr(hexdec($value));
        }
        return $cmd;
    }
    private function connect($host,$port) {
        $this->fp = @fsockopen($host,$port);
		#$this->fp = fsockopen($this->host, $this->port, $this->errno, $this->errstr, $this->timeout);
		if ($this->fp){
            $this->do_comand($this->code_comand($this->cmd_open_connect));
            $res = $this->decode_comand(fread($this->fp, 8192));
            if (in_array('fa181', $res)) {
                $this->do_comand($this->code_comand($this->cmd_term_type));
            }
            $this->do_comand($this->code_comand($this->cmd_establ_connect));
        } else {
            $this->err_num = 9;
        }
    }
    public function disconnect($cmd_exit='') {
        if ($cmd_exit) $this->do_comand ($cmd_exit."\r");
        if ($this->fp) {
            fclose($this->fp);
            $this->fp = NULL;
        }
    }
    public function Sleep() {
        if ($this->use_usleep) {
            usleep($this->sleeptime);
        } else {
            sleep(1);
        }
    }
	public function err($commands) {
		$this->logger->init(['log'=>'device','type'=>'errtelnet','descr'=>'Error '.($commands?' - '.$commands:''),'deviceid'=>$this->id,'who'=>'cron']);
		$error_message = "<div class=\"telnet_error_cmd\">".$this->lang['errtelnet'].": ".$commands."</div>";
		echo $error_message;
		die;
	}
	private function onPasswordFound() {
       return $this->do_comand($this->switch_password."\r", true);
    }
	public function executeCommands($commands) {
		try {
			$output = "";
			foreach ($commands as $cmd) {
				$output .= $this->do_comand($cmd."\r",true);
				$this->temp[] = $output;
				if(stripos($output, 'password') !== false) {
                    $output .= $this->onPasswordFound();
					$this->temp[] = $output;
                }
				sleep(1);
			}
			return $output;
		} catch (Exception $e) {
			throw new Exception("Error executing commands: " . $e->getMessage());
		}
	}	
	public function go($commands) {
		try {
			$output = "";
			foreach ($commands as $cmd) {
				$output .= $this->do_comand($cmd."\r",true);
				$this->temp[] = $output;
			}
			return $output;
		} catch (Exception $e) {
			throw new Exception("Error executing commands: " . $e->getMessage());
		}
	}
	public function parser_result($serch,$text) {
		$matches = array();
		preg_match('/'.$serch.'([\s\S]*)/i', $text, $matches);
		if (count($matches) > 0) {
			return $matches[1];
		} else {
			return '';
		}
	}
}
?>
