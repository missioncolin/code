<?php

class Forms{
    
    protected $_db;
    
    public function __construct($db){
        if ($db INSTANCEOF DB_MySQL){
            $this->_db = $db;
        }
        else{
            throw new Exception("You are not connected to a database");
        }

    }
    
    
    public function getMetaFieldsByGroup($groupName){
        $meta = array();
        $qry = sprintf("SELECT DISTINCT f.itemID, f.fieldLabel, f.validationCode 
            FROM sysUGFields AS f
            INNER JOIN sysUGFLinks as fglinks ON(f.itemID = fglinks.fieldID)
            INNER JOIN sysUGroups AS g ON fglinks.`groupID` = g.`itemID`
			WHERE f.sysOpen = '1' 
			AND g.`nameSystem` = '%s'
			ORDER BY f.myOrder ASC;",
				$this->_db->escape($groupName, true)
				);
		$res = $this->_db->query($qry);
		
		if ($this->_db->valid($res)){
    		while ($row = $this->_db->fetch_assoc($res)){
        		$meta[] = $row;
    		}
		}
		
		return $meta;
    }
}