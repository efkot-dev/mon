<?php
if (!defined('PONMONITOR')){
	die('Hacking attempt!');
}
class DB{
    private $_mysqli;
    private $_result;
	private $errors = array();	
	public $_query_list = [];
    private $logDir;
    private $logFile;	
	private $_query_num = 0;
	public function __construct() {
        $this->_mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
        $this->_mysqli->set_charset('utf8');			
    }    
    public function __destruct() {
        if ($this->_mysqli) {
            @$this->_mysqli->close();
        }
    }    
	public function get_ip() {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$clientIP = trim($ips[0]);
		} elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
			$clientIP = $_SERVER['HTTP_X_REAL_IP'];
		} else {
			$clientIP = $_SERVER['REMOTE_ADDR'];
		}		
		return $clientIP;
	}
	private function prepareQuery(string $sql, $params = []): string {
		if (empty($params)) return $sql;
		$params = is_array($params) ? array_map([$this, 'escapeSimple'], $params) : [$this->escapeSimple($params)];
		return vsprintf($sql, $params);
	}	
	public function NumRows($sql){
		$this->_result = $this->_mysqli->query($sql);
		if (!$this->_result) return false;
        if ($this->getNumRows() != 1) return false;
        $data = $this->processFetch();
        $this->freeResult();
        return $data;
    }	
	private function processQuery(string $sql): void {
		$this->_result = null;
		$time_before = $this->get_real_time();
		$this->_result = $this->_mysqli->query($sql);
		$time_after = $this->get_real_time();
		$execution_time = number_format($time_after - $time_before, 3, '.', '');
		$this->_query_list[] = [
			'time'  => $execution_time,
			'query' => $sql,
			'num'   => ($this->_query_num + 1)
		];
		$this->_query_num++;
	}
    private function escapeSimple($str){
        if(is_array($str)) {
            return array_map([$this, 'escapeSimple'], $str);
        }
        return $str !== null ? $this->_mysqli->real_escape_string($str) : '';
    }	    
    private function processFetch(){
        return $this->_result->fetch_assoc();
    }
    public function freeResult() {
        $this->_result->free();
    }
    public function debugSql(){
        return $this->_query_list;
    }
    public function getNumRows(){
        return $this->_result->num_rows;
    }
	public function getAffRows(){
		return $this->_mysqli->affected_rows;
	}
    public function getInsertId(){
        return $this->_mysqli->insert_id;
    }
    public function Fast($table, $columns = '*', $conditions = null, $sorting = null, $limit = null, $offset = null){
		$this->SQLselect($table, $columns, $conditions, $sorting, $limit, $offset);
		return $this->getFast();
	}   
	public function Multi($table, $columns = '*', $conditions = null, $sorting = null, $limit = null, $offset = null){
		$this->SQLselect($table, $columns, $conditions, $sorting, $limit, $offset);
		return $this->getWhile();
	}	
	public function Simple($sql){
		$this->query($sql);
		return $this->getSimple();
	} 	
	public function SimpleWhile($sql){
		$this->SimpleQuery($sql);
		return $this->getDataCol();
	}    
    public function getWhile(){
        if (!$this->_result) return false;
        $data = array();
        while ($row = $this->processFetch()) {
            $data[] = $row;
        }
        $this->freeResult();
        return $data;
    }
    public function getFast(){
        if (!$this->_result) return false;
        if ($this->getNumRows() != 1) return false;
        $data = $this->processFetch();
        $this->freeResult();
        return $data;
    }   
	public function getSimple(){
        if (!$this->_result) return false;
        if ($this->getNumRows() != 1) return false;
        $data = $this->processFetch();
        $this->freeResult();
        return $data;
    }
    public function getDataCol(){
        if (!$this->_result) return false;
        $data = array();
        while ($row = $this->processFetch()) {
			$data[] = $row;
        }
        $this->freeResult();
        return $data;
    }
    public function getDataCell(){
        if (!$this->_result) return false;
        if ($this->getNumRows() != 1) return false;
        $data = array_values($this->processFetch());
        $this->freeResult();
        return $data[0];
    }
    public function query($sql, $params = array()){
        $query = $this->prepareQuery($sql, $params);
        $this->processQuery($query);
    }   
	public function SimpleQuery($sql){
        $this->processQuery($sql);
    }	
	public function SimpleNumRows($sql) {
		$this->_result = $this->_mysqli->query($sql);
		return $this->_result->num_rows ?? false;
	}
    public function SQLselect($table, $columns = '*', $conditions = [], $sorting = [], $limit = null, $offset = null) {
		$query = sprintf(
			"SELECT %s FROM %s%s%s%s",
			$this->prepareColumns($columns),
			$this->prepareTable($table, 'select'),
			$this->prepareConditions($conditions),
			$this->prepareSorting($sorting),
			$this->prepareLimit($limit, $offset)
		);
		$this->processQuery($query);
	}
    public function SQLinsert($table, $data){
        $table = $this->prepareTable($table,'insert');
        $data = $this->prepareData($data);
        $query = "INSERT INTO {$table} SET {$data}";
        $this->processQuery($query);
    }
	public function SQLupdate($table, $data, $conditions = null) {
		$table = $this->prepareTable($table,'update');
		$data = $this->prepareData($data);
		$where = $this->prepareConditions($conditions);
		$query = "UPDATE {$table} SET {$data}";
		if (!empty($where)) {
			$query .= $where;
		}
		$this->processQuery($query);
	}
    public function SQLdelete($table, $conditions = null){
        $table = $this->prepareTable($table,'delet');
        $where = $this->prepareConditions($conditions);
        $query = "DELETE FROM {$table}";
        $query .= !empty($where) ? $where : '';
        $this->processQuery($query);
    }
    private function prepareTable($table,$type){
        if (empty($table)) {
            die('empty_table_'.$type);
        }
        $table = "`{$this->escapeSimple($table)}`";
        return $table;
    }
	private function prepareColumns($columns){
		if ($columns == '*') return $columns;
		if (empty($columns)) {
			die('Empty columns');
		}
		if (!is_array($columns)) {
			$columns = explode(',', $columns);
		}
		$columns = array_map('trim', $columns);
		$columns = array_map([$this, 'escapeSimple'], $columns);
		return '`' . implode('`, `', $columns) . '`';
	}
	private function parserRe($text) {
		$quotes = [
			'"', "'", "\x60", "union", "select", "script", "SELECT", "LEFT", "UNION", 
			"\t", "\n", "\r", "=", "*", "^", "%", "$", "<", ">", "\n", "\'"
		];		
		return str_ireplace($quotes, '', $text);
	}
	private function prepareData($data) {
		if (empty($data)) {
			die('Empty data');
		}		
		if (!is_array($data)) {
			throw new Exception('Data must be array');
		}
		$dataStr = '';
		$comma = '';
		foreach ($data as $param => $value) {
			$value = $this->parserRe($value);
			$dataStr .= "{$comma}`{$this->escapeSimple($param)}` = '{$this->escapeSimple($value)}'";
			$comma = ', ';
		}		
		return $dataStr;
	}

    private function prepareConditions($conditions){
        $where = '';
        if (!isset($conditions)) return $where;
        if (is_array($conditions)) {
            $and = '';
            foreach ($conditions as $param => $value) {
                $where .= "{$and}`{$this->escapeSimple($param)}` = '{$this->escapeSimple($value)}'";
                $and = " AND ";
            }
            $where = " WHERE {$where}";
        } else {
            die('Conditions must be array');
        }
        return $where;
    }
	private function prepareSorting($sorting) {
		if($sorting!=false){
			$sorting = $this->validateArray($sorting, 'Orders must be array');
			$order = array_map(function($param, $value) {
				return sprintf("`%s` %s", $this->escapeSimple($param), strtoupper($value) === 'DESC' ? 'DESC' : 'ASC');
			}, array_keys($sorting), $sorting);
			return $order ? " ORDER BY " . implode(', ', $order) : '';
		}
		return '';
	}
	private function validateArray($value, $message) {
		if (!is_array($value)) {
			die($message);
		}
		return $value;
	}
    private function prepareLimit($rows, $offset = null) {
        $limit = '';
        if (isset($offset)) {
            $rows = intval($rows);
            if (isset($offset)) {
                $offset = intval($offset);
                $limit = " LIMIT {$offset}, {$rows}";
            } else {
                $limit = " LIMIT {$rows}";
            }
        }
        return $limit;
    }
	private function get_real_time(){
        list($seconds, $microSeconds) = explode(' ', microtime());
        return ((float)$seconds + (float)$microSeconds);
    }
}
$db = New DB();
?>