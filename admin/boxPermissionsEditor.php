<?php

require '../includes/init.php';
require 'classes/Content.php';
//yell($_SERVER['DOCUMENT_ROOT']);
require 'templates/headerLight.php';



//$_REQUEST['regionContentID'] = 519;
//$_REQUEST['pageID'] = 264;



if((!isset($_REQUEST['regionContentID']) && !is_numeric($_REQUEST['regionContentID'])) || (!isset($_REQUEST['pageID']) && !is_numeric($_REQUEST['pageID']))) {
	print "Error: Need both a regionContentID and pageID.";
	die();
}

$showEditorForm = true; //default action

if(isset($_REQUEST['save']) && $_REQUEST['save'] == "true") {
	

	
		global $quipp, $db, $auth;
		
		if(!isset($auth)) {
			require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/quipp/Auth.php";
			$auth = new Auth();
		}
		
		
		//get details from the page record (this could likely be converted to a single query to be more efficient, but I'm doing this for coding speed right now)
		$privID = $auth->fetch_or_create_content_priv($_REQUEST['pageID'], $_REQUEST['regionContentID']);
		
			
		//we have groups to 'add' or 'adjust'
		if(is_array($_REQUEST['groups_list'])) {
					
			//we're about to reset to new, so clear out any old ones
			$auth->delete_privilege_links($privID);
			
			//write the permission links
			foreach($_REQUEST['groups_list'] as $group) {
				$qry = sprintf("INSERT INTO sysUGPLinks (privID, groupID, sysStatus, sysOpen) VALUES ('%d', '%d', 'active', '1');",
				$db->escape($privID),
				$db->escape($group)
				);
				
				$db->query($qry);
			}
			
		} else {
			//there are no groups to set, which must mean this is likely a reset, so we must remove password protection from the record by setting privID = 0
			$auth->delete_privilege_links($privID);
			$privID = 0;
		}
	
		$showEditorForm = false;
}

?>
	
	<?php if($showEditorForm) { ?>
	
	<!-- Group Permissions @ Content Level -->
			
			<form method="POST" action="boxPermissionsEditor.php">	
				<dl id="propertiesGroupsFormContentItem" class="propertiesForm">
						<dt>
						<p>By default, everyone can view content on the public front end. If you select a group below, the content becomes visible only to that group. To release back to everyone, unselect all groups.</p>
						<label>Viewable Only By:</label>
						</dt><?php 
						
					$privID = $auth->fetch_or_create_content_priv($_REQUEST['pageID'], $_REQUEST['regionContentID']);
					
					$qry = sprintf("SELECT DISTINCT g.itemID, g.nameFull, l.privID FROM sysUGroups AS g LEFT OUTER JOIN sysUGPLinks AS l ON (g.itemID = l.groupID AND l.privID = '%d') WHERE g.sysOpen = '1' ORDER BY g.nameFull ASC;", $privID);
					$res = $db->query($qry);
					
					//if we get values from the query string, unify these to an array so that we can apply checks in the loop
					if(isset($_REQUEST['groups_list']) && is_array($_REQUEST['groups_list'])) { 
						foreach($_REQUEST['groups_list'] as $groupKey => $groupVal) { 
							$grpArray[$groupKey] = $groupVal; 
						} 
					}
					
						if ($db->valid($res)) {
							while($grpRS = $db->fetch_assoc($res)) { ?>
								<dd class="groupListItem"><?php 
									if(!empty($grpArray[$grpRS['itemID']])) { $checkMe = "checked=\"checked\""; 
									} elseif(!isset($_REQUEST['groups_list']) && !empty($grpRS['privID'])) { 
										$checkMe = "checked=\"checked\""; 
									} elseif(isset($_REQUEST['groups_list']) && is_array($_REQUEST['groups_list']) && !empty($grpArray[$_REQUEST['groups_list[' . $grpRS['itemID'] . ']']])) { 
										$checkMe = "checked=\"checked\""; 
									} else { 
										$checkMe = ""; 
									} ?>
											<label><input type="checkbox" class="uniform" name="groups_list[<?php print $grpRS['itemID']; ?>]" id="groups_list[<?php print $grpRS['itemID']; ?>]" value="<?php print $grpRS['itemID']; ?>" <?php print $checkMe; 
												?> /> <?php print $grpRS['nameFull']; ?></label>
								</dd><br/>
					<?php } 
					}  ?>
						
					</dl>
					<input type="hidden" id="regionContentID" name="regionContentID" value="<?php print $_REQUEST['regionContentID']; ?>" />
					<input type="hidden" id="pageID" name="pageID" value="<?php print $_REQUEST['pageID']; ?>" />
					<input type="hidden" id="save" name="save" value="true" />
					<input type="submit" value="Save" id="saveBtn" name="saveBtn" class="btnStyle green"/>
			</form>
			<!-- Group Permissions @ Content Level -->
			
			
		<?php 
		} else {
			//
			print "<script type=\"text/javascript\">parent.$.fancybox.close();</script>";
		
		}
		//if($showEditorForm) { ?>




<?php 
//Functions used by this editor
	
	require 'templates/footerLight.php';  


?>