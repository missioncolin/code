<?php

include '../../../includes/init.php';
require '../../classes/Editor.php';
require '../auth/kinderSmiles.auth.php';

$meta['title'] = 'Testimonials Manager';
$meta['title_append'] = ' &bull; Quipp CMS';

$hasPermission = false;
if ($auth->has_permission("modifyTestimonials")){
    $hasPermission = true;
}
if ($hasPermission){
if (!isset($_GET['id'])) { $_GET['id'] = null; }

$te = new Editor();
$sa = new KinderSmilesAuth($auth, $db);

//set the primary table name
$primaryTableName = "tblTestimonials";

$domains = $sa->getSitesAllowed($user->id);
$sites = (is_array($domains))?array_keys($domains):false;

//editable fields
$fields[] = array(
	'label'   => "Submitted By",
	'dbColName'  => "name",
	'tooltip'   => "The full or abbreviated name of the person who submitted the testimonial",
	'writeOnce'  => false,
	'widgetHTML' => "<input style=\"width:300px;\" type=\"text\" class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
	'valCode'   => "RQvalALPH",
	'dbValue'   => false,
	'stripTags'  => true
);

$fields[] = array(
	'label'   => "Email Address",
	'dbColName'  => "emailAddress",
	'tooltip'   => "The email address of the person who submitted the testimonial",
	'writeOnce'  => false,
	'widgetHTML' => "<input style=\"width:300px;\" type=\"text\" class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\"  />",
	'valCode'   => "RQvalMAIL",
	'dbValue'   => false,
	'stripTags'  => true
);
$fields[] = array(
	'label'   => "Comment",
	'dbColName'  => "comment",
	'tooltip'   => "The comment/testimonial submitted",
	'writeOnce'  => false,
	'widgetHTML' => "<textarea id=\"FIELD_ID\" name=\"FIELD_ID\" cols=\"80\" rows=\"15\">FIELD_VALUE</textarea>",
	'valCode'   => "RQvalALPH",
	'dbValue'   => false,
	'stripTags'  => true
);

$sitesWidget = "";
if (isset($domains)){
    $sitesWidget = "<select class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\">";
    foreach ($domains as $id => $domainName){
        $sitesWidget .= "<option value=\"".$id."\">".$domainName."</option>";
    }
    $sitesWidget .= "</select>";
}
$fields[] = array(
	'label'   => "Submitted To",
	'dbColName'  => "siteID",
	'tooltip'   => "The practice the testimonial was submitted to",
	'writeOnce'  => false,
	'widgetHTML' => $sitesWidget,
	'valCode'   => "RQvalALPH",
	'dbValue'   => false,
	'stripTags'  => true
);
$fields[] = array(
	'label'   => "Active",
	'dbColName'  => "sysStatus",
	'tooltip'   => "Active testimonials will be displayed on the website",
	'writeOnce'  => false,
	'widgetHTML' => "<input type=\"checkbox\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"active\" FIELD_VALUE />",
	'valCode'   => "OPvalALPH",
	'dbValue'   => false,
	'stripTags'  => true
);

//dbaction = database interactivity, these standard queries will do for most single table interactions, you may need to replace with your own
if (!isset($_POST['dbaction'])) {
	$_POST['dbaction'] = null;

	if (isset($_GET['action'])) {
		$_POST['dbaction'] = $_GET['action'];
	}
}

if (!empty($_POST) && validate_form($_POST)) {
	switch ($_POST['dbaction']) {
	case "insert":

		//this insert query will work for most single table interactions, you may need to cusomize your own

		//the following loop populates 2 strings with name value pairs
		//eg.  $fieldColNames = 'articleTitle','contentBody',
		//eg.  $fieldColValues = 'Test Article Title', 'This is my test article body copy',
		//yell($_GET);
		//yell($fields);
		$fieldColNames  = '';
		$fieldColValues = '';
		foreach ($fields as $dbField) {
			if ($dbField['dbColName'] != false) {
				$requestFieldID = $dbField['valCode'] . str_replace(" ", "_", $dbField['label']);
				if ($dbField['dbColName'] == 'sysStatus') {
					if (isset($_POST[$requestFieldID])) {
						$_POST[$requestFieldID] = 'active';
					} else {
						$_POST[$requestFieldID] = 'inactive';
					}
				}

				$fieldColNames .= "`" . $dbField['dbColName'] . "`,";
				$fieldColValues .= "'" . $db->escape($_POST[$requestFieldID], $dbField['stripTags']) . "',";
			}
		}

		//trim the extra comma off the end of both of the above vars
		$fieldColNames = substr($fieldColNames, 0, strlen($fieldColNames) - 1);
		$fieldColValues = substr($fieldColValues, 0, strlen($fieldColValues) - 1);



		$qry = sprintf("INSERT INTO %s (%s, sysUserLastMod, sysDateLastMod, sysDateCreated, sysOpen) VALUES (%s, '%d', %s, %s, 1)",
			(string) $primaryTableName,
			(string) $fieldColNames,
			(string) $fieldColValues,
			$user->id,
			$db->now,
			$db->now
		);

		print $te->commit_a_modify_action($qry, "Insert", true);
		break;


	case "update":
		
	
		//this default update query will work for most single table interactions, you may need to cusomize your own
		$fieldColNames  = '';
		$fieldColValues = '';
		foreach ($fields as $dbField) {
			$requestFieldID = $dbField['valCode'] . str_replace(" ", "_", $dbField['label']);

			if ($dbField['dbColName'] != false) {
				
				if ($dbField['dbColName'] == 'sysStatus') {
                    
					if (isset($_POST[$requestFieldID])) {
						$fieldColValue = "'active',";
					} else {
						$fieldColValue = "'inactive',";
					}
					
					$fieldColNames .= "" . $dbField['dbColName'] . " = " . $fieldColValue;
				}
                else if (isset($_POST[$requestFieldID])){
				    $fieldColValue = "'" . $db->escape($_POST[$requestFieldID]) . "',";
				    $fieldColNames .= "" . $dbField['dbColName'] . " = " . $fieldColValue;
				}
			}
		}

		//trim the extra comma off the end of the above var
		$fieldColNames = substr($fieldColNames, 0, strlen($fieldColNames) - 1);


		$qry = sprintf("UPDATE %s SET %s, sysUserLastMod='%d', sysDateLastMod=NOW() WHERE itemID = '%s'", (string) $primaryTableName, (string) $fieldColNames, $user->id, (int) $_POST['id']);
		//yell($qry);
		print $te->commit_a_modify_action($qry, "Update", true);
		break;

	case "delete":

		//this delete query will work for most single table interactions, you may need to cusomize your own

		$qry = sprintf("UPDATE %s SET sysOpen = '0' WHERE itemID = '%d'",
			(string) $primaryTableName,
			(int)intval($_GET['id'],10));

		print $te->commit_a_modify_action($qry, "Delete");
		header('Location:' . $_SERVER['PHP_SELF'] . '?delete=true');
		break;
	}
} else {
	$_GET['view'] = 'edit';
}

include "../../templates/header.php";
?>

<h1>Testimonials Manager</h1>
<p>This allows the ability to add and modify client testimonials.</p>

<div class="boxStyle">
	<div class="boxStyleContent">
		<div class="boxStyleHeading">
			<h2>Edit</h2>
			
            <div class="boxStyleHeadingRight">
				<?php print "<input type=\"button\" class=\"btnStyle blue\" name=\"newItem\" id=\"newItem\" onclick=\"javascript:window.location.href='" . $_SERVER['PHP_SELF'] . "?view=edit';\" value=\"New\" />"; ?>
			</div>
		</div>
		<div class="clearfix">&nbsp;</div>
		<div id="template">







	<?php
//display logic





//view = view state, these standard views will do for most single table interactions, you may need to replace with your own
if (!isset($_GET['view'])) { $_GET['view'] = null; }

switch ($_GET['view']) {
case "edit": //show an editor for a row (existing or new)

	//determine if we are editing an existing record, otherwise this will be a 'new'

	$dbaction = "insert";

	$_GET['id'] = intval($_GET['id'], 10);

	if (is_numeric($_GET['id']) && $_GET['id'] > 0 && is_array($sites)) { //if an ID is provided, we assume this is an edit and try to fetch that row from the single table


		$qry = sprintf("SELECT * FROM $primaryTableName WHERE itemID = '%d' AND sysOpen = '1' AND siteID IN (%s);",
			(int)$_GET['id'],
			implode(",",$sites)
			);

		$res = $db->query($qry);


		if ($db->valid($res)) {
			$fieldValue = $db->fetch_assoc($res);
			foreach ($fields as &$itemField) {
				//if (is_string($itemField['dbColName'])) {
					$itemField['dbValue'] = $fieldValue[$itemField['dbColName']];
				//}
			}

			$dbaction = "update";
		}


	} 

	
	if ($message != '') {
		print $message;
	}

	$formBuffer = "
					<form enctype=\"multipart/form-data\" name=\"tableEditorForm\" id=\"tableEditorForm\" method=\"post\" action=\"" . $_SERVER['REQUEST_URI'] .  "\">
					<table class=\"clearTable\">
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
			} 
			else if ($field['dbColName'] == "siteID"){
			     $field['widgetHTML'] = str_replace('value="'.$field['dbValue'].'"','value="'.$field['dbValue'].'" selected="selected"',$field['widgetHTML']);
			     if (isset($domains[$field['dbValue']])){
			         $field['widgetHTML'] = str_replace($domains[$field['dbValue']],$domains[$field['dbValue']]."*",$field['widgetHTML']);
			     }
			}
			else {
				if (isset($_POST[$newFieldID]) && $message != '') {
					$field['dbValue'] = $_POST[$newFieldID];
				}
				$field['widgetHTML'] = str_replace("FIELD_VALUE", $field['dbValue'], $field['widgetHTML']);
			}
			
			if ($field['writeOnce'] === true && ($field['dbValue'] != '' || $field['dbValue'] !== false)) {
				$field['widgetHTML'] = str_replace("DISABLED", 'disabled="disabled"', $field['widgetHTML']);
			} else {
				$field['widgetHTML'] = str_replace("DISABLED", '', $field['widgetHTML']);
			}

		}
		//write in the html
		$formBuffer .= "<td width=\"120px\" valign=\"top\"><label for=\"".$newFieldID."\"><strong>" . $field['label'] . "</strong></label></td><td>" . $field['widgetHTML'] . " <p class=\"formTip\">" . $field['tooltip'] . "</p></td>";
		$formBuffer .= "</tr>";
	}

	//temp
	$id = null;
	$formAction = null;
	//end temp

	$formBuffer .= "<tr><td colspan=\"2\">
					<fieldset>
					<input type=\"hidden\" name=\"dbaction\" id=\"dbaction\" value=\"$dbaction\" />";

	if ($dbaction == "update") { //add in the id to pass back for queries if this is an edit/update form
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

    if (is_array($sites)){
	//list table query:
	   $listqry = sprintf("SELECT itemID, name, emailAddress, (SELECT `domain` FROM `sysSitesDomains` AS sd WHERE sd.`siteID` = $primaryTableName.`siteID` ORDER BY myOrder LIMIT 1) AS domain, sysStatus FROM $primaryTableName WHERE sysOpen = '1' AND `siteID` IN (%s)",
	       implode(",",$sites)
	   );
	//list table field titles
	   $titles[0] = "Submitted By";
	   $titles[1] = "Email Address";
       $titles[2] = "Site";
       $titles[3] = "Status";
	//print an editor with basic controls
	   print $te->package_editor_list_data($listqry, $titles, false, false, true, true);
	//to pass more advanced controls, you'll need to create your own $fields array and pass it directly to $te->display_editor_list($fields);
	
	}
	break;
}


?>
</div>

		<div class="clearfix">&nbsp;</div>

	</div>

</div>

<?php


//end of display logic


include "../../templates/footer.php";

} //end $hasPermisssion
else{
    echo 'no permission';
}
?>
