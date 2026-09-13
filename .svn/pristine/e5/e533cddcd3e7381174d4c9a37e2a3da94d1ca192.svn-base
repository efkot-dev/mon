<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
	if (ini_get('precision')<20){ini_set('precision',20);}

	class MikroBILL_API {
		
		protected $Host;
		protected $Port;
		protected $Login;
		protected $Password;
		private $CRYPTO_KEY_1;
		private $CRYPTO_KEY_2;
		private $Stream;
		private $errno=-1;
		private $errstr='';
		
		const TIMEOUT = 5;
		
		public function __construct($_Host, $_Port, $_Login, $_Password, 
									$CRYPTO_KEY_1_ = NULL, $CRYPTO_KEY_2_ = NULL) {
			$this->Host = $_Host;
			$this->Port = $_Port;
			$this->Login = $_Login;
			$this->Password = $_Password;
			
			if ((is_null($CRYPTO_KEY_1_))||(is_null($CRYPTO_KEY_2_))){
			
				if (file_exists('..\config.php')){
					include '..\config.php';

					$this->CRYPTO_KEY_1 = $CRYPTO_KEY_1;
					$Res = mysql_query('SELECT `param_value` FROM `workparams` WHERE `param_name` = "CRYPTO_KEY_2";',$mysql);
					if (!$Res) {
						$this->errstr=mysql_error();
						$this->errno=mysql_errno();
					} else {
						$Res=mysql_fetch_row($Res);
						$this->CRYPTO_KEY_2 = $Res[0];
					}
				} else {
					$this->errstr='MikroBILL WEB config file: \'config.php\' not found!';
					$this->errno=5;
				}
			} else {
				$this->CRYPTO_KEY_1 = $CRYPTO_KEY_1_;
				$this->CRYPTO_KEY_2 = $CRYPTO_KEY_2_;
			}
		}
		
		public function Process($Path, $Value = NULL){
			
			if ($this->CheckConnection()){
				return $this->Send($Path,$Value);
			} else {
				$ret = json_encode(array('result' => $this->errstr,
										 'code' => $this->errno));
				$this->errstr='Unknown';
				$this->errno=0;
				return $ret;
			}
		}
		
		function Send($Path,$Value=NULL){
			$ret = array('path' => $Path, 
						 'value' => $Value);
			$ret = $this->Encrypt(json_encode($ret));
			fwrite($this->Stream, pack('I',strlen($ret)));
			fwrite($this->Stream, $ret);	
			
			return $this->Read();
		}
		
		function Read(){
			$l=fread($this->Stream,4);
			
			if (!$l===FALSE){
				$l=unpack('I',$l);
				$Received = 0;
				$ret='';
				$Started=time();
				while (($Received<$l[1])&&((time()-$Started)<self::TIMEOUT)){
					$Add=fread($this->Stream,$l[1]-$Received);
					$AL=strlen($Add);
					if ($AL>0){
						$ret .= $Add;
						$Received += $AL;
						$Started=time();
					}
				}
				return $this->Decrypt($ret);
			} else {return NULL;}
		}
		
		function CheckConnection(){
			
			if ((is_null($this->CRYPTO_KEY_1))||(is_null($this->CRYPTO_KEY_2))){return false;}
			
			$this->errstr='Unknown';$this->errno=2;
			if (is_null($this->Stream)){
				$MT=microtime();
				$this->Stream = fsockopen($this->Host, 
										  $this->Port, 
										  $this->errno, 
										  $this->errstr, 
										  self::TIMEOUT);

				if ($this->Stream) {
					socket_set_timeout($this->Stream, self::TIMEOUT);	
					
					$MT=microtime();
					$ret = array('auth'=>array('login' => $this->Login,
											   'password' => sha1($this->Password.$MT), 
											   'sign'=>$MT
											   )
								);
					$ret = $this->Encrypt(json_encode($ret));
					fwrite($this->Stream, pack('I',strlen($ret)));
					fwrite($this->Stream, $ret);	
					
					$Auth = $this->Read();
					
					if (strlen($Auth)>0){
						
						$Auth = json_decode($Auth);
						
						if ($Auth->code==0){
							return true;
						} else {
							$this->errno=$Auth->code;
							$this->errstr=$Auth->return;
							fclose($this->Stream);		
							$this->Stream = NULL;
							return false;
						}
					} else {
						$this->errno=6;
						$this->errstr='Null response! Probably incorrect encryption keys!';
						return false;
					}
					
				} else {
					$this->Close();
					$this->errstr='Can`t connect!';
					return false;
				}
			} 
			return true;
		}
		
		function Close(){
			if (!is_null($this->Stream)){
				$this->Send('DISCONNECT');
				fclose($this->Stream);		
				$this->Stream = NULL;
			}
		}
		
		function Encrypt($txt) {
			
			return mcrypt_encrypt (
			  MCRYPT_RIJNDAEL_128,
			  base64_decode($this->CRYPTO_KEY_1),
			  $txt,
			  MCRYPT_MODE_CBC,
			  base64_decode($this->CRYPTO_KEY_2)
			);
		}
		
		function Decrypt($txt) {
			
			return trim(mcrypt_decrypt(
			  MCRYPT_RIJNDAEL_128,
			  base64_decode($this->CRYPTO_KEY_1),
			  $txt,
			  MCRYPT_MODE_CBC,
			  base64_decode($this->CRYPTO_KEY_2)),
			  "\x00"
			);
		}
		
	}
	
?>