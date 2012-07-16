<?php

class DB_MySQL extends DB {


	public $dblink;
	public $queries = array();
	public $now = 'NOW()';
	public $last_insert = '';
	public $query_time = 0;
	
	function __construct($host, $user, $pass, $db)
	{
		$this->dblink = mysql_connect($host, $user, $pass);
		$this->select_db($db);
	}
	
	
	function select_db($db)
	{
		mysql_select_db($db, $this->dblink);
	}
	
	
	function query($qry)
	{
		$time_start = microtime();
		
		array_push($this->queries, $qry);		
		$res			   = mysql_query($qry, $this->dblink);
		
		$time_end 		   = microtime();
		$qry_time 		   = $time_end - $time_start;
		$this->query_time += $qry_time;
		
		
		return $res;
	}
	
	
	function valid($res)
	{
		if (is_resource($res) && $this->num_rows($res) > 0) {
			return true;
		}
		return false;
	}
	
	
	function num_rows($res)
	{	
		return mysql_num_rows($res);
	}
	
	
	function insert_id()
	{
		return mysql_insert_id($this->dblink);	
	}
	
	
	function escape($str, $clean = false)
	{		
		if (get_magic_quotes_gpc()) { 
    		$str = stripslashes($str);
    	}    	
    	if (!is_numeric($str)) {
    		$str = mysql_real_escape_string($str, $this->dblink);    	
    	}
    	if ($clean == true) {
    		$str = clean($str, true);
    	}
    	return $str;	
	}
	
	
	function error()
	{
		if (mysql_error($this->dblink)) {
			return mysql_error($this->dblink);
		}
		return false;			
	}
	
	
	function close()
	{
		mysql_close($this->dblink);
	}
	
	
	
	function fetch_row($res)
	{
		return mysql_fetch_row($res);	
	}
	
	
	function fetch_array($res, $numeric = true)
	{
		if ($numeric) {
			return mysql_fetch_array($res);	
		} else {
			return $this->fetch_assoc($res);
		}	
	}
	
	
	function fetch_assoc($res)
	{
		return mysql_fetch_assoc($res);	
	}
	
	function affected_rows($res) {
	
		return mysql_affected_rows($this->dblink);

	}
	public function result_please($id, $table, $customSelect = false, $customWhere = false, $customOrder = false, $debug = false){
		return parent::result_please($id, $table, $customSelect, $customWhere, $customOrder, $debug);
	}
}

?>