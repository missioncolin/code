<?php

class DB_MsSQL extends DB {


	public $dblink;
	public $queries = array();
	public $now = 'GETDATE()';
	public $last_insert = ' SELECT SCOPE_IDENTITY() AS IDENTITY_COLUMN_NAME;';
	public $query_time = 0;
	
	function __construct($host, $user, $pass, $db)
	{
		$connectionInfo = array(
			"Database" => $db, 
			"UID" 	   => $user, 
			"PWD" 	   => $pass
		);
		return sqlsrv_connect($host, $connectionInfo);
	}
	
	
	function query($qry)
	{
		
		
		$time_start = microtime();
		
		array_push($this->queries, $qry);
		$res			   = sqlsrv_query($this->dblink, $qry);
		
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
		$i = 0;
		while ($z = sqlsrv_fetch_array($res)) {
			$i++;
		}
		if ($i > 0) {
			return $i;
		} 		
		return false;
	}
	
	
	function insert_id($res)
	{
		sqlsrv_next_result($res);
		sqlsrv_fetch($res);
		return sqlsrv_get_field($res, 0);	
	}
	
	
	function escape($str)
	{		
		if (get_magic_quotes_gpc()) { 
    		$str = str_replace("''", "'", $str);
    	}    	
    	if (!is_numeric($str)) {
    		$str = str_replace("'", "''", $str);	
    	}
    	return $str;	
	}
	
	
	function error()
	{
		$return = '';
		if (($errors = sqlsrv_errors()) != null) {
			foreach ($errors as $error) {
            	$return .= "SQLSTATE: " . $error[ 'SQLSTATE'] . "<br />";
            	$return .= "Code: " . $error[ 'code'] . "<br />";
           		$return .= "Message: " . $error[ 'message'] . "<br />";
         	}
      	}
      	return $return;
	}
	
	
	function close()
	{
		sqlsrv_close($this->dblink);
	}
	
	
	
	function fetch_row($res)
	{
		return sqlsrv_fetch_array($res);
	
	}
	
	
	function fetch_array($res, $numeric = false)
	{
		if (!$numeric) {
			return sqlsrv_fetch_array($res);
		} else {
			return sqlsrv_fetch_array($res, SQLSRV_FETCH_NUMERIC);
		}	
	}
	
	
	function fetch_assoc($res)
	{
		return sqlsrv_fetch_array($res);
	}
	
	
	function affected_rows($res) {
	
		return sqlsrv_rows_affected($res);

	}
}

?>