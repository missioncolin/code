<?php
$killView = false;
require '../includes/init.php';
require 'classes/Content.php';

$meta['title'] = 'Administrative Panel';
$meta['title_append'] = ' &bull; Quipp CMS';

require 'templates/header.php';

//ensure the feedback class is available
if(!isset($feedback)) {
	$feedback = new Feedback();
}


//DECIDE IF YOU ARE ALLOWED TO DO THIS
$approvePages = $auth->has_permission("approvepage");
$modifyPages = $auth->has_permission('modifypages');

if(!$modifyPages) {
	$feedback->message("You cannot modify this page because you are not allowed to modify pages.", "Permissions Notice", 2);
	$killView = true;
}


//This is an always 'edit' state editor. No new records can be created, only edited. So we must detect if a new record needs to be created first, create a skeleton record in the db, and then provide that ID to this editor so that it can edit it.

//IF CREATE NEW (page)
//this checks to see if a 'new' flag is being passed, if it is, a page is created and $_GET['navID'] is set to the newly created page

if(!isset($pageUtility)) {
		require_once("classes/PageUtility.php");
		$pageUtility = new PageUtility($db);
}

$workingWithNewPage = false;

if(!isset($nav)) {
		require_once("../inc/quipp/Nav.php");
		$nav = new Nav();
} 

if(isset($_REQUEST['new']) && $_REQUEST['new'] == "page") {

	if(isset($_REQUEST['navID'])) {
		$idToPass = (int) $_REQUEST['navID'];
		$typeToPass = "nav";
	} elseif(isset($_REQUEST['bucketID'])) {
		$idToPass = (int) $_REQUEST['bucketID'];
		$typeToPass = "bucket";
	}

	$_GET['navID'] = $pageUtility->create_empty_page($idToPass, $typeToPass); //this will create a page and a nav item under the appropriate bucket (supplied), and return the navID
	$_REQUEST['new'] = false;
	$workingWithNewPage = true;
} elseif(isset($_REQUEST['new']) && $_REQUEST['new'] == "link") { 
	
	yell("Creating a new empty nav item.");
	
	if(isset($_REQUEST['navID'])) {
		$idToPass = (int) $_REQUEST['navID'];
		$typeToPass = "nav";
	} elseif(isset($_REQUEST['bucketID'])) {
		$idToPass = (int) $_REQUEST['bucketID'];
		$typeToPass = "bucket";
	}
	
	yell("nav->create_empty_nav_item(" . $idToPass . ", " . $typeToPass . ")");
	$_GET['navID'] = $nav->create_empty_nav_item($idToPass, $typeToPass); //this will create a page and a nav item under the appropriate bucket (supplied), and return the navID
	$_REQUEST['new'] = false;
	

//end of check to see if this is new data
//To do: (create new link)
	
}

//EDIT (Data-Fetch)
//once we have a nav ID, grab its data to determine if we're editing a page or an outbound link
if(isset($_GET['navID'])) {
	
	//get the information from sysNav for this record
	$qry = sprintf("SELECT * FROM sysNav WHERE sysOpen = '1' AND itemID = '%d';",
		(int) $_GET['navID']);
	$res = $db->query($qry);
	
	if($db->valid($res)) { //we've got the nav record
	
		$navRS = $db->fetch_assoc($res);
		
		if(!empty($navRS['url'])) { //if we have a URL we know it's an outbound link
			
			$editType = "link";
							
		
		} else { //otherwise it must be a page we're editing
		
			//create new working draft from the live copy if one wasn't already created above as part of a 'new page' process
			if(!$workingWithNewPage) {
				$pageUtility->create_draft_copy_of_live_page($navRS['pageSystemName']);
				$quipp->system_log("A new draft for page [" . $navRS['pageSystemName'] . "] has been created.");
			}
		
			$editType = "page";
			//there is always a draft version available of every page
			//When a new page is created (which is done above) it is created as a draft, which this then fetches.
			//When a page is approved to go live, a duplicate of it is created as a draft.
			$pQry  = sprintf("SELECT * FROM sysPage WHERE sysOpen = '1' AND systemName ='%s' AND sysVersion = 'draft';",
				$db->escape($navRS['pageSystemName']));
			$pRes = $db->query($pQry);
	
			
			if ($db->valid($pRes)) {  //grab the page data
				$pageRS = $db->fetch_assoc($pRes);
				
			} else { //this 'else' is likely to get removed
				echo "Couldn't find that page. Error.";	
			}
		}
	}
}



//if this page is a protected page, check to see if this user has the priv that is listed with this record
if (isset($pageRS) && $pageRS['editPrivID'] > 0) {
	
	$modifyPagePriv = $auth->has_permission("modify" . $pageRS['systemName'] . "page");

	if (!$modifyPagePriv) {
		$showMasterMessage  = true;
		$showEditPrivMessage = true;
	}

	if ($user->id == "1") {
		$modifyPagePriv   = true;
		$showMasterMessage  = false;
		$showEditPrivMessage = false;
	}
} else {
	$modifyPagePriv = true;
}




//LINK EDITING INTERFACE
if (!$killView){

if ((isset($navRS) && isset($_GET['new']) && $_GET['new'] == 'link')  || $editType == "link") {

?>

<div class="boxStyle">
	<div class="boxStyleContent">
	<div class="boxStyleHeading"> 
			<h2>Outbound Link</h2> 
			
		</div> 
		<div class="clearfix">&nbsp;</div> 
		<div id="template"> 
	
		<table class="clearTable">
			<tbody>
				<tr>
					<td style="width:120px"><label for="linkLabel"><strong>Navigation Label</strong></label></td>
					<td>
						
						<input style="width:300px;" type="text" class="uniform" id="linkLabel" name="linkLabel" value="<?php print $navRS['label']; ?>">
						<p class="formTip">What your visitors will see in navigation headings</p>
					</td>
				</tr>
				<tr>
					<td><label for="linkURL"><strong>Navigation URL</strong></label></td>
					<td>
						
						<input style="width:300px;" type="text" class="uniform" id="linkURL" name="linkURL" value="<?php print $navRS['url']; ?>">
						<p class="formTip">The web address to link to (don't forget the http://)</p>
					</td>
				</tr>
				<tr>
					<td><label for="linkBehaviour"><strong>Behaviour</strong></label></td>	
					<td><?php 
						$targetOptions = array(
							'_self'  => 'Open link in same window',
							'_blank' => 'Open link in new window'
						); 
			
						if(isset($navRS['target'])) {
							$behaviour = $navRS['target'];
						} else {
							$behaviour = "_self";
						}
			
					print get_list("linkBehaviour", $targetOptions, "", "", $behaviour); ?></td>
				</tr>
				<tr>
					<td colspan="2"> 
							
							<button id="outboundLinkSaveChanges" class="btnStyle green">Save Changes</button>
							<input id="currentlyEditingNavID" type="hidden" value="<?php echo $navRS['itemID']; ?>" /> <!-- DO NOT REMOVE currnetlyEditingNavID ITS CRITICAL FOR THE AJAX TO WORK -->
					</td>
				</tr>
			</tbody>
		
		</table>
		
</div></div>
</div>

<?php 

} else if (isset($pageRS)){  

//PAGE EDITING INTERFACE

?>

<div class="boxStyle editHeader">
	<form>
		<input id="currentlyEditingPageID" type="hidden" value="<?php echo $pageRS['itemID']; ?>" /> <!-- DO NOT REMOVE currnetlyEditingPageID ITS CRITICAL FOR THE AJAX TO WORK -->
		<input id="currentlyEditingNavID" type="hidden" value="<?php echo $_GET['navID']; ?>" /> 
		<input id="pagePropertyLabel" name="pagePropertyLabel" type="text" class="formStyle pageTitle" value="<?php print $pageRS['label']; ?>" />
	

	<div class="systemName">
		<label>Address (URL):</label> <?php print $_SERVER['SERVER_NAME']; ?>/
		<input id="pageSystemName" name="pageSystemName" type="text" class="formStyle systemName" value="<?php print $pageRS['systemName']; ?>" />
	</div>
	
	
	</form>
	<div class="actionBtns">
		<a id="previewButton" href="http://<?php echo $pageUtility->get_my_primary_domain($_GET['navID']); ?>/?p=<?php echo $navRS['pageSystemName']; ?>&draft=preview" target="_blank" class="btnStyle">Preview Page</a>
		<button id="startOverFromLive" class="btnStyle red">Start Over From Live</button>
		<button id="submitForReview" class="btnStyle blue">Submit For Review</button>
		<?php if($approvePages) { ?>
		<button id="makeThisLive" class="btnStyle green">Make This Live</button>
		<?php } ?>
	
	</div>
</div>

<div class="boxStyle"><div class="boxStyleContent">
	<div class="boxStyleHeading">
		<h2>Settings</h2>
		<div class="boxStyleHeadingRight"><a href="#" class="hideSection" rel="settingsForm">Hide</a></div>
	</div>
	<div class="clear">&nbsp;</div>
	<div>
	<form class="settings" id="settingsForm">
		<table>
			<tr>
				<td>
				<!-- Specify as Home Page -->
				Make This Page The Home Page <br />
					<input id="makeHomepageCheck" type="checkbox" <?php if($pageRS['isHomepage'] == 1) { print "checked=\"checked\""; } elseif($_REQUEST['OPSet_Homepage']) { print "checked=\"checked\""; } ?>/>
				
					<label for="makeHomepageCheck">This Is The Homepage</label>
				</td>
				
				<td>
				
					Make This Page Password Protected<br />
					<dl id="propertiesGroupsForm" class="propertiesForm">
						<dt>

						<label>Viewable Only By:</label>
						</dt><?php 
					
					$qry = sprintf("SELECT DISTINCT g.itemID, g.nameFull, l.privID FROM sysUGroups AS g LEFT OUTER JOIN sysUGPLinks AS l ON (g.itemID = l.groupID AND l.privID = '%d') WHERE g.sysOpen = '1' ORDER BY g.nameFull ASC;", $pageRS['privID']);
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
											<label><input type="checkbox" name="groups_list[<?php print $grpRS['itemID']; ?>]" id="groups_list[<?php print $grpRS['itemID']; ?>]" value="<?php print $grpRS['itemID']; ?>" <?php print $checkMe; 
												?> /> <?php print $grpRS['nameFull']; ?></label>
								</dd>
					<?php } 
					}  ?>
						
					</dl>
				</td>
				
				<td>
						Who Can Edit Content On This Page<br />
						<?php
					
					
						/********************************************* vv Group Area Do Not Modify vv *********************************************/
						print "<dl id=\"propertiesEditGroupsForm\" class=\"propertiesForm\">
						<dt><label>Content Can Be Modified By:</label></dt>";
						
						
						$qry = sprintf("SELECT DISTINCT g.itemID, g.nameFull, l.privID FROM sysUGroups AS g LEFT OUTER JOIN sysUGPLinks AS l ON (g.itemID = l.groupID AND l.privID = '%d') WHERE g.sysOpen = '1' ORDER BY g.nameFull ASC;", $pageRS['editPrivID']);
						
						$res = $db->query($qry);
						
						//if we get values from the query string, unify these to an array so that we can apply checks in the loop
						if(isset($_REQUEST['edit_group_list']) && is_array($_REQUEST['edit_group_list'])) { 
							foreach($_REQUEST['edit_group_list'] as $groupKey => $groupVal) { 
								$grpArray[$groupKey] = $groupVal; 
							} 
						}
						
						
						if ($db->valid($res)) {
							while($grpRS = $db->fetch_assoc($res)) {
								print "<dd class=\"editGroupListItem\">";
								
								if(!empty($grpArray[$grpRS['itemID']])) { 
									$checkMe = "checked=\"checked\""; 
								} 
								
								if($grpRS['itemID'] == "1" || $grpRS['itemID'] == "2" || $grpRS['itemID'] == "3") { 
									$checkMe = "checked=\"checked\" disabled=\"disabled\""; 
								} elseif(!isset($_REQUEST['edit_group_list']) && !empty($grpRS['privID'])) { 
									$checkMe = "checked=\"checked\""; 
								} elseif(isset($_REQUEST['edit_group_list']) && is_array($_REQUEST['edit_group_list']) && !empty($grpArray[$_REQUEST['edit_group_list[' . $grpRS['itemID'] . ']']])) { $checkMe = "checked=\"checked\""; 
								} else { 
									$checkMe = ""; 
								}
								
								if(isset($_REQUEST['nid']) && $_REQUEST['nid'] == "p") {  
									//this user is creating a new page, therefore we will auto-check off any groups they belong to
									//admin group override (admins are allowed to do everything
									if(returnSpecificItem(false, "sysTBLUGLinks", "userID", false, false, " groupID = '" . $grpRS['itemID'] . "' AND userID = '" . $_SESSION['myId'] . "' AND sysActive = '1' AND sysOpen = '1'") > 0) {
										$checkMe = "checked=\"checked\"";
									}
								}
								
								if($grpRS['itemID'] == "1" || $grpRS['itemID'] == "2" || $grpRS['itemID'] == "3") { 
									$checkMe = "checked=\"checked\" disabled=\"disabled\""; 
								} 
								
								print "<label><input type=\"checkbox\" name=\"edit_group_list[" . $grpRS['itemID'] . "]\" id=\"edit_group_list[" . $grpRS['itemID'] . "]\" value=\"" . $grpRS['itemID'] . "\"" . $checkMe . " /> " . $grpRS['nameFull'] . "</label></dd>";
							} //while($myGrpRS = draggin_fetch_array($getMyGroupsResult)) { 

						} 						
						print "</dl>";
						
						?>
	
	
	
	
	
				
				
				</td>
				<td>
				Keywords For Search Engines<br />
				<textarea class="uniform" id="keywordsForSE" style="height:100px;"><?php print $pageRS['pageKeywords']; ?></textarea>
				
				</td>
				<td>
				Description For Search Engines<br />
				<textarea class="uniform" id="descriptionForSE" style="height:100px;"><?php print $pageRS['pageDescription']; ?></textarea>
				
				</td>
			</tr>
				
				
			
		</table>
	</form>
	
	</div>
</div></div>

<!-- /////////// -->

<div class="boxStyle"><div class="boxStyleContent">
	<div class="boxStyleHeading">
		<h2>Template</h2>
		<div class="boxStyleHeadingRight"><a href="#" class="hideSection" rel="templateForm">Hide</a></div>
	</div>
	<div class="clear">&nbsp;</div>
	<div id="templateForm">
		<?php
			
			$pQry  = sprintf("SELECT itemID, templateName, pathToIcon, grouping FROM sysPageTemplate WHERE sysOpen = '1' ORDER BY grouping ASC, myOrder ASC;");
			$tRes = $db->query($pQry);
			$totalNumOfTemplates = $db->num_rows($tRes);
			//yell($pQry);

			if ($db->valid($tRes)) {  //grab the template data
				$first = true;
				$ti = 0;
				$i = 0;
				echo "<table id=\"adminTemplateSelectionTable\">";
				while($tRS = $db->fetch_assoc($tRes)) {
					$amIChecked = "";
					if($tRS['itemID'] == $pageRS['templateID']) {
						$amIChecked = "checked=\"checked\"";
					
					} 					
					
					$i++;
					$ti++;
					if($first) {
						echo "<tr>";
						$first = false;
						$lastGrouping = $tRS['grouping'];
					} elseif($i > 5 || $lastGrouping != $tRS['grouping']) {
						echo "</tr><tr>";
						$i=1;
					}
					
					echo "<td>
						
							<label>
								<img src=\"$tRS[pathToIcon]\" /><br />
								<input type=\"radio\" name=\"pageTemplate\" class=\"pageTemplateOption\" id=\"pageTemplate" . $tRS['itemID'] . "\" $amIChecked value=\"" . $tRS['itemID'] . "\" /><br />
								" . $tRS['templateName'] . "
								</label>
					</td>";
					
					$amIChecked = "";
					
					if($ti == $totalNumOfTemplates) {
						
						if($i < 4) {
							echo "<td colspan=\"".(4 - $i)."\">&nbsp;</td>";
						}
						echo "</tr>";
					}
					
					$lastGrouping = $tRS['grouping'];
				}
				echo " </table>";
				
			} else {
				echo "<strong> Whoops </strong>, the system says that there are no templates registered in the database! You might want to ask whoever is in charge of managing the technical part of the system to fix this. You'll need templates registered in the database to create pages.";
			
			}
			
			?>
	
	</div>
</div></div>

<!-- ////////////// -->



<div class="boxStyle"><div class="boxStyleContent">
	<?php 
	
	// build template dropdown
	
	
	?>
	<div class="boxStyleHeading">
		<h2>Content</h2>
		<div class="boxStyleHeadingRight">
			&nbsp;
		</div>
	</div>
	<?php
//if (!empty($postBackMessages) && !empty($postBackType)) {
// print alert_box($postBackMessages, $postBackType);
//}

?>

	<?php

	if ($auth->has_permission("canaddapps")) { ?>

		<!-- 'Add An App' Drop Down List Widget -->

<!--

		<form enctype="multipart/form-data" method="post" action="<?php print $_SERVER['PHP_SELF']; ?>?pb=addApplication&navID=<?php print $_GET['navID'];?>">
-->
			<button id="btnAddToApp" class="btnStyle blue" style="font-size:10px; float:right;">Add Widget</button>
			<label for="appToAdd">Widgets: </label>

			<?php print get_list("appToAdd", "sysPageContent", "adminTitle", "WHERE sysOpen = '1' AND isAnApp = '1'", false, "style=\"font-size:10px; width:80%;\"", false, "itemID", false, false, false); ?>
		
		<!-- END OF 'Add An App' Drop Down List Widget -->
	<?php
	}

?>
	<div class="clearfix">&nbsp;</div>

	<div id="template">
	<?php
$box = new Content();
$regionsArray = $box->build_template($pageRS['itemID'], $pageRS['templateID']);
?>
	</div>



	<div class="clearfix">&nbsp;</div>

	</div>

</div></div> <!-- End of <div class="boxStyle"><div class="boxStyleContent">-->

<?php



if (isset($regionsArray) && is_array($regionsArray)) {
	$quipp->js['onload'] .= '$("#template .regionbox").sortable({connectWith: ".regionbox", update: function(event, ui) { update_order(this); }}).disableSelection();';
}

}
?>

<?php
}
require 'templates/footer.php';
?>
