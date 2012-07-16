<?php

class Testimonials{

    protected $_db;
    
    public function __construct($db){
        if ($db INSTANCEOF DB_MySQL){
            $this->_db = $db;
        }
        else{
            throw new Exception("You are not connected to a database");
        }
    }
    public function getTestimonialsList($siteID,$isAdmin){
        $info = false;
        if (is_numeric($siteID) && (int)$siteID > 0){

            $viewAll = ($isAdmin === true)?"":" AND `sysStatus` = 'active'";
            $qry = sprintf("SELECT `itemID`, `name`, `comment`, `emailAddress`, `siteID` FROM `tblTestimonials` WHERE `siteID` = %d AND `sysOpen` = '1' %s",
                (int)$siteID,
                $viewAll
            );
            
            $res = $this->_db->query($qry);
            if ($this->_db->valid($res)){
                $t = 0;
                while($row = $this->_db->fetch_assoc($res)){
                    foreach($row as $key => $value){
                        $info[$t][$key] = $value;
                    }
                    $t++;
                }
            }
        }
        return $info;
    }   
    public function getTestimonialsByID($itemID,$isAdmin){
        if (is_numeric($itemID) && (int)$itemID > 0){

            $preview = ($isAdmin === true)?"":" AND `sysOpen` = '1'";
            $qry = sprintf("SELECT `itemID`, `name`, `comment`, `emailAddress`, `siteID` FROM `tblTestimonials` WHERE `itemID` = %d AND `sysStatus` = 'active' %s",
                (int)$itemID,
                $preview
            );
            
            $res = $this->_db->query($qry);
            if ($this->_db->valid($res)){
                while($row = $this->db->fetch_assoc($res)){
                    return $row; 
                }
            }
        }
        return false;
    }
}