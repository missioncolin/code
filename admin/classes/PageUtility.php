<?php

class PageUtility
{

    protected $_db;
	function __construct($db)
	{
	   if (is_object($db)){
    	   $this->_db = $db;
	   }
	}


	function get_page_properties($pageID = false) {
	
		global $quipp, $notify;
		
		if($pageID) {
			$res = $this->_db->query($qs = "SELECT * FROM sysPage WHERE itemID = '$pageID'");
		} 
		
		if ($this->_db->valid($res)) {

			$rs = $this->_db->fetch_assoc($res);
			return $rs;

		}

	}
	
    function get_my_primary_domain($navID = false) {
  
        global $quipp, $notify;
        
        if($navID) {
            $res = $this->_db->query($qs = "SELECT d.domain FROM sysSitesDomains AS d
                INNER JOIN sysSitesInstances AS si ON (d.`siteID` = si.`siteID`)
                INNER JOIN sysNavBuckets AS b ON (si.`itemID` = b.`instanceID`) 
                INNER JOIN sysNav AS n ON (b.itemID = n.bucketID) 
                WHERE n.itemID = '$navID' ORDER BY d.`myOrder` LIMIT 1");
        } 
        
        if ($this->_db->valid($res)) {
        
            $rs = $this->_db->fetch_assoc($res);
            return $rs['domain'];
        
        }
    }

	function create_empty_page($underThisParentID = 1, $parentType="bucket")
	{
		global $quipp, $nav;
		

		if(!isset($nav)) {
			require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/quipp/Nav.php";
			$nav = new Nav();
		}
		//yell("CREATE EMPTY PAGE");
		switch ($parentType) {
		case "bucket":
			//yell("CREATE EMPTY PAGE BUCKET!");
			//get the instance ID based on the bucket
			$qry = sprintf("SELECT instanceID FROM sysNavBuckets WHERE itemID = '%d'",
				$this->_db->escape($underThisParentID));
			$res = $this->_db->query($qry);
			$rs = $this->_db->fetch_assoc($res);
			$pageInstanceID = $rs['instanceID'];

			if(!is_numeric($pageInstanceID)) {
				return false;
			}

			$bucketID = $underThisParentID;

			break;
		case "nav":

			//get the instance ID based on the bucket via the nav item
			$qry = sprintf("SELECT instanceID FROM sysNavBuckets WHERE itemID = (SELECT bucketID FROM sysNav WHERE itemID = '%d')",
				$this->_db->escape($underThisParentID));
			//yell($qry);
			$res = $this->_db->query($qry);
			$rs = $this->_db->fetch_assoc($res);
			$pageInstanceID = $rs['instanceID'];

			if(!is_numeric($pageInstanceID)) {
				return false;
			}

			//get the bucket ID via the nav item
			$qry = sprintf("SELECT bucketID FROM sysNav WHERE itemID = '%d';",
				$this->_db->escape($underThisParentID));
			$res = $this->_db->query($qry);
			$rs = $this->_db->fetch_assoc($res);
			$bucketID = $rs['bucketID'];

			if(!is_numeric($bucketID)) {
				return false;
			}

			break;
		}

		//yell("CREATE EMPTY PAGE A: " . $bucketID . " | " . $pageInstanceID);

		//$pageHash = substr(md5((time("c") + rand()));
		$nQry = sprintf("SELECT MAX(itemID)+1 as newest FROM sysPage");
		$nRes = $this->_db->query($nQry);
		
		if($this->_db->valid($nRes)) {  //grab the page data
			$newest = $this->_db->fetch_assoc($nRes);
			$pageTempName = "Untitled-" . $newest['newest'];
		} else {
			$pageHash = md5((time("c") + rand()));
			$pageTempName = "Untitled-" . $pageHash;
		}
		//create the page record first
		//each page gets a random md5 hash system name
		//note: we're using inactive here because we don't want this to be available
		$qry = sprintf("INSERT INTO sysPage (instanceID, templateID, systemName, label, masterHeading, sysDateCreated, sysVersion, sysStatus, sysOpen) VALUES ('%d','1', '%s', 'Untitled', '%s', NOW(), 'draft', 'inactive', '1');",
			$this->_db->escape($pageInstanceID),
			$this->_db->escape($pageTempName),
			$this->_db->escape($pageTempName));
		//yell($qry);
		if($this->_db->query($qry)) {

			$pageID = $this->_db->insert_id();
			//yell("create_empty_page pageID" . $pageID);
			//then create the nav record
			//$nav->create_nav_item($bucketID, $parentID, $myOrder, $pageSystemName, $url, $target, $label, $active);
			$navID = $nav->create_nav_item($bucketID, 0, 0, $pageTempName);

			if(is_numeric($navID)) {
				//then ceate the page link (THIS IS DEPRECATED AND WILL BE REMOVED ON THE NEXT PROJET, hopefully?)
				$qry = sprintf("INSERT INTO sysSitesInstanceDataLink (instanceID, appID, appItemID, sysDateCreated, sysStatus, sysOpen) VALUES ('%d', 'page', '%s', NOW(), 'active', '1');",
					$this->_db->escape($pageInstanceID),
					$this->_db->escape($pageTempName)
				);
				if($this->_db->query($qry)) {
					return $navID;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	* This is used to copy data in the properties table from a live record to a draft record so they don't have to be manually set by the admin each time a page is edited
	*/
	function copy_live_content_properties($liveContentID, $draftContentID){

         if ($liveContentID > 0 && $draftContentID > 0){
            $draftQry = sprintf("SELECT `propertyData` FROM `sysContentDataLink` WHERE `pageTemplateRegionContentID` = %d",
                    (int)$liveContentID
                    );

            $res = $this->_db->query(sprintf("SELECT `propertyData` FROM `sysContentDataLink` WHERE `pageTemplateRegionContentID` = %d",
                    (int)$liveContentID
                    ));

            if ($this->_db->valid($res)){
                $row = $this->_db->fetch_assoc($res);

                $this->_db->query(sprintf("INSERT INTO `sysContentDataLink` (`propertyData`, `pageTemplateRegionContentID`, `sysDateCreated`, `sysOpen`) 
                    VALUES ('%s', '%d', NOW(), '1') ON DUPLICATE KEY UPDATE `propertyData` = '%s'",
                        (string)$row['propertyData'],
                        (int)$draftContentID,
                        (string)$row['propertyData'])
                );
            }
         }
     }
     /*
      * Need to get the content region ID so properties can be copied from live to draft without always re-saving
      */
     function getContentTemplateIDByPage($pageSystemName, $version){

         $contentID = 0;
         $qry = sprintf("SELECT c.itemID
                    FROM  `sysPageTemplateRegionContent` AS c
                    INNER JOIN  `sysPage` AS p ON c.`pageID` = p.`itemID` 
                    WHERE p.`sysStatus` =  'active'
                    AND p.`sysOpen` =  '1'
                    AND p.`systemName` =  '%s' 
                    AND p.`sysVersion` = '%s'
                    ORDER BY c.`itemID` DESC
                    LIMIT 0 , 1",
                    $this->_db->escape($pageSystemName),
                    $version);
         $res = $this->_db->query($qry);
         if ($this->_db->valid($res)){
             $row = $this->_db->fetch_assoc($res);
             $contentID = (int)$row["itemID"];
         }
         return $contentID;
     }

	function create_draft_copy_of_live_page($systemName)
	{
		
		global $quipp;
		
		//error_log("Calling create_draft_copy_of_live_page(" . $systemName . ")  \n", 3, "/resolutionDevSiteRoot/dev.log");
		
		//only create a draft if one doesn't already exist, if it does, just return that id instead
		//drafts get killed by the approval process where they are promoted to live, this function will come in to create a new draft where necessary
		//it's used in content.php when editing a page already set as a live, and in approve_draft_and_make_live to replace the promoted draft when it's set to live
		$pQry  = sprintf("SELECT itemID FROM sysPage WHERE sysOpen = '1' AND systemName ='%s' AND sysVersion = 'draft';",
				$this->_db->escape($systemName));
			$pRes = $this->_db->query($pQry);
	
			
			if($this->_db->valid($pRes)) {  //grab the page data
				if($pageID = $this->_db->fetch_assoc($pRes)) {
				    //copy any system data properties from live to draft record
				    $liveContID = $this->getContentTemplateIDByPage($systemName, 'live');
                    $draftContID = $this->getContentTemplateIDByPage($systemName, 'draft');
                    $this->copy_live_content_properties($liveContID, $draftContID);
					return $pageID['itemID'];
				}
			}
		
		//otherwise we're going to copy live and make a new draft, this will happen 
		
		//get the data for this page
		$pQry  = sprintf("SELECT * FROM sysPage WHERE sysOpen = '1' AND systemName ='%s' AND sysVersion = 'live';",
			$this->_db->escape($systemName));
		$pRes = $this->_db->query($pQry);
		//error_log($pQry . " \n", 3, Quipp()->config('yell_log'));

		if($this->_db->valid($pRes)) {  //grab the page data
			$pageRS = $this->_db->fetch_assoc($pRes);
		} else {
			$quipp->system_log("Issue: A request was received to create a draft version of [" . $systemName . "] but a live version of that page could not be found to use as a copy source. [create_draft_copy_of_live_page()]");
			return false;
		}
		
		//insert a new page record to use as a base for the new draft
		//Note, that we're setting active here because we can assume that if a user wants to make something live that the content will be 'active'
		$dQry = sprintf("INSERT INTO sysPage (checkOutID, privID, editPrivID, templateID, systemName, label, masterHeading, pageDescription, pageKeywords, isHomepage, isProtected, isDevLocked, sysDateCreated, sysVersion, sysStatus, sysOpen) VALUES (NULL, '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', %s, 'draft', '%s', '1');%s",
			$this->_db->escape($pageRS['privID']),
			$this->_db->escape($pageRS['editPrivID']),
			$this->_db->escape($pageRS['templateID']),
			$this->_db->escape($pageRS['systemName']),
			$this->_db->escape($pageRS['label']),
			$this->_db->escape($pageRS['masterHeading']),
			$this->_db->escape($pageRS['pageDescription']),
			$this->_db->escape($pageRS['pageKeywords']),
			$this->_db->escape($pageRS['isHomepage']),
			$this->_db->escape($pageRS['isProtected']),
			$this->_db->escape($pageRS['isDevLocked']),
			$this->_db->now,
			$this->_db->escape($pageRS['sysStatus']),
			$this->_db->last_insert);
		$this->_db->query($dQry);
		$draftID = $this->_db->insert_id();
		
		//error_log($dQry . " \n", 3, "/resolutionDevSiteRoot/dev.log");

		//dupicate page content, from the old live version
		//pull it first
		$pcQry = sprintf("SELECT c.*, l.pageID AS contentPageID, l.regionID AS contentRegionID, l.myOrder AS contentMyOrder, l.itemID AS ptrcID 
				FROM sysPageTemplateRegionContent AS l
				LEFT OUTER JOIN sysPageContent AS c ON (l.contentID = c.itemID AND l.pageID = '%d')
				WHERE c.sysOpen = '1';",
			$pageRS['itemID']);
			
		$pcRes = $this->_db->query($pcQry);
		//error_log($pcQry . " \n", 3, "/resolutionDevSiteRoot/dev.log");
		//insert the new copies of the content boxes
		if($this->_db->valid($pcRes)) {
			while ($contentRS = $this->_db->fetch_assoc($pcRes)) {

				if($contentRS['isAnApp'] != "1") { //if this is a regular content box
					$pcQry = sprintf("INSERT INTO sysPageContent (divBoxStyle, adminTitle, htmlContent, includeOverride, isAnApp, appAdminLink, isProtected, divHideTitle, sysOpen) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '1');%s",
						$this->_db->escape($contentRS['divBoxStyle']),
						$this->_db->escape($contentRS['adminTitle']),
						$this->_db->escape($contentRS['htmlContent']),
						$this->_db->escape($contentRS['includeOverride']),
						$this->_db->escape($contentRS['isAnApp']),
						$this->_db->escape($contentRS['appAdminLink']),
						$this->_db->escape($contentRS['isProtected']),
						$this->_db->escape($contentRS['divHideTitle']),
						$this->_db->last_insert);
					$this->_db->query($pcQry);
					$draftContentID = $this->_db->insert_id();
					//error_log($pcQry . " \n", 3, "/resolutionDevSiteRoot/dev.log");
				} else { //otherwise, must be an app, just link it (we don't duplicate apps)
					$draftContentID = $contentRS['itemID'];
				}

				//insert a new link between the newly created content boxes (or apps) and the new draft page
				$qry = sprintf("INSERT INTO sysPageTemplateRegionContent (contentID, pageID, regionID, myOrder, sysOpen) VALUES ('%d', '%d', '%d', '%d', '1');",
					$draftContentID,
					$draftID,
					$contentRS['contentRegionID'],
					$contentRS['contentMyOrder']);
				$this->_db->query($qry);
				//copy over any sys content data properties from live record to draft
				$newPTRCID = $this->_db->insert_id();
                $this->copy_live_content_properties($contentRS['ptrcID'], $newPTRCID);
				//error_log($qry . " \n", 3, "/resolutionDevSiteRoot/dev.log");
			}
		}
		
		return $draftID;

	}

	function perm_delete_specific_page_id_and_content($pageID) 
	{
		
		global $quipp, $notify;
		
		//delete the page record
		$pQry  = sprintf("DELETE FROM sysPage WHERE itemID ='%d';",
			$this->_db->escape($pageID));
		$pRes = $this->_db->query($pQry);
		//yell($pQry);
		
		//get all the links first so that we can grab the specific content records
		$pQry  = sprintf("SELECT * FROM sysPageTemplateRegionContent WHERE pageID ='%d';",
			$this->_db->escape($pageID));
		$pRes = $this->_db->query($pQry);
		if($this->_db->valid($pRes)) {  //grab the page data
			while($clRS = $this->_db->fetch_assoc($pRes)) {
				//purge the associated content box so long as it's not an app
				$pQry  = sprintf("DELETE FROM sysPageContent WHERE itemID ='%d' AND isAnApp = '0';",
				$this->_db->escape($clRS['contentID']));
				$this->_db->query($pQry);
			}
				//purge all the content links
				$pQry  = sprintf("DELETE FROM sysPageTemplateRegionContent WHERE pageID ='%d';",
				$this->_db->escape($pageID));
				$this->_db->query($pQry);
		
		}
		
		return true;
			
	}

	function live_version_exists($systemName) 
	{
		global $quipp, $notify;
		$pQry  = sprintf("SELECT itemID FROM sysPage WHERE sysOpen = '1' AND systemName ='%s' AND sysVersion = 'live';",
			$this->_db->escape($systemName));
		$pRes = $this->_db->query($pQry);
		//yell($pQry);

		if($this->_db->valid($pRes)) {  
			//yes a live exists
			return true;
		} else {
			//this page could not be found
			return false;
		}
	
	}

	function start_over_from_live($pageID) 
	{
		global $quipp, $notify;
		
		//get the data for this page
		$pQry  = sprintf("SELECT * FROM sysPage WHERE sysOpen = '1' AND itemID ='%d' ORDER BY sysStatus DESC, sysDateCreated DESC;",
			$this->_db->escape($pageID));
		$pRes = $this->_db->query($pQry);
		//yell($pQry);

		if($this->_db->valid($pRes)) {  //grab the page data
			$pageRS = $this->_db->fetch_assoc($pRes);
		} else {
			//this page could not be found
			return false;
		}
		
		if($this->live_version_exists($pageRS['systemName'])) {
			//remove all instances of the provided page
			$this->perm_delete_specific_page_id_and_content($pageID);
			return $this->create_draft_copy_of_live_page($pageRS['systemName']);
		} else {
			//this page doesn't have a live version, it only exists as a draft, so just return the same pageID that was provided
			return $pageID;
		}
		
	}

	function approve_draft_and_make_live($pageID)
	{
		global $quipp, $notify, $approvalUtility;
		
		//error_log("Calling approve_draft_and_make_live(" . $pageID . ")  \n", 3, Quipp()->config('yell_log'));
		
		if(!isset($notify)) {
			require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/quipp/Notify.php";
			$notify = new Notify();
		}
		
		if(!isset($notify)) {
			require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/classes/ApprovalUtility.php";
			$approvalUtility = new ApprovalUtility();
		}
		
		//get the data for this page
		$pQry  = sprintf("SELECT * FROM sysPage WHERE sysOpen = '1' AND itemID ='%d' ORDER BY sysStatus DESC, sysDateCreated DESC;",
			$this->_db->escape($pageID));
		$pRes = $this->_db->query($pQry);
		//yell($pQry);

		if($this->_db->valid($pRes)) {  //grab the page data
			$pageRS = $this->_db->fetch_assoc($pRes);
		} else {
			return false;
		}

		//log this action
		$quipp->system_log("Current draft for page [" . $pageRS['label'] . "] has been approved. Replaced live with draft version.");
		
		//get any tickets which have the page ID set and approve them
		$pQry  = sprintf("SELECT itemID FROM sysApprovalTickets WHERE appName = 'page' AND appItemID ='%d' AND sysStatus = 'active';",
			$this->_db->escape($pageID));
		$pRes = $this->_db->query($pQry);
		yell($pQry);

		if($this->_db->valid($pRes)) {  //grab the page data
			while($rs = $this->_db->fetch_assoc($pRes)) {
				$approvalUtility->approve_ticket($rs['itemID']);
			}
		} 	
		
		//first, archive the current live (if one exists)
		$qry = sprintf("UPDATE sysPage SET sysVersion = 'archive', checkOutID = NULL, approveNotifyID = NULL WHERE sysOpen = '1' AND sysVersion = 'live' AND systemName = '%s';",
			$this->_db->escape($pageRS['systemName']));
		$this->_db->query($qry);
		//error_log($qry . " \n", 3, Quipp()->config('yell_log'));

		//then, set this draft page as the live page by setting sysVersion = 'live', this will not touch sysStatus, ensuring new files must be 'activated first'
		$qry = sprintf("UPDATE sysPage SET sysVersion = 'live', checkOutID = NULL WHERE sysOpen = '1' AND itemID = '%d';",
			(int) $pageRS['itemID']);
		$this->_db->query($qry);
		//error_log($qry . " \n", 3, Quipp()->config('yell_log'));
		
		//then create a new draft version and supply it back to the user, if a draft already exists it will be returned, otherwise a new one will be created and it's ID will be returned
		return $this->create_draft_copy_of_live_page($pageRS['systemName']);

	}



	function change_page_system_name($oldSystemName, $newSystemName)
	{
		global $quipp;
		//updating a system name is a huge deal as it's the key tying things to pages, so we must change a few things
		//THIS WHOLE THING SHOULD LIKELY BE A TRANSACTION
		
		//run a check to see if this system name already exists, if it does, return a false
		if(is_numeric($this->_db->return_specific_item(false, "sysPage", "itemID", "--", " systemName = '" . $newSystemName . "'"))) {
			return false;
		}
		
		$qry = sprintf("UPDATE sysPage SET systemName = '%s' WHERE systemName = '%s';",
			$this->_db->escape($newSystemName),
			$this->_db->escape($oldSystemName)
		);


		if($this->_db->query($qry)) {
			$qry = sprintf("UPDATE sysNav SET pageSystemName = '%s' WHERE pageSystemName = '%s';",
				$this->_db->escape($newSystemName),
				$this->_db->escape($oldSystemName)
			);
			if($this->_db->query($qry)) {
				$qry = sprintf("UPDATE sysPageDataLink SET pageSystemName = '%s' WHERE pageSystemName = '%s';",
					$this->_db->escape($newSystemName),
					$this->_db->escape($oldSystemName)
				);
				if($this->_db->query($qry)) {
					$qry = sprintf("UPDATE sysSitesInstanceDataLink SET appItemID = '%s' WHERE appItemID = '%s' AND appID = 'page';",
						$this->_db->escape($newSystemName),
						$this->_db->escape($oldSystemName)
					);
					if($this->_db->query($qry)) {
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}

	}

	//this will make all instances of that systemName as the homepage and un-set any pre-existing
	//there is of course a problem here if the user sets their homepage as a page that is inactive during the un-seat of the current homepage
	//but the expectation is that the user will correct this action when reported with an error
	//- a user should finish creating this page before making it the homepage
	function set_as_home_page($systemName)
	{
		global $quipp;
		//un-set any pages which are marked as the homepage
		$qry = sprintf("UPDATE sysPage SET isHomepage = '0' WHERE isHomepage = '1' AND systemName IN(SELECT appItemID FROM sysSitesInstanceDataLink WHERE appID = 'page' and instanceID = '%s');",
			$this->_db->escape($this->_db->return_specific_item(false, "sysSitesInstanceDataLink", "instanceID", "--", " appItemID = '" . $systemName . "'")));

		if($this->_db->query($qry)) {
			$qry = sprintf("UPDATE sysPage SET isHomepage = '1' WHERE systemName = '%s';",
				$this->_db->escape($systemName));

			if($this->_db->query($qry)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}

	}
	
	
	

	function adjust_password_protect($pageID, $permissionGroups) 
	{
	
		global $quipp, $auth;
		
		if(!isset($auth)) {
			require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/quipp/Auth.php";
			$auth = new Auth();
		}
		
		
		//get details from the page record (this could likely be converted to a single query to be more efficient, but I'm doing this for coding speed right now)
		$privID = $this->_db->return_specific_item($pageID, "sysPage", "privID");
		
			
		//we have groups to 'add' or 'adjust'
		if(is_array($permissionGroups)) {
		
			if(!is_numeric($privID) || $privID == 0) { 
				//we do not have a good privID to work with, create one
				
				$pageSystemName = $this->_db->return_specific_item($pageID, "sysPage", "systemName");
				$pageSystemName = "access_" . $pageSystemName . "_page"; 
				$pageLabel = $this->_db->return_specific_item($pageID, "sysPage", "label");
				$pageLabel = "Can access [" . $pageLabel . "] page";
				
				
				//first check to see if one exists already that we could re-use by checking the permission table for the permission system name
				$oldPrivToReuse = $this->_db->return_specific_item(false, "sysPrivileges", "itemID", "--", " systemName = '" . $pageSystemName . "'");
				if(!is_numeric($oldPrivToReuse)) { //no prermission pre-exists so create one
				
					$qry = sprintf("INSERT INTO sysPrivileges (groupID, systemName, label, myOrder, sysStatus, sysOpen)  VALUES ('4', '%s', '%s', '0', 'active', '1');",
					$this->_db->escape($pageSystemName),
					$this->_db->escape($pageLabel)
					);
				
					if($this->_db->query($qry)) {
						$privID = $this->_db->insert_id();
					} else {
						return false;
					}
				} else {
					$privID = $oldPrivToReuse;
				
				}
			}
			
			$auth->delete_privilege_links($privID);
			
			//write the permission links
			foreach($permissionGroups as $group) {
				$qry = sprintf("INSERT INTO sysUGPLinks (privID, groupID, sysStatus, sysOpen) VALUES ('%d', '%d', 'active', '1');",
				$this->_db->escape($privID),
				$this->_db->escape($group)
				);
				
				$this->_db->query($qry);
			}
			
		} else {
			//there are no groups to set, which must mean this is likely a reset, so we must remove password protection from the record by setting privID = 0
			$auth->delete_privilege_links($privID);
			$privID = 0;
		}
	
		//finally update the page with the appropriate permission record id
		$this->update_page_property($pageID, "privID", $privID);
		return true;
	
	
	}



	function update_page_property($pageID, $fieldName, $value)
	{

		global $quipp, $nav;


		if(!isset($nav)) {
			require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/quipp/Nav.php";
			$nav = new Nav();
		}

		$runDefaultQuery = true;

		//build a query to run
		switch ($fieldName) {

		case "systemName":
			//updating a system name is a huge deal as it's the key tying things to pages, so we must change a few things
			$runDefaultQuery = false;
			if($this->change_page_system_name($this->_db->return_specific_item($pageID, "sysPage", "systemName"), $value)) {
				return true;
			} else {
				return false;
			}
			break;
		case "isHomepage":
			$runDefaultQuery = false;
			if($this->set_as_home_page($this->_db->return_specific_item($pageID, "sysPage", "systemName"), $value)) {
				return true;
			} else {
				return false;
			}

			break;
		case "label":
			//let label update itself as part of the default query, however, let's update the nav too
			//get all of the nav items that have this systemName
			
			//POSSIBLE ISSUE: this doesn't check the instance ID, and probably should as it might change the label on other pages that share the same name.
			
			$qry = sprintf("SELECT itemID FROM sysNav WHERE pageSystemName = '%s';",
				$this->_db->escape($this->_db->return_specific_item($pageID, "sysPage", "systemName")));

			$res = $this->_db->query($qry);
			while ($nprs = $this->_db->fetch_assoc($res)) {
				$nav->rename_nav_item($nprs['itemID'], $value);
			}
			break;
			
		case "templateID":
			//the template will get updated, and update the page record, however, we must determine the primary col in the new template and migrate all the content boxes there
			//get the primary col from the new targeted template
			
			$runDefaultQuery = false; //setting this to false as a safeguard in case the following queries fail. If they work, then we'll set it to true.
			$qry = sprintf("SELECT r.itemID FROM sysPageTemplateRegion AS r LEFT OUTER JOIN sysPageTemplate AS t ON(t.itemID = r.templateID) WHERE r.isDefault = '1' AND t.itemID = '%d'",
			(int) $value);
			$res = $this->_db->query($qry);
	
			if ($this->_db->valid($res)) { 
				$reg = $this->_db->fetch_assoc($res);
				if ($this->_db->valid($res)) { 
					$tmp = $this->_db->fetch_assoc($res);			
					$qry = sprintf("UPDATE sysPageTemplateRegionContent SET regionID = '%d' WHERE  pageID = '%d'",
						(int) $reg['itemID'],
						(int) $pageID);
					$this->_db->query($qry);
					$runDefaultQuery = true; 
				}	
			}			
			
			
			break;


		}


		//yell($qry);
		if($runDefaultQuery) {

			$qry = sprintf("UPDATE sysPage SET %s = '%s' WHERE itemID = '%d';",
				$this->_db->escape($fieldName),
				$this->_db->escape($value),
				$this->_db->escape($pageID)
			);


			if($this->_db->query($qry)) {
				return true;
			} else {
				return false;
			}
		}

	}
	
	function delete_content($contentID, $regionID, $pageID) 
	{
	
		global $quipp;
		
		$qry = sprintf("DELETE FROM sysPageTemplateRegionContent WHERE contentID = '%d' AND pageID = '%d' AND regionID = '%d';",
			(int) $contentID,
			(int) $pageID,
			(int) $regionID);
		
		if ($this->_db->query($qry)) {
			$quipp->system_log("Content Deleted From Page: " . $this->_db->return_specific_item($pageID, "sysPage", "label") . ". " . $qry);
			return true;
		} else {
			return false;
		}

	
	}


}

?>