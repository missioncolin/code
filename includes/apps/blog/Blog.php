<?php
class Blog{
    
    protected $_db;
    protected $_siteID;
    protected $_status;
    
    public function __construct($db, $siteID, $status){
        if (is_object($db) && $db INSTANCEOF DB_MySQL){
            
            $this->_db = $db;
            $this->_siteID = (int)$siteID;
            $this->_status = $this->_db->escape($status);
        
        }
        else{
            throw new Exception("You are not connected to a database");
        }
    }

    public function getPostList($offset, $max){
        
        $limit = "LIMIT ".$offset.",".$max;
        
        $qry = sprintf("SELECT `title`, `lead_in`, UNIX_TIMESTAMP(`displayDate`) as `displayDate`, `slug`, `itemID`, (SELECT count(`itemID`) FROM `tblNews` WHERE `approvalStatus` = '1' AND `sysOpen` = '1' AND `sysStatus` = '%s' AND `type` = 'blog' AND `siteID` = %d) AS count 
        FROM `tblNews` WHERE `approvalStatus` = '1' AND `sysOpen` = '1' AND `sysStatus` = '%s' AND `type` = 'blog' AND `siteID` = %d ORDER BY 
        UNIX_TIMESTAMP(`displayDate`) DESC %s",
            $this->_status,
            (int)$this->_siteID,
            $this->_status,
            (int)$this->_siteID,
            $limit
        );
        
        $res = $this->_db->query($qry);
        if ($this->_db->valid($res)){
            $data = array();
            while ($row = $this->_db->fetch_assoc($res)){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getFullPost($slug){
    
        $condition = ($slug == "latest")?"ORDER BY UNIX_TIMESTAMP(`displayDate`) DESC":"AND `slug` = '".$this->_db->escape($slug)."'";
        
        $qry = sprintf("SELECT * FROM `tblNews` WHERE `sysOpen` = '1' AND `type` = 'blog' AND `sysStatus` = '%s' AND `approvalStatus` = '1' AND `siteID` = %d %s LIMIT 0,1",
            $this->_status,
            $this->_siteID,
            $condition
        );

        $res = $this->_db->query($qry);
        if ($this->_db->valid($res)) {
            return $this->_db->fetch_assoc($res);
        } else {
            return false;
        }
    }
    
    public function getRecentPosts($offset, $limit){
    
        $condition = (is_numeric($offset) && is_numeric($limit))?sprintf("LIMIT %d,%d",(int)$offset,(int)$limit):"";
            
        $qry = sprintf("SELECT `title`, UNIX_TIMESTAMP(`displayDate`) as `displayDate`, `slug`, `lead_in` FROM `tblNews` WHERE `sysOpen` = '1' AND `type` = 'blog' AND `sysStatus` = '%s' AND `approvalStatus` = '1' AND `siteID` = %d AND UNIX_TIMESTAMP(`displayDate`) >= %d ORDER BY UNIX_TIMESTAMP(`displayDate`) DESC %s",
            $this->_status,
            $this->_siteID,
            strtotime("1 month ago"),
            $condition
        );
        
        $res = $this->_db->query($qry);
        if ($this->_db->valid($res)){
            
            while ($row = $this->_db->fetch_assoc($res)){
                $toReturn[] = $row;
            }
            
            return $toReturn;
        }
            
        return false;
    }
    public function getPostArchive(){
        $qry = sprintf("SELECT `title`, UNIX_TIMESTAMP(`displayDate`) as `displayDate`, `slug`, `category`,`lead_in` FROM `tblNews` WHERE `sysOpen` = '1' AND `type` = 'blog' AND `sysStatus` = '%s' AND `approvalStatus` = '1' AND `siteID` = %d AND UNIX_TIMESTAMP(`displayDate`) < %d ORDER BY UNIX_TIMESTAMP(`displayDate`) DESC",
            $this->_status,
            $this->_siteID,
            strtotime("1 month ago")
        );
        
        $res = $this->_db->query($qry);
        if ($this->_db->valid($res)){
        
            while ($row = $this->_db->fetch_assoc($res)){
                
                $mainIndex = (trim($row["category"]) != "")?trim($row["category"]):date("F Y",trim($row["displayDate"]));
                $toReturn[$mainIndex][] = $row;               
            }
            return $toReturn;
        
        }
        return false;
    }
    public function getArchiveByCategory($category){
        $condition = ($category == "recent")?"UNIX_TIMESTAMP(`displayDate`) >= ".strtotime("1 month ago"):" `category` = '".$this->_db->escape($category)."'";
        
        $qry = sprintf("SELECT `title`, UNIX_TIMESTAMP(`displayDate`) as `displayDate`, `slug`, `category`,`author`, `lead_in` FROM `tblNews` WHERE `sysOpen` = '1' AND `type` = 'blog' AND `sysStatus` = '%s' AND `approvalStatus` = '1' AND `siteID` = %d AND %s ORDER BY UNIX_TIMESTAMP(`displayDate`) DESC",
            $this->_status,
            $this->_siteID,
            $condition
        );
        
        $res = $this->_db->query($qry);
        if ($this->_db->valid($res)){
            while ($row = $this->_db->fetch_assoc($res)){
                $toReturn[] = $row;
            }
            return $toReturn;
        }
        return false;
        
    }
    public function getArchiveByDate($dateTime){
        if (preg_match("%^[0-9]{5,}$%",$dateTime,$matches)){
            $qry = sprintf("SELECT `title`, UNIX_TIMESTAMP(`displayDate`) as `displayDate`, `slug`, `lead_in`, `author` FROM `tblNews` WHERE `sysOpen` = '1' AND `type` = 'blog' AND `sysStatus` = '%s' AND `approvalStatus` = '1' AND `siteID` = %d AND  UNIX_TIMESTAMP(`displayDate`) < %d ORDER BY UNIX_TIMESTAMP(`displayDate`) DESC",
                $this->_status,
                $this->_siteID,
                $dateTime
            );
            
            $res = $this->_db->query($qry);
            if ($this->_db->valid($res)){
                while ($row = $this->_db->fetch_assoc($res)){
                    $toReturn[] = $row;
                }
                return $toReturn;
            }
        }
        return false;
    }
}