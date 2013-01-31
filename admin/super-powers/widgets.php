<?php

require '../../includes/init.php';
require '../classes/Editor.php';
$meta['title'] = 'Site Widget Editor';
$meta['title_append'] = ' &bull; Quipp CMS';

require '../templates/header.php';

if (!$auth->has_permission("root")) {
    $quipp->system_log("User manager Has Been Blocked Because of Insufficient Privileges.");
    print alert_box("You do not have sufficient privileges to view the widget manager.  Would you like to <a href=\"/admin/\">Return to Management System Home</a>?", 2);
    require '../templates/footer.php';
    die();
}


$te = new Editor();

//set the primary table name
$primaryTableName = "sysPageContent";
if (isset($_GET['id'])) {
    $_GET['id'] = intval($_GET['id'], 10);
}
//editable fields

$fields[] = array(
    'label'   => "Widget Name",
    'dbColName'  => "adminTitle",
    'tooltip'   => "eg. News Full Story",
    'writeOnce'  => false,
    'widgetHTML' => "<input style=\"width:300px;\" type=\"text\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
    'valCode'   => "RQvalALPH",
    'dbValue'   => false,
    'stripTags' => true
);


$fields[] = array(
    'label'   => "Widget Path",
    'dbColName'  => 'includeOverride',
    'tooltip'   => "/includes/apps/appname/views/viewname.php",
    'writeOnce'  => false,
    'widgetHTML' => "<input style=\"width:450px;\" type=\"text\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
    'valCode'   => "RQvalALPH",
    'dbValue'   => false,
    'stripTags' => true
);

$fields[] = array(
    'label'   => "Widget Admin Link",
    'dbColName'  => 'appAdminLink',
    'tooltip'   => "/admin/apps/appname/index.php",
    'writeOnce'  => false,
    'widgetHTML' => "<input style=\"width:450px;\" type=\"text\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
    'valCode'   => "OPvalALPH",
    'dbValue'   => false,
    'stripTags' => true
);


$statuses = array(
    '0' => 'Deleted',
    '1' => 'Active'
);
$status = 'active';
if (isset($_GET['id'])) {
    $status = $db->return_specific_item($_GET['id'], 'sysPageContent', 'sysOpen');
}
$fields['sysStatus'] = array(
    'label'   => "Status",
    'dbColName'  => 'sysOpen',
    'tooltip'   => '',
    'writeOnce'  => false,
    'widgetHTML' => get_list('RQvalALPHStatus', $statuses, false, false, $status),
    'valCode'   => "RQvalALPH",
    'dbValue'   => false,
    'stripTags' => true
);


?>



	<h1>Site Widgets</h1>
	<p>This app displays all widgets that have been registered with quipp.</p>

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
            
            $fieldColValues .= "'" . $db->escape($_REQUEST[$requestFieldID], $dbField['stripTags']) . "',";
            
            $fieldColNames .= $dbField['dbColName'] . ",";

        }
    }
    $fieldColValues .= '\'1\',';
    $fieldColNames .= 'isAnApp,';

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


    $qry = sprintf("UPDATE %s SET sysOpen = '0' WHERE itemID = '%d'",
        (string) $primaryTableName,
        (int) $_GET['id']);
    print $te->commit_a_modify_action($qry, "Delete");

    break;
}




//view = view state, these standard views will do for most single table interactions, you may need to replace with your own
if (!isset($_REQUEST['view'])) { $_REQUEST['view'] = null; }

switch ($_REQUEST['view']) {
case "edit": //show an editor for a row (existing or new)

    //determine if we are editing an existing record, otherwise this will be a 'new'

    $action = "insert";

    //$_GET['id'] = base_convert($_GET['id'], 36, 10);

    if (is_numeric($_GET['id'])) { //if an ID is provided, we assume this is an edit and try to fetch that row from the single table


        $qry = sprintf("SELECT * FROM $primaryTableName WHERE itemID = '%d';",
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
    $formBuffer .= "</table><p>&nbsp;</p><table>";






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

    //list table query:
    $listqry = "SELECT itemID, adminTitle, includeOverride, sysOpen FROM $primaryTableName WHERE isAnApp = '1' ORDER BY sysOpen DESC, adminTitle";
    //list table field titles
    $titles[0] = "Widget Name";
    $titles[1] = "Path";


    //print an editor with basic controls
    print $te->package_editor_list_data($listqry, $titles);
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
require '../templates/footer.php';
?>
