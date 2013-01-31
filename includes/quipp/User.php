<?php

class User
{
	public $isAD = false;
	public $info = array();
	public $id;
	public $username;
	protected $db;

	function __construct($db,$id = false)
	{
		$this->db = $db;

		if(is_numeric($id)) {
			$this->id   = $id;
			$this->info = $this->get_details();

			$qry = sprintf("SELECT userIDField, myKey, lastLoginDate, regDate, sysIsADUser FROM sysUsers WHERE itemID = '%d' AND sysStatus = 'active'", $this->id);
			$res = $this->db->query($qry);
			if($this->db->valid($res)) {
				$tmp = $this->db->fetch_assoc($res);

				$this->username   = $tmp['userIDField'];
				$this->myKey    = $tmp['myKey'];
				$this->lastLoginDate = $tmp['lastLoginDate'];
				$this->regDate    = $tmp['regDate'];
				$this->isAD    = ($tmp['sysIsADUser'] == '1') ? true : false;

			}
			
			// get groups the user is a member of
			$this->groups = array();
			$gQry = sprintf("SELECT itemID, nameSystem FROM sysUGroups WHERE itemID IN (SELECT groupID FROM sysUGLinks WHERE userID = '%d')", $id);
			$gRes = $this->db->query($gQry);
			if($this->db->valid($gRes)) {
    			while($g = $this->db->fetch_assoc($gRes)) {
        			$this->groups[$g['nameSystem']] = $g['itemID'];
    			}
			}
    		
			
		}


		if($this->isAD) {
			$this->ad = new adLDAP();
		}

	}


	public function get_group_memberships($userID = false)
	{


	}


	function get_permissions()
	{



	}

	function get_members_of_group() {
	
		
	}

	
	function set_meta($fieldLabel, $value)
	{
		global $db;
		//determine the keyID of this field
		$fieldID = $this->db->return_specific_item(false, "sysUGFields", "itemID", "--", "fieldLabel = '".$fieldLabel."'");

		if(is_numeric($fieldID)) {
			if(array_key_exists($fieldLabel, $this->info)) {

				//field exists for this user, lets update it
				$qry = sprintf("UPDATE sysUGFValues SET value='%s' WHERE userID='%d' AND fieldID='%d';",
					$this->db->escape($value),
					(int)$this->id,
					(int)$fieldID);
				$this->db->query($qry);

			} else {
				//user does not have this value, insert the link first

				$qry = sprintf("INSERT INTO sysUGFValues (userID, fieldID, value, sysStatus, sysOpen) VALUES ('%d', '%d', '%s', 'active', '1');",
					(int)$this->id,
					(int)$fieldID,
					$this->db->escape($value));
				$this->db->query($qry);


			}
			return true;
		} else {
			return "Could not find the field [" . $fieldLabel . "] in sysUGFields, check your spelling and make sure it matches exactly.";

		}
	}

	/*
		GET META FOR THIS USER
		returns the value of a passed meta label for a specific user, currently only handles single value meta items
		*/

	public function get_meta($fieldLabel, $userID = false)
	{
		global $db;
		//currently only returns a signle value

		if($userID == false) {
			$userID = $this->id;
		}

		$qry = sprintf("SELECT v.value
			FROM sysUGFields f
			LEFT JOIN sysUGFValues v ON f.itemID=v.fieldID
			WHERE v.sysOpen='1'
			AND f.fieldLabel='%s'
			AND v.userID='%d'",
			$this->db->escape($fieldLabel),
			(int) $userID);
			
		$res = $this->db->query($qry);

		if($this->db->valid($res)) {
			$tmp = $this->db->fetch_assoc($res);
			return $tmp['value'];
		}
		return false;

	}
	
		/*
		GET DETAILS (GET ALL META FOR THIS USER)
		returns the value of all stored meta for all specific users
		*/

	public function get_details($userID = false)
	{
		
		if($userID == false) {
			$userID = $this->id;
		}

		$qry = sprintf("SELECT f.fieldLabel, v.value
			FROM sysUGFields f
			LEFT JOIN sysUGFValues v ON f.itemID=v.fieldID
			WHERE v.sysOpen='1'
			AND v.userID='%d'",
			(int) $userID);
		$res = $this->db->query($qry);

		if($this->db->valid($res)) {
			$meta = array();
			while($tmp = $this->db->fetch_assoc($res)) {
				$meta[$tmp['fieldLabel']] = $tmp['value'];
			}
			return $meta;
		}
		return false;

	}
	
	
	/**
	 * Grab a list of Active Directory fields
	 * @return array
	 */
	private function get_ad_fields() {
		
	
	}


	/**
	 * Build the user editor form for use in the Editor class
	 *
	 * @param int     userID
	 */

	public function build_user_editor($userID, $groups = false, $postFields = false)
	{
		global $auth;

		if($udResult = $this->db->result_please((int) $userID, "sysUsers")) {
			$udRS = $this->db->fetch_assoc($udResult);
		}
		


		$groupCheck = sprintf("SELECT groupID FROM sysUGLinks WHERE userID = '%d'", $userID);
		if (is_array($groups)) {
			$tmp = '';
			foreach ($groups as $groupID) {
				$tmp .= $groupID . ',';
			}
			$groupCheck = substr($tmp, 0, -1);

		}
		
		
		$fields = array();
		

		$uQry = sprintf("SELECT DISTINCT f.itemID, f.fieldLabel, f.sysAdFieldName, f.sysISADField, f.myOrder, f.validationCode, v.value
			FROM sysUGFields AS f
				LEFT OUTER JOIN sysUGFValues as v ON(f.itemID = v.fieldID AND v.userID = '%d')
				LEFT OUTER JOIN sysUGFLinks as fglinks ON(f.itemID = fglinks.fieldID)
			WHERE f.sysOpen = '1' 
			AND fglinks.groupID IN (%s)
			ORDER BY f.myOrder ASC;",
				(int) $userID,
				$groupCheck);
		$uRes = $this->db->query($uQry);
	
		if($this->db->valid($uRes)) {
		
				
			//if we are using AD here, pull this users profile if it exists
			if(isset($udRS) && $udRS['sysIsADUser'] == '1' && $auth->type == 'ad') {
			
				if (!is_object($this->ad)) {
					$this->ad = new adLDAP();
				}
				
				$getADFieldsQry = "SELECT sysADFieldName FROM sysUGFields WHERE sysIsADField = '1';";
				$getADFieldsRes = $this->db->query($getADFieldsQry);
				
				$myADFields = array();
				while ($ADFieldsRS = $this->db->fetch_assoc($getADFieldsRes)) {
	
					if(!empty($ADFieldsRS['sysADFieldName'])) {
						array_push($myADFields, $ADFieldsRS['sysADFieldName']);
					}
				}
				array_push($myADFields, "memberof");
	
				///the user name being passed to user info may need to be adjusted
				$ADFieldValues = $this->ad->user_info(addslashes(strtoupper($udRS['userIDField'])), array("*"));
	
			}
		
		
			while ($udRS = $this->db->fetch_assoc($uRes)) {

				if($udRS['sysISADField'] == '1') { 
					
					//always set with a value from the directory if we have one and this site is connected to the AD
					if(isset($ADFieldValues[0][$udRS['sysAdFieldName']][0])) {
						$udRS['value'] = $ADFieldValues[0][$udRS['sysAdFieldName']][0];
					}
				}
				
				
				$newFormID = $udRS['validationCode'] . str_replace(" ", "_", $udRS['fieldLabel']);

				if(isset($_POST[$newFormID])) {
					$udRS['value'] = $_POST[$newFormID];
				}
				if(isset($postFields[$newFormID])) {
					$udRS['value'] = $postFields[$newFormID];
				}
				
				
				switch (substr($udRS['validationCode'], 5, 4)) {

					case "CHCK":
						$checkMe = (!empty($udRS['value'])) ? ' checked="checked"' : '';						
						$fieldBuffer = '<input type="checkbox" id="meta[' . $newFormID . ']" name="meta[' . $newFormID . ']" value="1"' . $checkMe . ' />';
						break;
					case "PROV":
						$fieldBuffer = get_prov_list('meta[' . $newFormID . ']', $udRS['value']);
						break;
					case "COUN":
						$fieldBuffer = get_country_list('meta[' . $newFormID . ']', $udRS['value']);
						break;
				   case "TEXT":
						$fieldBuffer = '<textarea id="meta[' . $newFormID . ']" name="meta[' . $newFormID . ']">' . $udRS['value'] . '</textarea>';
						break;
					default:
						$fieldBuffer = '<input type="text" id="meta[' . $newFormID . ']" name="meta[' . $newFormID . ']" value="' . $udRS['value'] . '" />';
						break;
				}
								
				$fields[] = array(
					'label'   => $udRS['fieldLabel'],
					'dbColName'  => false,
					'tooltip'   => '',
					'writeOnce'  => false,
					'widgetHTML' => $fieldBuffer, // <-- the contents of this variable are built above
					'valCode'   => $udRS['validationCode'],
					'dbValue'   => false
				);
				
			}
		}
		
			
		$formBuffer = "<table>";
		foreach($fields as $field) {
			$formBuffer .= "<tr>";
			
			$newFieldID = $field['valCode'] . str_replace(" ", "_", $field['label']);
			
			$formBuffer .= "<td valign=\"top\" style=\"width:180px\"><label for=\"meta[".$newFieldID."]\">" . $field['label'] . "</label></td><td>" . $field['widgetHTML'] . " <p class=\"tooltip\">" . $field['tooltip'] . "</p></td>";
			$formBuffer .= "</tr>";
		}
		$formBuffer .= "</table>";
	
	
	
		return $formBuffer;
	}

    /**
     * Change the user's password
     * @param string
     * @return bool
     */
     
    public function changePassword($password)
    {
        if (empty($password)) {
            throw new Exception('Password can not be empty');
        }
        $stmt = $this->db->query(sprintf("UPDATE sysUsers SET `userIDPassword`= MD5('%s') WHERE itemID='%d'", $password, $this->id));
        
        return (boolean)$this->db->affected_rows($stmt);
    
    }


    /**
     * Rest the fp hash
     * @return bool
     */
    public function removeHash()
    {
        $stmt = $this->db->query(sprintf("UPDATE sysUsers SET `fpHash`= NULL WHERE itemID='%d'", $this->id));
        return (boolean)$this->db->affected_rows($stmt);
    }
}


?>