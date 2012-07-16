<?php
//The purpose of this class is to return a list of domains that a specific user has access to so they can assign allowed domains application items

class KinderSmilesAuth{

    protected $_auth;
    protected $_db;
    
    public function __construct($auth, $db){
    
        if ($auth INSTANCEOF Auth){
            $this->_auth = $auth;
        }
        else{
            throw new exception('Auth is not defined');
        }
        if ($db INSTANCEOF DB_MySQL){
            $this->_db = $db;
        }
        else{
            throw new exception('You are not connected to a database');
        }
    }

    public function getSitesAllowed($authID){
        $sites = array();
        
        if (is_numeric($authID)){
        
            $query = sprintf("SELECT s.`itemID`, s.`title` FROM `sysSites` AS s INNER JOIN `sysUSites` AS us ON s.`itemID` = us.`siteID` AND us.`userID` = %d",
            (int)$authID
            );
            $res = $this->_db->query($query);
            if ($this->_db->valid($res)){
                while ($row = $this->_db->fetch_assoc($res)){
                    $sites[trim($row["itemID"])] = trim($row["title"]);
                }
            }
        }
        return $sites;
    }
}