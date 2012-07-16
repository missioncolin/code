<?php

class Auth
{
	
	public $type = 'local';
	public $logged_in = false;
	protected $db;
	protected $quipp;
	function __construct($db,$quipp,$type = 'local')
	{
		$this->db = $db;
		$this->quipp = $quipp;
		if (isset($type)) {
			$this->type = $type;
		} 
	
	}
	
	function delete_privilege_links($privID) 
	{
		
		$qry = sprintf("DELETE FROM sysUGPLinks WHERE privID = '%d';",
				$this->db->escape($privID)
				);
				
				$this->db->query($qry);
	}
	
	public function login($username, $password)
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
		$lQry = sprintf("SELECT itemID, sysStatus 
			FROM sysUsers
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
				$_SESSION['myKey']  = $myKey = md5($username . date("r") . rand(1, 999999999));

				
				$qry = sprintf("UPDATE sysUsers SET myKey = '%s', lastLoginDate = %s WHERE itemID = '%d';",
					$_SESSION['myKey'],
					$this->db->now,
					(int) $RS['itemID']);					
				$this->db->query($qry);
				
				if (isset($_GET['cf'])) {
					header("Location:http://". $_SERVER['SERVER_NAME'] . urldecode($_REQUEST['cf']));
					die('Redirecting (good)...');
				} else { 
					header("Location:http://". $_SERVER['SERVER_NAME'] . "/");
					die('Redirecting (good, no cf)...');
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

	function check_auth()
	{
		//global $quipp;
		
		// check to see if this user is logged in
		if (!strpos($_SERVER['PHP_SELF'], "auth.php")) {
			if (isset($_SESSION['userID'], $_SESSION['myKey']) && $_SESSION['userID'] && $_SESSION['myKey']) {
				
				$qry = sprintf("SELECT itemID, sysStatus, myKey, userIDField FROM sysUsers WHERE  itemID = '%d';",
					(int) $_SESSION['userID']);
				$res = $this->db->query($qry);
		
				if ($this->db->valid($res)) {
					
					$loginUserRS = $this->db->fetch_assoc($res);			
					if ($loginUserRS['sysStatus'] == 'disabled') {
						
						$this->quipp->system_log($loginUserRS['userIDField'] . ' was booted out because their account was disabled');
						$this->boot_em_out(7); //Account Has Been Disabled
					} elseif ($loginUserRS['sysStatus'] == 'inactive') {
					
						$this->quipp->system_log($loginUserRS['userIDField'] . ' was booted out because their account was deactivated');
						$this->boot_em_out(7); //Account Has Been Disabled
					}  elseif ($loginUserRS['myKey'] != $_SESSION['myKey']) {
						//$this->quipp->system_log($loginUserRS['userIDField'] . ' was booted out because their keys did not match. ' . $loginUserRS['myKey'] . ' != ' . $_SESSION['myKey']);
						//$this->boot_em_out(9);
					} elseif (strpos($_SERVER['PHP_SELF'], "admin")) { //Global Var For Who can get into the Admin Console
						
						if (!$this->has_permission('root')) {
							
							$this->quipp->system_log($loginUserRS['userIDField'] . ' was booted out of the admin because they don\'t have permission');
							$this->boot_em_out(6); 
						} // Entering The Administration Console
					}
				} elseif (strpos($_SERVER['PHP_SELF'], "admin")) {
					$this->quipp->system_log($loginUserRS['userIDField'] . ' was booted out because their session expired');
					$this->boot_em_out(2); // Session Expired
				}
			} elseif (strpos($_SERVER['PHP_SELF'], "admin") && !($_SESSION['userID'] && $_SESSION['myKey'])) { 
				$this->boot_em_out(1);//No Session
			} 
		}
	
		
	}
	
	
	function fetch_or_create_content_priv($pageID, $regionContentID) 
	
	{
		global $quipp, $db;
		//build the lookup string (systemName)	
					
					$permissionSystemName = "view_pageID_" . $pageID . "_pageTemplateRegionContentID_" . $regionContentID;
					
					//first check to see if a permission for this pageTemplateRegionContent exists
					$privID = $db->return_specific_item(false, "sysPrivileges", "itemID", false, "systemName = '".$permissionSystemName."'");
					if(!$privID) {
						//a permission has not been registered for this pageTemplateRegionContentID, let's create one and register it
						//this should be replaced by a $auth-create_permission(… .or something when that gets done
						$qry = sprintf("INSERT INTO sysPrivileges (groupID, systemName, label, myOrder, sysStatus, sysOpen)  VALUES ('4', '%s', '%s', '0', 'active', '1');",
						$db->escape($permissionSystemName),
						$db->escape($permissionSystemName)
						);
					
						if($db->query($qry)) {
							$privID = $db->insert_id();
						} else {
							print "Error: A privID could not be found or created. It's possible there are missing arguments that are breaking the query. Needs pageID and regionContentID.";
							die();
						}
					} 
					
					return $privID;
	
	}

	
	
	//passes permission and literal groupid
	function group_has_permission($permission, $groupID)
	{
		global $quipp, $user;
		
		if(!is_numeric($groupID)) {
			return false;
		}
	
		$qry = sprintf("SELECT pg.itemID
			FROM sysPrivileges AS p 
			LEFT OUTER JOIN sysUGPLinks AS pg ON(p.itemID = pg.privID)
			WHERE p.sysOpen = '1' 
			AND p.systemName = '%s'
			AND pg.groupID = '%d'
			AND p.sysStatus = 'active'
			AND pg.sysStatus = 'active';",
				$this->db->escape($permission),
				$this->db->escape($groupID));
		$res = $this->db->query($qry);

		if ($this->db->valid($res)) {
			return true;
		}
		return false;

	}
	
	
	
	function has_permission($permission, $userID = false)
	{
		global $quipp, $user;
		
		if($userID == false) {
			$userID = $user->id;
		}

		
		if ($this->type == "ad" && isset($userID) && $user->isAD) {

			//this seems like a lot of queries, but we can't trust that there haven't been changes in the AD, so this routiene just syncs AD groups with Quipp groups
			//we do this so that the administrator doesn't have to manage users in Quipp if he/she doesn't want to.
			//why not just use AD groups only? We'll because we want to tie custom app permissions to groups and need a reference table anyway, otherwise it gets quite messy with permissions tied directly to string based AD names with little ability to manage

			
			//get group membership from the AD
			$ADGroups = $user->ad->user_groups(addslashes(strtoupper($user->username)), true);



			//get the groups from Quipp that this user is a member of
			$gQry = sprintf("SELECT g.*, u.userIDField AS userName 
				FROM sysUGroups AS g
				LEFT OUTER JOIN sysUGLinks AS l ON (g.itemID = l.groupID)
				LEFT OUTER JOIN sysUsers AS u ON (l.userID = u.itemID) 
				WHERE (
					l.userID = '%d' 
					AND l.userID != '0' 
					AND l.userID != '' 
					AND l.userID IS NOT NULL
				) 
				AND l.sysOpen = '1' 
				AND l.sysStatus = 'active' 
				AND g.sysOpen = '1' 
				AND g.sysStatus = 'active' 
				AND g.sysIsADGroup = '1'
				AND g.sysGroup = '0'
				AND u.sysOpen = '1' 
				AND u.sysStatus = 'active';",
					$userID);
			$gRes = $this->db->query($gQry);

			$adGroupsThatFoundAMatch = array();
			
			if ($this->db->valid($gRes)) {
				while ($adG = $this->db->fetch_assoc($gRes)) {

					$theQuippGroupThisUserIsInDoesNotMatchAD = true; //init

					
					foreach ($ADGroups as $groupNameString) { 

						if ((trim($groupNameString) == trim($adG['sysADGroupName'])) && $adG['sysIsADGroup'] == '1') { //if names match up cancel the delete
							$theQuippGroupThisUserIsInDoesNotMatchAD = false;
							array_push($adGroupsThatFoundAMatch, $groupNameString);
						}

					}


					if ($theQuippGroupThisUserIsInDoesNotMatchAD) { //we must remove this user from this Quipp group because they are no longer in that group in the AD
						$qry = sprintf("DELETE FROM sysUGLinks WHERE userID = '%d' AND groupID = '%d'",
							$userID,
							(int) $adG['itemID']);
						$this->db->query($qry);
					}

				}
			}

			//we don't want to run a hit on the databse for every single AD group this user is a member of, only the ones which have been registered to Quipp, so lets get them out
			$qry = "SELECT sysADGroupName FROM sysUGroups WHERE sysOpen = '1' AND sysStatus = 'active' AND sysIsAdGroup = '1'";
			$res = $this->db->query($qry);

			$QuippRegisteredADGroups = array();
			if ($this->db->valid($res)) {
				while ($adGR = $this->db->fetch_assoc($res)) {
					array_push($QuippRegisteredADGroups, $adGR['sysADGroupName']);
				}


				//after we've checked all the Quipp group memberships for deletions, we need to check to see if there are any new group memberships set from AD that need to propagate to Quipp for this user
				foreach ($ADGroups as $groupNameString) {


					if (in_array($groupNameString, $QuippRegisteredADGroups)) { //if this group is registered in Quipp

						if (!in_array($groupNameString, $adGroupsThatFoundAMatch)) { //if this group was not in the Quipp groups for this user, add it
							
							//we must determine the ID for this group:								
							//returns false if none
							$thisGroupID = returnSpecificItem("", "sysUGroups", "itemID", false, false, " sysADGroupName = '" . $groupNameString . "' AND sysIsADGroup = '1'"); 

							if ($thisGroupID) { //if this AD group actually exists in Quipp
								$qry = sprintf("INSERT INTO sysUGLinks (userID, groupID, sysStatus, sysOpen) VALUES ('%d', '%d', 'active', '1')",
									$userID,
									$thisGroupID);
								$this->db->query($qry);
							}
						}
					}
				}
			}
		}  //after this it's back to business as usual


		//admin group override (admins are allowed to do everything
		$qry = sprintf("SELECT userID FROM sysUGLinks WHERE groupID = '1' AND userID = '%d' AND sysStatus = 'active' AND sysOpen = '1';",
			$userID);
		$res = $this->db->query($qry);
		
		if ($this->db->valid($res)) {
			return true;
		}
		
		$qry = sprintf("SELECT p.systemName, ug.userID, ug.groupID AS userGroup, pg.groupID as privGroup 
			FROM sysPrivileges AS p 
			LEFT OUTER JOIN sysUGPLinks AS pg ON(p.itemID = pg.privID)
			LEFT OUTER JOIN sysUGLinks AS ug ON(pg.groupID = ug.groupID)
			WHERE p.sysOpen = '1' 
			AND (
				ug.userID = '%d' 
				AND ug.userID != '0' 
				AND ug.userID != '' 
				AND ug.userID IS NOT NULL
			) 
			AND p.systemName = '%s'
			AND p.sysStatus = 'active'
			AND pg.sysStatus = 'active'
			AND ug.sysStatus = 'active';",
				$userID,
				$this->db->escape($permission));
		$res = $this->db->query($qry);

		if ($this->db->valid($res)) {
			return true;
		}
		return false;

	}

	
	function get_users_with_this_permission($permission = false) {
		//this doesn't sync AD groups yet. Not sure if it should.
		global $quipp, $user;
		
		if(!$permission) {
			return false;
		}
		

		$qry = sprintf("SELECT p.systemName, ug.userID, ug.groupID AS userGroup, pg.groupID as privGroup 
			FROM sysPrivileges AS p 
			LEFT OUTER JOIN sysUGPLinks AS pg ON(p.itemID = pg.privID)
			LEFT OUTER JOIN sysUGLinks AS ug ON(pg.groupID = ug.groupID)
			WHERE p.sysOpen = '1' 
			AND (
				ug.userID != '0' 
				AND ug.userID != '' 
				AND ug.userID IS NOT NULL
			) 
			AND p.systemName = '%s'
			AND p.sysStatus = 'active'
			AND pg.sysStatus = 'active'
			AND ug.sysStatus = 'active';",
				$this->db->escape($permission));
		$res = $this->db->query($qry);

		if ($this->db->valid($res)) {
			
			$userList = array();
			
			while($tmp = $this->db->fetch_assoc($res)) {
				$userList[] = $tmp['userID'];
			}
			return $userList;
			
		}
	}
	
	//permission expects string system name of permission
	function get_groups_with_this_permission($permission = false) {
		//this doesn't sync AD groups yet. Not sure if it should.
		global $quipp, $user;
		
		if(!$permission) {
			return false;
		}
		

		$qry = sprintf("SELECT pg.groupID 
			FROM sysPrivileges AS p 
			LEFT OUTER JOIN sysUGPLinks AS pg ON(p.itemID = pg.privID)
			WHERE p.sysOpen = '1' 
			AND p.systemName = '%s'
			AND p.sysStatus = 'active'
			AND pg.sysStatus = 'active';",
				$this->db->escape($permission));
		$res = $this->db->query($qry);

		if ($this->db->valid($res)) {
			
			$groupList = array();
			
			while($tmp = $this->db->fetch_assoc($res)) {
				$groupList[] = $tmp['groupID'];
			}
			return $groupList;
			
		}
	}



	function boot_em_out($reason)
	{
		global $qs;

		header("Location:http://" . $_SERVER['SERVER_NAME'] . "/login?t=" . $reason . $qs . "&cf=" . urlencode($_SERVER['REQUEST_URI']));
		die('You do not have access...');
	}
	

	/**
	 * Returns a fail message based on a numerical value
	 */
	function fail_type($type)
	{
		switch ($type) {
			case 2:
				$message = "<div align=\"left\">
				<strong> Session Expired </strong>
				<p>Either we could not identify you or there was a problem with your login. <br />If you entered a user name and password, that password/user name pair do not seem to be in our system. <br />
				<em>Please try again</em>, if you continue to experience difficulty connecting please contact the Network Administrator. </p></div>";
		
				return alert_box($message, 2);
				break;
			case 3:
				$message = "<div align=\"left\">
				<strong> Access Denied </strong>
				<p>You do not have the necessary permission to view this area. </p>
				<p> <input type=\"button\" onclick=\"javascript:history.go(-1);\" value=\"Go Back\" /></p></div>";
		
				return alert_box($message, 2);
				break;
			case 4:
				$message = "<div align=\"left\">
				<strong> Incorrect Login </strong>
				<p>Either we could not identify you or there was a problem with your login. </p>
				<p> If you entered a user name and password, that password/user name pair do not seem to be in our system.</p></div>";
		
				return alert_box($message, 2);
				break;
			case 5:
				$message = "<div align=\"left\">
				<strong> User Not Active </strong>
				<p><em>Please check your email</em>.  There you will find a validation link to activate your account.  <br />" .
					"If you continue to experience difficulty connecting please contact the Network Administrator. </p></div>";
		
				return alert_box($message, 2);
				break;
			case 6:
				$message = "<div align=\"left\">
				<strong>Error Entering The Administration Console </strong>
				<p><em>If you are not an Administrator of this site please return to the " .
					"<a href=\"/\">home page</a></em>. </p></div>";
		
				return alert_box($message, 2);
				break;
			case 7:
				$message = "<div align=\"left\">
				<strong> Access Denied </strong>
				<p>Your account has been disabled .&nbsp;&nbsp;Please <a href=\"mailto:" . $this->db->return_specific_item(1, "sysStorageTable", "value") . "\">contact us</a>.</p></div>";
		
				return alert_box($message, 2);
				break;
			case 8:
				$message = "<div align=\"left\">
				<strong> Access Denied </strong>
				<p>You do not have access to view this area.</p></div>";
		
				return alert_box($message, 2);
				break;
			case 9:
				$message = "<div align=\"left\">
				<strong> Session Expired </strong>
				<p>Your session has expired. This is usually caused because your login was used on another machine.</p></div>";
		
				return alert_box($message, 2);
				break;
			case "preview":
				$message = "<div align=\"left\">
					<strong> Access Denied </strong>
					<p>You need to login to preview page data. </p>
				</div>";		
				return alert_box($message, 2);
				break;
		}
		return false;
	
	}


}




?>