<?php

require '../includes/init.php';
require 'classes/Editor.php';
$meta['title'] = 'Groups Editor';
$meta['title_append'] = ' &bull; Quipp CMS';

require 'templates/header.php';
?>

<?php
$te = new Editor();

//set the primary table name
$primaryTableName = "sysUGroups";
if (isset($_GET['id'])) {
	$_GET['id'] = intval($_GET['id'],10);
}
//editable fields

$fields[] = array(
	'label'   => "Group Name",
	'dbColName'  => "nameFull",
	'tooltip'   => "The human readable name for the group. eg. Summer Interns",
	'writeOnce'  => false,
	'widgetHTML' => "<input style=\"width:300px;\" type=\"text\" class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
	'valCode'   => "RQvalALPH",
	'dbValue'   => false,
	'stripTags' => true
);


$fields[] = array(
	'label'   => "System Name",
	'dbColName'  => "nameSystem",
	'tooltip'   => "A short name that will be used by the system. Automatically created.",
	'writeOnce'  => false,
	'widgetHTML' => "<input style=\"width:300px;\" readonly=\"readonly\" type=\"text\" class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
	'valCode'   => "RQvalALPH",
	'dbValue'   => false,
	'stripTags' => true
);


/********************************************* vv Custom Privilege Area Do Not Modify vv *********************************************/
if (!isset($_GET['id'])) {
	$_GET['id'] = null;
}

if (!isset($_REQUEST['privs_list'])) {
	$_REQUEST['privs_list'] = null;
}


$privBuffer = '<dl>';

if ($_GET['id'] != 1) {
	$pgQry = "SELECT g.*, COUNT(p.itemID) AS numLinks
		FROM sysPrivilegeGroups AS g
		LEFT OUTER JOIN sysPrivileges AS p ON(g.itemID = p.groupID)
		WHERE g.sysOpen = '1'
		GROUP BY g.itemID, g.systemName, g.label, g.pageDescription, g.sysStatus, g.sysOpen
		ORDER BY g.itemID;";
	$pgRes = $db->query($pgQry);

	while ($pgRS = $db->fetch_assoc($pgRes)) {
		if ($pgRS['numLinks'] > 0) {

			$privBuffer .= "<dt class=\"privGroups\"><label>" . $pgRS['label'] . "</label> - " . $pgRS['pageDescription'] . "</dt>";


			$getMyPrivs= sprintf("SELECT DISTINCT p.itemID, p.label, p.groupID, p.myOrder, p.systemName, l.groupID
				FROM sysPrivileges AS p
				LEFT OUTER JOIN sysUGPLinks AS l ON (p.itemID = l.privID AND l.groupID = '%d')
				WHERE p.sysOpen = '1'
				AND p.groupID = '%d'
				ORDER BY p.itemID ASC, p.groupID, p.label ASC;",
				(int) $_GET['id'],
				(int) $pgRS['itemID']);
			$getMyPrivsResult = $db->query($getMyPrivs);

			if ($_REQUEST['privs_list']) {
				foreach ($_REQUEST['privs_list'] as $privKey => $privVal) {
					$privArray[$privKey] = $privVal;
				}
			}

			while ($privRS = $db->fetch_assoc($getMyPrivsResult)) {
				$privBuffer .= "<dd class=\"groupListItem\">";

				$checkMe = null;
				if (!empty($privArray[$privRS['itemID']]) || !$_REQUEST['privs_list'] && !empty($privRS['groupID'])) {
					$checkMe = "checked=\"checked\"";
				}

				$privBuffer .= "<input type=\"checkbox\" class=\"uniform\" name=\"privs_list[".$privRS['itemID']."]\" id=\"privs_list[" . $privRS['itemID']. "]\" value=\"" . $privRS['itemID'] . "\" $checkMe /> <label class=\"checkbox\" for=\"privs_list[" . $privRS['itemID']. "]\">" .  $privRS['label'] . "</label>";
			}
			$privBuffer .= "</dd>";
		}
	}
} else {
	$privBuffer .= " <dt class=\"privGroups\"><span class=\"formTip\">This is the system group.  All privileges will be applied automatically.</span></dt>";
}
$privBuffer .= " </dl>";
/********************************************* ^^ Privilege Area Do Not Modify ^^ *********************************************/
//print $privBuffer;


$fields[] = array(
	'label'   => "Privileges",
	'dbColName'  => false,
	'tooltip'   => "What this group is allowed to do on the system",
	'writeOnce'  => false,
	'widgetHTML' => $privBuffer, // <-- the contents of this variable are built above
	'valCode'   => false,
	'dbValue'   => false
);


/******************************User Fields ****************************************/
$addFieldsBuffer ="<dl id=\"groupFormFields\">";
$myGrpRS = null;
$groupRS = null;
$getMyFieldsResult = $db->query($getMyFields = "SELECT f.itemID, f.fieldLabel, l.itemID AS hasGFLink
				FROM sysUGFields AS f LEFT OUTER JOIN sysUGFLinks AS l ON (l.fieldID = f.itemID AND l.groupID = '" . $_GET['id'] . "')
				WHERE f.sysOpen = '1' ORDER BY f.myOrder ASC;");

while ($myGrpRS = $db->fetch_assoc($getMyFieldsResult)) {
	if (!empty($myGrpRS['hasGFLink'])) {
		$checkMe = " checked=\"checked\"";
	} elseif (!empty($_POST['my_fields_list'][$myGrpRS['itemID']])) {
		$checkMe = " checked=\"checked\"";
	} else {
		$checkMe = "";
	}
	$addFieldsBuffer .= "<dd><input type=\"checkbox\" class=\"uniform\" name=\"my_fields_list[" . $myGrpRS['itemID'] . "]\" id=\"my_fields_list[" . $myGrpRS['itemID'] . "]\" value=\"" . $myGrpRS['itemID'] . "\"" . $checkMe . " /> <label for=\"my_fields_list[" . $myGrpRS['itemID'] . "]\" class=\"checkbox\">" . $myGrpRS['fieldLabel'] . "</label></dd>"; }

$addFieldsBuffer .= "</dl>";
/******************************End Of User Fields ****************************************/
/********************************************* vv Privilege Area Do Not Modify vv *********************************************/


$fields[] = array(
	'label'   => "User Fields",
	'dbColName'  => false,
	'tooltip'   => "Data collected for users of this group",
	'writeOnce'  => false,
	'widgetHTML' => $addFieldsBuffer, // <-- the contents of this variable are built above
	'valCode'   => false,
	'dbValue'   => false
);

?>



	<h1>Site Groups</h1>
	<p>Users are 'grouped' mostly to facilitate privileges. You can create and manage your groups here and record what a group can/can't do and what information should be collected from group members.</p>
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
			$fieldColNames .= $dbField['dbColName'] . ",";
			$fieldColValues .= "'" . $db->escape($_REQUEST[$requestFieldID], $dbField['stripTags']) . "',";
		}
	}

	//trim the extra comma off the end of both of the above vars
	$fieldColNames = substr($fieldColNames, 0, strlen($fieldColNames) - 1);
	$fieldColValues = substr($fieldColValues, 0, strlen($fieldColValues) - 1);

	$qry = sprintf("INSERT INTO %s (%s) VALUES (%s)", (string) $primaryTableName, (string) $fieldColNames, (string) $fieldColValues);
	
	print $te->commit_a_modify_action($qry, "Insert");
	$_GET['id'] = $db->insert_id();
	yell($qry);
	
	break;
case "update":

	//this default update query will work for most single table interactions, you may need to cusomize your own

	foreach ($fields as $dbField) {
		if ($dbField['dbColName'] != false) {
			$requestFieldID = $dbField['valCode'] . str_replace(" ", "_", $dbField['label']);
			$fieldColValue = "'" . $db->escape($_REQUEST[$requestFieldID]) . "',";
			$fieldColNames .= "" . $dbField['dbColName'] . " = " . $fieldColValue;
		}
	}

	//trim the extra comma off the end of the above var
	$fieldColNames = substr($fieldColNames, 0, strlen($fieldColNames) - 1);


	$qry = sprintf("UPDATE %s SET %s WHERE itemID = '%s'", (string) $primaryTableName, (string) $fieldColNames, (int) $_GET['id']);

	print $te->commit_a_modify_action($qry, "Update");

	break;
case "delete":

	//this delete query will work for most single table interactions, you may need to cusomize your own
	
	
	$qry = sprintf("UPDATE %s SET sysOpen = '0' WHERE itemID = '%d' AND sysGroup='0'", 
		(string) $primaryTableName, 
		(int) $_GET['id']);
	print $te->commit_a_modify_action($qry, "Delete");

	break;
}



if (isset($_POST['action']) && ($_POST['action'] == 'insert' || $_POST['action'] == 'update')) {
	
	
	
	
	
	
	// insert the privs and fields
	if (isset($_POST['privs_list']) && is_array($_POST['privs_list'])) { 

		$qry = sprintf("DELETE FROM sysUGPLinks WHERE groupID='%d';", 
			(int) $_GET['id']);
		$db->query($qry);
		
		$qry = "INSERT INTO sysUGPLinks (privID, groupID) VALUES ";
		foreach ($_POST['privs_list'] as $privID) { 
			$qry .= sprintf("('%d', '%d'),", 
				(int) $privID,
				(int) $_GET['id']);
		}
		$qry = substr($qry, 0, -1);
		$db->query($qry);
	}
	
	if (isset($_POST['my_fields_list']) && is_array($_POST['my_fields_list'])) { 
		
		$qry = sprintf("DELETE FROM sysUGFLinks WHERE groupID='%d';", 
			(int) $_GET['id']);
		$db->query($qry);
		
		$qry = "INSERT INTO sysUGFLinks (groupID, fieldID) VALUES ";
		foreach ($_POST['my_fields_list'] as $fieldID) { 
			$qry .= sprintf("('%d', '%d'),", 
				(int) $_GET['id'],
				(int) $fieldID);
		}
		$qry = substr($qry, 0, -1);
		$db->query($qry);
	}
	
	
	$db->query('OPTIMIZE TABLE sysUGFLinks');
	$db->query('OPTIMIZE TABLE sysUGPLinks');
	
	

}

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


	} else {
		yell($_GET);
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
		$formBuffer .= "<td valign=\"top\"><label for=\"".$newFieldID."\">" . $field['label'] . "</label></td><td>" . $field['widgetHTML'] . " <p class=\"tooltip\">" . $field['tooltip'] . "</p></td>";
		$formBuffer .= "</tr>";
	}

	//temp
	$id = null;
	$formAction = null;
	//end temp

	$formBuffer .= "<tr><td>
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

	//list table query:
	$listqry = "SELECT itemID, nameFull, nameSystem, sysGroup FROM $primaryTableName WHERE sysOpen = '1'";
	//list table field titles
	$titles[0] = "Group Title";
	$titles[1] = "System Name";


	//print an editor with basic controls
	print $te->package_editor_list_data($listqry, $titles);
	//to pass more advanced controls, you'll need to create your own $fields array and pass it directly to $te->display_editor_list($fields);
	break;
}






//end of display logic
?>
</div>

		<div class="clearfix">&nbsp;</div>

	</div>

</div>
<?php
require 'templates/footer.php';
?>
