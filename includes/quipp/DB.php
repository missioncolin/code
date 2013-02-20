<?php

class DB extends Quipp {

	/**
	 * returns a $result from a query if one exists for common 'get me all of the
	 * records from this table where itemID = this' queries
	 */
	function result_please($id, $table, $customSelect = false, $customWhere = false, $customOrder = false, $debug = false) 
	{ 		
		$selectList = "*";
		if ($customSelect) { 
			$selectList = $customSelect;
		}	
		
		$whereList = "WHERE sysStatus = 'active' AND sysOpen = '1'";
		if ($customWhere) { 
			$whereList = "WHERE $customWhere"; 
		} elseif ($id) {
			$whereList = "WHERE itemID = '$id'";
		}
		
		$orderList = "";
		if ($customOrder) {
			$orderList = "ORDER BY $customOrder";
		}
	
		$qry = "SELECT $selectList FROM $table $whereList $orderList;";

		$res = $this->query($qry);

		if ($this->valid($res)) {
			return $res;
		}
		return false;
	}

	
	/**
	 * returns the value of a single cell from the specified table
 	 */
	function return_specific_item($itemID, $whatTable, $fieldName, $emptyReturn = "--", $argCustomWhere = false, $debug = false) 
	{	
		$res = $this->result_please($itemID, $whatTable, $fieldName, $argCustomWhere, false, $debug);
		
		if ($res) {
			$tmp = $this->fetch_assoc($res);
			return $tmp[$fieldName];
		} elseif ($debug) {
			return $res;
		}
		return $emptyReturn;
	}

}
?>