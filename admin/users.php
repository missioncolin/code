<?php

require '../includes/init.php';
require 'classes/Editor.php';
$meta['title'] = 'Users Editor';
$meta['title_append'] = ' &bull; Quipp CMS';

if(!$auth->has_permission("modifyusers")) {
	$quipp->system_log("User manager Has Been Blocked Because of Insufficient Privileges.");
	print alert_box("You do not have sufficient privileges to view the user manager.  Would you like to <a href=\"/admin/\">Return to Management System Home</a>?", 2);
	require 'templates/footer.php';
	die();
}


$te = new Editor();

//set the primary table name
$primaryTableName = "sysUsers";
if (isset($_GET['id'])) {
	$_GET['id'] = intval($_GET['id'], 10);
}
//editable fields

$fields[] = array(
	'label'   => "User Name",
	'dbColName'  => "userIDField",
	'tooltip'   => "eg. hi@example.com",
	'writeOnce'  => false,
	'widgetHTML' => "<input style=\"width:300px;\" type=\"text\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
	'valCode'   => "RQvalALPH",
	'dbValue'   => false,
	'stripTags' => true
);


$fields[] = array(
	'label'   => "Password",
	'dbColName'  => 'userIDPassword',
	'tooltip'   => "If you don't enter/confirm a new password, your password will not be changed.",
	'writeOnce'  => false,
	'widgetHTML' => "<input style=\"width:300px;\" type=\"text\" id=\"RQvalALPHPassword\" name=\"RQvalALPHPassword\" value=\"\" />",
	'valCode'   => "RQvalALPH",
	'dbValue'   => false,
	'stripTags' => true
);



$statuses = array(
	'active' => 'Active',
	'inactive' => 'Inactive',
	'public' => 'Public',
	'disabled' => 'Disabled'
);
$status = 'active';
if (isset($_GET['id'])) {
	$status = $db->return_specific_item($_GET['id'], 'sysUsers', 'sysStatus');
}
$fields['sysStatus'] = array(
	'label'   => "Status",
	'dbColName'  => 'sysStatus',
	'tooltip'   => '',
	'writeOnce'  => false,
	'widgetHTML' => get_list('RQvalALPHStatus', $statuses, false, false, $status),
	'valCode'   => "RQvalALPH",
	'dbValue'   => false,
	'stripTags' => true
);

if ($auth->type == 'ad') {
	print "<div class=\"alertBoxFunctionAD\" style=\"background-image: url('/images/admin/adConnected.png'); display:block;\"> <strong>Connected To Active Directory:</strong> The user management system is connected to the Active Directory at <em>". implode(", ", $auth->ad['domain_controllers']) ."</em>. </div>";
}


//display logic

if (!isset($_GET['id'])) { $_GET['id'] = null; }

$fieldColNames = '';
$fieldColValues = '';
//action = database interactivity, these standard queries will do for most single table interactions, you may need to replace with your own
if (!isset($_REQUEST['action'])) { $_REQUEST['action'] = null; }

switch ($_REQUEST['action']) {
case "insert":

	//this insert query will work for most single table interactions, you may need to cusomize your own

	//the following loop populates 2 strings with name value pairs
	//eg.  $fieldColNames = 'articleTitle','contentBody',
	//eg.  $fieldColValues = 'Test Article Title', 'This is my test article body copy',
	//yell($_REQUEST);
	//yell($fields);

	foreach ($fields as $dbField) {
		if ($dbField['dbColName'] != false) {
			
			$requestFieldID = $dbField['valCode'] . str_replace(" ", "_", $dbField['label']);
			if ($dbField['dbColName'] == 'nameSystem') { 
				$_REQUEST[$requestFieldID] = slug($_POST['RQvalALPHGroup_Name']);
			}
			if ($dbField['dbColName'] == 'userIDPassword') {
					$fieldColValues .= " MD5('" . $db->escape($_REQUEST[$requestFieldID], $dbField['stripTags']) . "'),";
			} else {
				$fieldColValues .= "'" . $db->escape($_REQUEST[$requestFieldID], $dbField['stripTags']) . "',";
			}
			$fieldColNames .= $dbField['dbColName'] . ",";
			
		}
	}
	$fieldColValues .= 'NOW(),';
	$fieldColNames .= 'regDate,';

	//trim the extra comma off the end of both of the above vars
	$fieldColNames = substr($fieldColNames, 0, strlen($fieldColNames) - 1);
	$fieldColValues = substr($fieldColValues, 0, strlen($fieldColValues) - 1);

	$qry = sprintf("INSERT INTO %s (%s) VALUES (%s)", (string) $primaryTableName, (string) $fieldColNames, (string) $fieldColValues);
	
	print $te->commit_a_modify_action($qry, "Insert");
	$_GET['id'] = $db->insert_id();

	
	break;
case "update":

	//this default update query will work for most single table interactions, you may need to cusomize your own

	foreach ($fields as $dbField) {
		if ($dbField['dbColName'] != false) {
			$requestFieldID = $dbField['valCode'] . str_replace(" ", "_", $dbField['label']);
		
			if ($dbField['dbColName'] == 'userIDPassword') {
				if ($_REQUEST[$requestFieldID] != '') {
					$fieldColValue = "MD5('" . $db->escape($_REQUEST[$requestFieldID]) . "'),";
					$fieldColNames .= "" . $dbField['dbColName'] . " = " . $fieldColValue;
				}
			} else {
				$fieldColValue = "'" . $db->escape($_REQUEST[$requestFieldID]) . "',";
				$fieldColNames .= "" . $dbField['dbColName'] . " = " . $fieldColValue;
			}
		}
	}

	//trim the extra comma off the end of the above var
	$fieldColNames = substr($fieldColNames, 0, strlen($fieldColNames) - 1);


	$qry = sprintf("UPDATE %s SET %s WHERE itemID = '%s'", (string) $primaryTableName, (string) $fieldColNames, (int) $_GET['id']);

	print $te->commit_a_modify_action($qry, "Update");

	break;
case "delete":

	//this delete query will work for most single table interactions, you may need to cusomize your own
	
	
	$qry = sprintf("UPDATE %s SET sysOpen = '0' WHERE itemID = '%d'", 
		(string) $primaryTableName, 
		(int) $_GET['id']);
	print $te->commit_a_modify_action($qry, "Delete");

	break;
}



if (isset($_POST['action']) && ($_POST['action'] == 'insert' || $_POST['action'] == 'update') && !$db->error()) {
	
	
	//yell('print', $_POST);
	
	// insert the groups

	if (isset($_POST['my_groups_list']) && is_array($_POST['my_groups_list'])) { 
		
		$qry = sprintf("DELETE FROM sysUGLinks WHERE userID='%d';", 
			(int) $_GET['id']);
		$db->query($qry);
		
		$qry = "INSERT INTO sysUGLinks (userID, groupID) VALUES ";
		foreach ($_POST['my_groups_list'] as $groupID) { 
			$qry .= sprintf("('%d', '%d'),", 
				(int) $_GET['id'],
				(int) $groupID);
		}
		$qry = substr($qry, 0, -1);
		$db->query($qry);
	}	
	$db->query('OPTIMIZE TABLE sysUGLinks');
	
	/** insert the site permissions **/
        
    //delete all first
    $qry = sprintf("DELETE FROM sysUSites WHERE userID='%d';",
            (int) $_GET['id']);
    $db->query($qry);
    
    if (isset($_POST["my_sites_list"]) && is_array($_POST["my_sites_list"])){
        $sQry = "INSERT INTO `sysUSites` (`userID`, `siteID`, `sysDateCreated`) VALUES ";
        $sAdded = 0;
        foreach($_POST["my_sites_list"] as $siteID){
            if ((int)$siteID > 0 && $sAdded != (int)$siteID){
                $sQry .= sprintf("('%d', '%d',NOW()),",
                    (int)$_GET['id'],
                    (int)$siteID
                );
                $sAdded = $siteID;
            }
        }
        $sQry = rtrim($sQry,",");
        $db->query($sQry);
    }
    $db->query('OPTIMIZE TABLE sysUSites');
	
	
	if (isset($_POST['meta']) && is_array($_POST['meta'])) {
		$qry = sprintf("DELETE FROM sysUGFValues WHERE userID='%d';", 
			(int) $_GET['id']);
		$db->query($qry);
		
		
		$qry = sprintf("SELECT DISTINCT f.itemID, f.fieldLabel, f.validationCode, f.sysIsADField, f.sysADFieldName, f.sysADFieldNameClean, f.myOrder
			FROM sysUGFields AS f
				LEFT OUTER JOIN sysUGFLinks as fglinks ON(f.itemID = fglinks.fieldID)
			WHERE f.sysOpen = '1' 
			AND fglinks.groupID IN (SELECT groupID FROM sysUGLinks WHERE userID = '%d')
			ORDER BY f.myOrder ASC;",
				(int) $_GET['id']);
		$res = $db->query($qry);
		
		if ($db->valid($res)) {
			
			if ((!isset($user->ad) || isset($user->ad) && !is_object($user->ad)) && $auth->type == 'ad') {
				$user->ad = new adLDAP();
			}
			
			$userName = $db->return_specific_item($_GET['id'], "sysUsers", "userIDField");
			
			while ($udRS = $db->fetch_assoc($res)) {
			
				if ($udRS['sysIsADField'] == '1' && $auth->type == 'ad') {
					if (isset($_POST['meta'][$udRS['validationCode'] . str_replace(" ", "_", $udRS['fieldLabel'])])) {
						$user->ad->user_modify(addslashes(strtoupper($userName)), array($udRS['sysADFieldNameClean'] => $_POST['meta'][$udRS['validationCode'] . str_replace(" ", "_", $udRS['fieldLabel'])]));
					}
				
				} else if (isset($_POST['meta'][$udRS['validationCode'] . str_replace(" ", "_", $udRS['fieldLabel'])])) {
					$qry = sprintf("INSERT INTO sysUGFValues (userID, fieldID, value, sysStatus, sysOpen) VALUES ('%d', '%d', '%s', 'active', '1')",
						(int) $_GET['id'],
						(int) $udRS['itemID'],
						$db->escape($_POST['meta'][$udRS['validationCode'] . str_replace(" ", "_", $udRS['fieldLabel'])]));
					$db->query($qry);
				

				}
			
			}
		
		}
	
	}
	
	header('Location:' . $_SERVER['PHP_SELF'] . '?' . strtolower($_POST['action']) . '=true');
}

if (isset($_GET['view'], $_GET['id']) && $_GET['view'] == 'masquerade' && is_numeric($_GET['id']) && $auth->has_permission('canMasquerade')) {
        
        $masqueradeAccount = new User($db, $_GET['id']);

        $quipp->system_log('User ' . $user->username . ' has masqueraded to user ' . $masqueradeAccount->username, "authentication");

        $_SESSION['isMasquerading'] = $_SESSION['userID'];
        $_SESSION['userID'] = $_GET['id'];
        
        header('Location: /?masquerade=' . (int)$_GET['id']);
        die();
} elseif (isset($_GET['view'], $_GET['id']) && $_GET['view'] == 'masquerade') {
    
    $message = 'Masquerading failed';
}


if (!isset($_GET['id'])) {
	$_GET['id'] = null;
}



$addFieldsBuffer ="<dl id=\"groupFormFields\">";

$gQry = sprintf("SELECT *, IF((SELECT itemID FROM sysUGLinks AS l WHERE l.userID='%d' AND groupID=g.itemID),'1','0') AS hasGLink
	FROM sysUGroups AS g
	WHERE g.sysOpen = '1'
	ORDER BY g.nameFull;",
		(int) $_GET['id']);
$gRes = $db->query($gQry);

while ($gRS = $db->fetch_assoc($gRes)) {
	$checkMe = (!empty($gRS['hasGLink']) || !empty($_POST['my_groups_list'][$gRS['itemID']])) ? ' checked="checked"' : '';

	$addFieldsBuffer .= '<dd><input type="checkbox" onchange="updateFields(\'' . $_GET['id'] . '\');"  class="groupForm" name="my_groups_list[' . $gRS['itemID'] . ']" id="my_groups_list[' . $gRS['itemID'] . ']" value="' . $gRS['itemID'] . '"' . $checkMe . ' /> <label for="my_groups_list[' . $gRS['itemID'] . ']" class="checkbox">' . $gRS['nameFull'] . '</label></dd>'; }

$addFieldsBuffer .= "</dl>";


$fields[] = array(
	'label'   => "User Groups",
	'dbColName'  => false,
	'tooltip'   => "What group this user is apart of. Each group has different meta fields applied, which can be edited after creation",
	'writeOnce'  => false,
	'widgetHTML' => $addFieldsBuffer, // <-- the contents of this variable are built above
	'valCode'   => false,
	'dbValue'   => false
);

/**
* This section creates a list of available sites that a user can be assigned to. new application records will have to be assigned to one or more sites 
*/
$sitesFieldBuffer = '<dl id="siteFormFields">';
$sQry = sprintf("SELECT *, IF((SELECT itemID FROM sysUSites AS u WHERE u.userID='%d' AND u.siteID = s.itemID),'1','0') AS hasSLink
	FROM sysSites AS s
	WHERE s.sysOpen = '1' AND s.sysStatus = 'active'
	ORDER BY s.title;",
    (int) $_GET['id']);
$sRes = $db->query($sQry);

while ($sRS = $db->fetch_assoc($sRes)){
    $checkMe = (!empty($sRS['hasSLink']) || !empty($_POST['my_sites_list'][$sRS['itemID']])) ? ' checked="checked"' : '';
    $sitesFieldBuffer .= '<dd><input type="checkbox"  class="groupForm" name="my_sites_list[' . $sRS['itemID'] . ']" id="my_sites_list[' . $sRS['itemID'] . ']" value="' . $sRS['itemID'] . '"' . $checkMe . ' /> <label for="my_sites_list[' . $sRS['itemID'] . ']" class="checkbox">' . $sRS['title'] . '</label></dd>';
}

$sitesFieldBuffer .= '</dl>';

$fields[] = array(
    'label'   => "Sites",
    'dbColName'  => false,
    'tooltip'   => "Users will only have the option to post content to sites they are editors for. At least one is required",
    'writeOnce'  => false,
    'widgetHTML' => $sitesFieldBuffer, // <-- the contents of this variable are built above
    'valCode'   => false,
    'dbValue'   => false
);

require 'templates/header.php';
?>
<h1>Site Users</h1>
	<p>These are all of the members of your web site. You can use this tool to manually create and manage users as well as change their information and group memberships.</p>
	<p>&nbsp;</p>
	<p><a href="/admin/groups.php">Manage Group Permissions</a></p>
<div class="boxStyle">
	<div class="boxStyleContent">
		<div class="boxStyleHeading">
			<h2>Edit</h2>
			<div class="boxStyleHeadingRight">
				<input type="button" name="newItem" class="btnStyle blue" id="newItem" onclick="javascript:window.location.href='<?php print $_SERVER['PHP_SELF']; ?>?view=edit';" value="New" />

			</div>
		</div>
		<div class="clearfix">&nbsp;</div>
		<div id="template">
<?php



//view = view state, these standard views will do for most single table interactions, you may need to replace with your own
if (!isset($_REQUEST['view'])) { $_REQUEST['view'] = null; }

switch ($_REQUEST['view']) {
case "edit": //show an editor for a row (existing or new)

	//determine if we are editing an existing record, otherwise this will be a 'new'

	$action = "insert";

	//$_GET['id'] = base_convert($_GET['id'], 36, 10);

	if (is_numeric($_GET['id'])) { //if an ID is provided, we assume this is an edit and try to fetch that row from the single table


		$qry = sprintf("SELECT * FROM $primaryTableName WHERE itemID = '%d' AND sysOpen = '1';",
			(int) $_GET['id']);

		$res = $db->query($qry);


		if ($db->valid($res)) {
			$fieldValue = $db->fetch_assoc($res);
			foreach ($fields as &$itemField) {
				if (is_string($itemField['dbColName'])) {
					$itemField['dbValue'] = $fieldValue[$itemField['dbColName']];
				}
			}

			$action = "update";
		}


	}


	if (isset($message) && $message != '') {
		print $message;
	}

	$formBuffer = "
					<form enctype=\"multipart/form-data\" name=\"tableEditorForm\" id=\"tableEditorForm\" method=\"post\" action=\"" . $_SERVER['REQUEST_URI'] .  "\">
					<table>
				";

	//print the base fields
	$f=0;
	foreach ($fields as $field) {

		$formBuffer .= "<tr>";
		//prepare an ID and Name string with a validation string in it

		if ($field['dbColName'] != false) {

			$newFieldIDSeed = str_replace(" ", "_", $field['label']);
			$newFieldID = $field['valCode'] . $newFieldIDSeed;

			$field['widgetHTML'] = str_replace("FIELD_ID", $newFieldID, $field['widgetHTML']);

			//set value if one exists
			if ($field['dbColName'] == 'sysStatus') {
				if ($field['dbValue'] == 'active') {
					$field['widgetHTML'] = str_replace("FIELD_VALUE", 'checked="checked"', $field['widgetHTML']);
				} else {
					$field['widgetHTML'] = str_replace("FIELD_VALUE", '', $field['widgetHTML']);
				}
			} else {
				if (isset($_POST[$newFieldID]) && (isset($message) && $message != '' || !isset($message))) {
					$field['dbValue'] = $_POST[$newFieldID];
				}
				$field['widgetHTML'] = str_replace("FIELD_VALUE", $field['dbValue'], $field['widgetHTML']);
			}

		}
		//write in the html
		$formBuffer .= "<td valign=\"top\" style=\"width:180px\"><label for=\"".$newFieldID."\">" . $field['label'] . "</label></td><td>" . $field['widgetHTML'] . " <p class=\"tooltip\">" . $field['tooltip'] . "</p></td>";
		$formBuffer .= "</tr>";
	}
	
	
	
	// build the meta editor
	$formBuffer .= "</table><div id=\"metaFields\">" . $user->build_user_editor($_GET['id'])  . "</div><table>";

	
	
	
	
	
	//temp
	$id = null;
	$formAction = null;
	//end temp

	$formBuffer .= "<tr class=\"last\"><td>
					<fieldset>
					<input type=\"hidden\" name=\"action\" id=\"action\" value=\"$action\" />";

	if ($action == "update") { //add in the id to pass back for queries if this is an edit/update form
		$formBuffer .= "<input type=\"hidden\" name=\"id\" id=\"id\" value=\"".$_GET['id']."\" />";
	}

	$formBuffer .= "
					<input type=\"button\" class=\"btnStyle\" name=\"cancelUserForm\" id=\"cancelUserForm\" onclick=\"javascript:window.location.href='" . $_SERVER['PHP_SELF'] . "';\" value=\"Cancel\" />
					<input type=\"submit\" class=\"btnStyle green\" name=\"submitUserForm\" id=\"submitUserForm\" value=\"Save Changes\" />
					</fieldset>
					</td></tr>";
	$formBuffer .= "</table></form>";

	//print the form
	print $formBuffer;
	break;
	
	default: //(list)
            $offset  = 0;
            $page    = 1;
            $display = 50;
            
            if (isset($_GET['page'])) {
                $page   = (int) $_GET['page'];
                $offset = ($page - 1) * $display;
            }

            $search = '';
            // set up the search
            if (isset($_GET['q']) && $_GET['q'] != '') {
            
                $search .= sprintf(" AND u.userIDField LIKE '%%%s%%'", $db->escape($_GET['q']));
            }
            
            if (isset($_GET['g']) && $_GET['g'] != '' && $_GET['g'] != '0') {
                $search .= sprintf(" AND l.groupID = '%d'", $db->escape($_GET['g']));
            
            }
            
            
            //list table query:
            $qry = sprintf("SELECT SQL_CALC_FOUND_ROWS u.itemID, u.userIDField, u.lastLoginDate, u.sysStatus, u.sysUser AS sysGroup, (SELECT GROUP_CONCAT(g.nameFull) FROM sysUGLinks AS l LEFT JOIN sysUGroups AS g ON l.groupID=g.itemID WHERE l.userID=u.itemID) as groups
                FROM sysUsers AS u
                LEFT OUTER JOIN sysUGLinks AS l ON u.itemID=l.userID
                WHERE u.sysOpen = '1' 
                %s
                GROUP BY u.itemID
                ORDER BY u.sysUser DESC, u.userIDField 
                LIMIT %d,%d",
                    $search,
                    (int) $offset,
                    $display);
            $res = $db->query($qry);
            
            // get the total rows
            $rows        = $db->query("SELECT FOUND_ROWS();");
            list($total) = $db->fetch_array($rows);
            
            //print an editor with basic controls            
           ?>
            <form method="get">
                <div style="text-align:right; padding:0 0 10px 10px;">

                    <input type="text" name="q" id="q" placeholder="Username" />
                    <?php echo get_list('g', 'sysUGroups', 'nameFull', "WHERE sysOpen = '1'", false, '', '', 'itemID', false, false, '- User Group -'); ?>
                    <input type="submit" value="Search" />  <input type="button" onclick="javascript:window.location='/admin/users.php/';" value="Cancel" />
                </div>
            </form>
            
            <table id="adminTableList" class="adminTableList tablesorter" width="100%" cellpadding="5" cellspacing="0" border="1">
                
				    <tr>
				        <th>Username</th>
				        <th>Last Login Date</th>
				        <th>Status</th>
				        <th>Groups</th>
				        <th>&nbsp;</th>
				        <th>&nbsp;</th>
				        <?php if ($auth->has_permission('canMasquerade')) { ?><th>&nbsp;</th><?php } ?>
                    </tr>
                </thead>
                <?php
                
                if ($total > $display) {
                ?>
                <tfoot>
                    <tr>
                        <td colspan="6">
                
                            <?php
                            $searchParams = '';
                            if (isset($_GET['q'])) {
                                $searchParams .= 'q=' . $_GET['q'] . '&amp;';
                            }
                            if (isset($_GET['g'])) {
                                $searchParams .= 'g=' . (int) $_GET['g'] . '&amp;';
                            }
                            
                            echo pagination($total, $page, $url = '/admin/users.php/?' . $searchParams . 'page=', $display, false);
                            ?>
                            
                        </td>
                    </tr>
                </tfoot>
                <?php 
                }
                ?>
                <tbody>
                    <?php
                    
                    
                    if (is_resource($res) && $db->num_rows($res) > 0) { 
                        while ($u = $db->fetch_assoc($res)) {
                            
                    ?>
                    
                    <tr>
                        <td><?php echo $u['userIDField']; ?></td>
                        <td><?php echo $u['lastLoginDate']; ?></td>
                        <td><?php echo ucwords($u['sysStatus']); ?></td>
                        <td><?php echo $u['groups']; ?></td>
                        <?php if (isset($u['sysGroup']) && $u['sysGroup'] == '1' || isset($u['sysOpen']) && $u['sysOpen'] == '0') { ?>
                            <td style="width:50px;" align="center">-</td>
                        <?php } else { ?>
                            <td style="width:50px;" align="center"><input class="btnStyle red noPad" id="btnDelete_<?php echo $u['itemID']; ?>" type="button" onclick="javascript:confirmDelete('?action=delete&id=<?php echo $u['itemID']; ?>');" value="Delete" /></td>
                        <?php } ?>
                        <td style="width:50px;" align="center"><input class="btnStyle blue noPad" id="btnEdit_<?php echo $u['itemID']; ?>" type="button" onclick="javascript:window.location='?view=edit&id=<?php echo $u['itemID']; ?>';" value="Edit" /></td>
                        <?php if ($auth->has_permission('canMasquerade')) { ?>
                        <td style="width:50px;" align="center">
                            <?php if ($u['itemID'] != $_SESSION['userID']) { ?>
                            <input class="btnStyle green noPad" id="btnEdit_<?php echo $u['itemID']; ?>" type="button" onclick="javascript:window.location='?view=masquerade&id=<?php echo $u['itemID']; ?>';" value="Masquerade" />
                            <?php } else { echo '--'; } ?>
                        </td>
                        <?php } ?>
                    </tr>
                                        
                    <?php
                        }
                    }
                    ?>
                
                </tbody>
            </table>
            <?php
                
            
            //to pass more advanced controls, you'll need to create your own $fields array and pass it directly to $te->display_editor_list($fields);
            break;
    }
    
    
    
    
    
    
    //end of display logic
    //button to manage groups
    ?>
    </div>
    		<div class="clearfix">&nbsp;</div>
    
    	</div>
    
    </div>
    



<?php
require 'templates/footer.php';
?>
