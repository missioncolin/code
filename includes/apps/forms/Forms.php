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
            'adaptive' => true
        ),
        'small' => array(
            'l'        => 48,
            'w'        => 48,
            'adaptive' => true
        )
    );
    
    public function __construct($db){
        if ($db INSTANCEOF DB_MySQL){
            $this->db = $db;
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
    
    public function createUserAccount($post, $password){
    
        $insID = 0;
        
        $qry = sprintf("INSERT INTO `sysUsers` (`userIDField`, `userIDPassword`, `regDate`, `sysUser`, `sysIsADUser`, `sysStatus`, `sysOpen`) VALUES ('%s', '%s', NOW(), '0', '0', 'active', '1')",
            $this->db->escape($post["Email"]["value"], true),
            MD5($this->db->escape($password, true))
        );
        
        $res = $this->db->query($qry);
        $inID = 0;
        if ($this->db->affected_rows($res) == 1){
            $insID = $this->db->insert_id();
            $this->id = $insID;
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
}