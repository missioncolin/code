<?php
/**
* This class overrides the parent method so we can custom re-direct users based on their group. If applicants come to the site and login w/o a job ID, they will be re-directed to the edit profile page
*/
class customAuth extends Auth{
    
    public function login($username, $password, $newRegistration = false)
	{
		$cf = '';
		if (isset($_GET['cf'])) {
			$cf = '&cf=' . $_REQUEST['cf'];
		}
		
		if ($username == '' || $password == '') {
			
			header("Location:http://". $_SERVER['SERVER_NAME'] . '/login?t=4' . $cf);
			die('Redirecting (no user or password)...');
			
		}
		// use local authentication as default			
		$lQry = sprintf("SELECT u.`itemID`, u.`sysStatus`, 
		      (SELECT group_concat(`nameSystem`) FROM `sysUGroups` AS g 
		          INNER JOIN `sysUGLinks` AS gl ON g.`itemID` = gl.`groupID` 
		          WHERE gl.`userID` = u.`itemID` AND g.`sysOpen` = '1' AND g.`sysStatus` = 'active') as usrGroup
			FROM sysUsers AS u
			WHERE userIDField  = '%s'
			AND userIDPassword = MD5('%s') 
			AND sysOpen = '1';",
				$this->db->escape($username),
				$this->db->escape($password));
				

				
		$userIsInDirectory = false;		
		if ($this->type == "ad") {			
			//check to see that this user is in the directory
			if ($user->ad->authenticate(strtoupper($username), $password)) { 
				
				//yes the user is in the directory, preform this query without the password check
				$lQry = sprintf("SELECT itemID, sysStatus FROM sysUsers
					WHERE userIDField  = '%s'
					AND sysOpen = '1';",
						$this->db->escape($username));
				$userIsInDirectory = true;

			} 
		}		
		
		$lRes = $this->db->query($lQry);
		
		if ($this->db->valid($lRes) || $userIsInDirectory) {

			if (!$this->db->valid($lRes)) { //this site uses AD if this is false, this user was found in the directory, but is not in the local draggin db, so add them with no grouping
				
				$qry = sprintf("INSERT INTO sysUsers (userIDField, userIDPassword, myKey, lastLoginDate, regDate, regHash, ecHash, fpHash, sysUser, sysIsADUser, sysStatus, sysOpen) VALUES ('%s', 'ACTIVE DIRECTORY MANAGED', '', %s, %s, '', '', '', '0', '1', 'active', '1');%s",
					$this->db->escape(clean($username, true)),
					$this->db->now,
					$this->db->now,
					$this->db->last_insert);
				$res = $this->db->query($qry);
				
				$RS['itemID'] 	 = $this->db->insert_id($res);
				$RS['sysStatus'] = "active";

			} else {
				$RS = $this->db->fetch_assoc($lRes);
			}
		
			if ($RS['sysStatus'] == 'active') {

				$_SESSION['userID'] = $RS['itemID'];
				$_SESSION['canEditPageContent'] = true;
				$_SESSION['myKey']  = md5($username . date("r") . rand(1, 999999999));

				
				$qry = sprintf("UPDATE sysUsers SET myKey = '%s', lastLoginDate = %s WHERE itemID = '%d';",
					$_SESSION['myKey'],
					$this->db->now,
					(int) $RS['itemID']);					
				$this->db->query($qry);
				
				if (isset($_GET['cf'])) {
					header("Location:http://". $_SERVER['SERVER_NAME'] . urldecode($_REQUEST['cf']));
					die('Redirecting (good)...');
				} else { 
				    //here re-direct based on user group
				    switch ($RS['usrGroup']){
    				    case "applicants":
    				    	 if ($newRegistration == false){
    				        	header("Location:http://". $_SERVER['SERVER_NAME'] . "/profile");
    				        	die('Redirecting (applicant good, no cf)...');
    				        }else{
	    				       header("Location:http://". $_SERVER['SERVER_NAME'] . "/applications");
    				        	die('Redirecting (applicant good, no cf)...');
    				        }
    				        break;
    				    case "hr-managers":
    				        header("Location:http://". $_SERVER['SERVER_NAME'] . "/create-job?step=1");
    				        die('Redirecting (applicant good, no cf)...');
    				        
    				        break;
    				    default:
    				        header("Location:http://". $_SERVER['SERVER_NAME'] . "/");
    				        die('Redirecting (good, no cf)...');
    				        break;
				    }
					
				} 
				
			} elseif ($RS['sysStatus'] == 'disabled') { 
				$this->quipp->system_log($username . ' was denied access because their account is disabled');
				header("Location:http://". $_SERVER['SERVER_NAME'] . '/login?t=7' . $cf);
				die('Redirecting (disabled)...');
				
	
			} elseif ($RS['sysStatus'] == 'public') { 
				
				$this->quipp->system_log($username . ' was denied access because their account is set to public');
				header("Location:http://". $_SERVER['SERVER_NAME'] . '/login?t=8' . $cf);
				die('Redirecting (public)...');
				

			} elseif ($RS['sysStatus'] == 'inactive') { 
				$this->quipp->system_log($username . ' was denied access because their account is inactive ');
				header("Location:http://". $_SERVER['SERVER_NAME'] . '/login?t=5' . $cf);
				die('Redirecting (inactive)...');
				
			} 
		} else { 
			$this->quipp->system_log($username . ' was denied access because their password was wrong');
			header("Location:http://". $_SERVER['SERVER_NAME'] . '/login?t=4' . $cf);
			die('Redirecting (username or password was bad)...');
			

		} 

	}
}