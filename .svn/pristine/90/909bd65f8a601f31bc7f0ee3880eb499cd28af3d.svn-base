<?php
if(!defined('PONMONITOR')){
	die("Hacking attempt!");
}
class TemplateMonitor{ 	
	public $folder = 'style/tpl';
	public $template = null;
	public $copy_template = null;
	public $data = array ();
	public $block_data = array ();
	public $result = array ('info' => '','content' => '' );
	public $allow_php_include = true;
	public $include_mode = 'tpl';	
	public $template_parse_time = 0;
	public $lang = [];
	public function __construct() {
		global $lang;
		$this->lang = $lang;
	}	
	function set($name, $var) {		
		if( is_array( $var ) ) {
			if( count( $var ) ) {
				foreach ( $var as $key => $key_var ) {
					$this->set( $key, $key_var );
				}
			}
			return;
		}		
		#$var = str_replace(array("{", "["),array("_&#123;_", "_&#91;_"), $var);			
		$this->data[$name] = $var;		
	}
	function set_block($name, $var) {
		if( is_array( $var ) && count( $var ) ) {
			foreach ( $var as $key => $key_var ) {
				$this->set_block( $key, $key_var );
			}
		} else
			$var = str_replace(array("{", "["),array("_&#123;_", "_&#91;_"), $var);			
			$this->block_data[$name] = $var;
	}	
	function loadForm($tpl_name,$replacements = []){
		$template_file = $this->folder . "/" . $tpl_name;
		$form = '';
		if (!file_exists($template_file)) {
			return false;
		}
		$form = file_get_contents($template_file);
		foreach ($replacements as $key => $value) {
			$form = str_replace("{{" . $key . "}}", $value, $form);
		}				
		
		return $form;
	}

	function load_template(string $tpl_name): bool {
		$time_before = $this->get_real_time();
		$tpl_name = str_replace("\0", '', $tpl_name);
		$url = parse_url($tpl_name);
		$file_path = dirname($this->clear_url_dir($url['path']));
		$tpl_name = pathinfo($url['path'], PATHINFO_BASENAME);
		$tpl_name = totranslit($tpl_name);
		$type = pathinfo($tpl_name, PATHINFO_EXTENSION);
		$type = strtolower($type);		
		if ($type !== "tpl") {
			$this->template = "Not Allowed Template Name: " . str_replace(ROOT_DIR, '', $this->folder) . "/" . $tpl_name;
			$this->copy_template = $this->template;
			return false;
		}
		$tpl_name = ($file_path && $file_path !== ".") ? "$file_path/$tpl_name" : $tpl_name;
		$template_file = $this->folder . "/" . $tpl_name;

		if (!file_exists($template_file)) {
			$this->template = "Template not found: " . str_replace(ROOT_DIR, '', $this->folder) . "/" . $tpl_name;
			$this->copy_template = $this->template;
			return false;
		}
		$this->template = file_get_contents($template_file);
		$this->copy_template = $this->template;
		$this->template_parse_time += $this->get_real_time() - $time_before;
		return true;
	}
	function clear_url_dir($var) {
		if ( is_array($var) ) return "";
		$var = str_ireplace( ".php", "", $var );
		$var = str_ireplace( ".php", ".ppp", $var );
		$var = trim( strip_tags( $var ) );
		$var = str_replace( "\\", "/", $var );
		$var = preg_replace( "/[^a-z0-9\/\_\-]+/mi", "", $var );
		$var = preg_replace( '#[\/]+#i', '/', $var );
		return $var;	
	}
	function _clear() {
		$this->data = array ();
		$this->block_data = array ();
		$this->copy_template = $this->template;
	}
	function clear() {
		$this->data = array ();
		$this->block_data = array ();
		$this->copy_template = null;
		$this->template = null;
	}
	function global_clear() {
		$this->data = array ();
		$this->block_data = array ();
		$this->result = array ();
		$this->copy_template = null;
		$this->template = null;
	}	
	private function replaceLang() {
		if(preg_match_all('/\[lang:(.*?)\]/', $this->copy_template, $matches)) {
			foreach($matches[0] as $index => $match) {
				$lang_key = $matches[1][$index];
				$replacement = isset($this->lang[$lang_key]) ? $this->lang[$lang_key] : "!lang_empty:$lang_key";
				$this->copy_template = str_replace($match, $replacement, $this->copy_template);
			}
		}
	}
	function compile(string $tpl): void {
		$start_time = microtime(true);
		$this->replaceLang();
		if (!empty($this->block_data)) {
			$find_replace_pairs = [];
			foreach ($this->block_data as $key_find => $key_replace) {
				$find_replace_pairs[$key_find] = $key_replace;
			}
			$this->copy_template = strtr($this->copy_template, $find_replace_pairs);
		}
		$find_replace_pairs = [];
		foreach ($this->data as $key_find => $key_replace) {
			$find_replace_pairs[$key_find] = $key_replace;
		}
		$this->copy_template = strtr($this->copy_template, $find_replace_pairs);
		if (isset($this->result[$tpl])) {
			$this->result[$tpl] .= $this->copy_template;
		} else {
			$this->result[$tpl] = $this->copy_template;
		}
		$this->_clear();
		$this->template_parse_time += microtime(true) - $start_time;
	}
	function get_real_time() {
		list ( $seconds, $microSeconds ) = explode( ' ', microtime() );
		return (( float ) $seconds + ( float ) $microSeconds);
	}
}
?>