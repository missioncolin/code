<?php
require_once(dirname(dirname(__DIR__)) ."/quipp/User.php");
class Forms extends User{
    
    
    public $mimeTypes = array(
        'image/jpeg'   => 'jpg',
        'image/pjpeg'  => 'jpg',
        'image/png'   => 'png'
    );

    public $vMimeTypes = array(
        'video/mp4'   => 'mp4'
    );

    public $thumbnails = array(
        'med'     => array(
            'l'        => 80,
            'w'        => 80,
            'adaptive' => false
        ),
        'small' => array(
            'l'        => 48,
            'w'        => 48,
            'adaptive' => false
        )
    );
    
    public function __construct($db, $userID = false){
        if ($db INSTANCEOF DB_MySQL){
            parent::__construct($db, $userID);
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
				$this->db->escape($groupName, true)
				);
		$res = $this->db->query($qry);
		
		if ($this->db->valid($res)){
    		while ($row = $this->db->fetch_assoc($res)){
        		$meta[] = $row;
    		}
		}
		
		return $meta;
    }

    protected function setUserGroup($groupName){
        $groupID = $this->db->return_specific_item(false, 'sysUGroups', 'itemID',0, "`nameSystem` = '".$this->db->escape($groupName,true)."'");
        if ($groupID > 0 && $this->id > 0){
            $this->db->query(sprintf("INSERT INTO `sysUGLinks` SET `userID` = '%d', `groupID` = '%d', `sysStatus` = 'active', `sysOpen` = '1'",
                (int)$this->id,
                (int)$groupID
            ));
        }
    }
    
    public function getUserGroups(){
        $group = array();
        if ((int)$this->id > 0){
            $res = $this->db->query(sprintf("SELECT g.`nameFull`, g.`nameSystem`, g.`itemID` 
            FROM `sysUGroups` AS g 
            INNER JOIN `sysUGLinks` AS ug ON g.`itemID` = ug.`groupID` 
            WHERE ug.`userID` = %d",
                $this->id
            ));
            if ($this->db->num_rows($res) > 0){
                while ($row = $this->db->fetch_assoc($res)){
                    $group[] = $row;
                }
                
            }
        }
        return $group;
    }
    
    public function createUserAccount($post, $password, $groupName){
    
        $insID = 0;
        
        $qry = sprintf("INSERT INTO `sysUsers` (`userIDField`, `userIDPassword`, `regDate`, `sysUser`, `sysIsADUser`, `sysStatus`, `sysOpen`) VALUES ('%s', '%s', NOW(), '0', '0', 'active', '1')" ,
            $this->db->escape($post["Email"]["value"], true),
            MD5($this->db->escape($password, true))
        );

        $res = $this->db->query($qry);
        $inID = 0;
        if ($this->db->affected_rows($res) == 1){
            $insID = $this->db->insert_id();
            $this->id = $insID;
            $this->setUserGroup($groupName);
            foreach($post as $key => $data){
                $this->set_meta($data["label"], $data["value"]);
            }
        }
        else{
            global $message;
            $message = '<li>'.$this->db->error().'</li>';
        }
        return $insID;
    }
    
    public function updateUserAccount($post, $password){

        global $message;
        $updated = true;
        if ((int)$this->id > 0){        	
            foreach($post as $key => $data){
                if (!$this->set_meta($data["label"], $data["value"])){
                    $message = (empty($message))? '<li><strong>'.$data["label"].'</strong> was not updated</li>' : $message . '<li><strong>'.$data["label"].'</strong> was not updated</li>';
                    $updated = false;
                }
            }
        }
        if (!empty($password)){
            $res = $this->db->query(sprintf("UPDATE `sysUsers` SET `userIDPassword` = '%s' WHERE `itemID` = '%d'",
                MD5($this->db->escape($password, true)),
                $this->db->escape((int)$this->id, true)
            ));
            if ($this->db->affected_rows($res) == 0){
                $message = (empty($message))? '<li><strong>Password</strong> was not updated</li>' : $message . '<li><strong>Password</strong> was not updated</li>';
                $updated = false;
            }
        }
        return $updated;
    }
    
    public function updateUserAccountApplicant($post, $password){

        global $message;
        $updated = true;
        if ((int)$_SESSION['userID'] > 0){        	
            foreach($post as $key => $data){
                if (!$this->setApplicantMeta($data["label"], $data["value"])){
                    $message = (empty($message))? '<li><strong>'.$data["label"].'</strong> was not updated</li>' : $message . '<li><strong>'.$data["label"].'</strong> was not updated</li>';
                    $updated = false;
                }
            }
        }
        return $updated;
    }
    
    public function setApplicantMeta($fieldLabel, $value)
	{
		global $db;
		//determine the keyID of this field
		$fieldID = $this->db->return_specific_item(false, "sysUGFields", "itemID", "--", "fieldLabel = '".$fieldLabel."'");
		if(is_numeric($fieldID) && $fieldID > 0) {
				//field exists for this user, lets update it
				$qry = sprintf("UPDATE sysUGFValues SET value='%s' WHERE userID='%d' AND fieldID='%d';",
					$this->db->escape($value),
					(int)$_SESSION['userID'],
					(int)$fieldID);
				$this->db->query($qry);
				
		} else {
			return "Could not find the field [" . $fieldLabel . "] in sysUGFields, check your spelling and make sure it matches exactly.";
		}
	}
    
}