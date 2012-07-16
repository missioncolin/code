<?php

include '../../../includes/init.php';
require '../../classes/Editor.php';

$meta['title'] = 'News Manager';
$meta['title_append'] = ' &bull; Quipp CMS';

$has_permission = $auth->has_permission("modifyNews");
if ($has_permission) {
    if (!isset($_GET['id'])) { $_GET['id'] = null; }
    $te = new Editor();


    //set the primary table name
    $primaryTableName = "tblNews";

    //editable fields
    $fields[] = array(
        'label'   => "Article Title",
        'dbColName'  => "title",
        'tooltip'   => "A title for this article",
        'writeOnce'  => false,
        'widgetHTML' => "<input style=\"width:300px;\" type=\"text\" class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
        'valCode'   => "RQvalALPH",
        'dbValue'   => false,
        'stripTags'  => true
    );
    
    $fields[] = array(
        'label'   => "Author",
        'dbColName'  => "author",
        'tooltip'   => false,
        'writeOnce'  => false,
        'widgetHTML' => "<input style=\"width:300px;\" type=\"text\" class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
        'valCode'   => "RQvalALPH",
        'dbValue'   => false,
        'stripTags'  => true
    );
    
    $fields[] = array(
        'label'   => "Category",
        'dbColName'  => "category",
        'tooltip'   => false,
        'writeOnce'  => false,
        'widgetHTML' => "<input style=\"width:300px;\" type=\"text\" class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
        'valCode'   => "OPvalALPH",
        'dbValue'   => false,
        'stripTags'  => true
    );
    
    $fields[] = array(
        'label'   => "Lead-in",
        'dbColName'  => "lead_in",
        'tooltip'   => "A short introduction to the article",
        'writeOnce'  => false,
        'widgetHTML' => "<textarea class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" rows=\"5\" cols=\"75\">FIELD_VALUE</textarea>",
        'valCode'   => "RQvalALPH",
        'dbValue'   => false,
        'stripTags'  => false,
        'isWYSIWYG' => true
    );
    $fields[] = array(
        'label'   => "Article Body",
        'dbColName'  => "body",
        'tooltip'   => "The main content of the article",
        'writeOnce'  => false,
        'widgetHTML' => "<textarea class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" rows=\"25\" cols=\"100\">FIELD_VALUE</textarea>",
        'valCode'   => "RQvalALPH",
        'dbValue'   => false,
        'stripTags'  => false,
        'isWYSIWYG' => true
    );

    $fields[] = array(
        'label'   => "Slug",
        'dbColName'  => "slug",
        'tooltip'   => "Enter a unique name for this news article with no spaces. Example: article-title",
        'writeOnce'  => false,
        'widgetHTML' => "<input style=\"width:300px;\" type=\"text\" class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
        'valCode'   => "RQvalALPH",
        'dbValue'   => false,
        'stripTags'  => true
    );

    $fields[] = array(
        'label'   => "External Link",
        'dbColName'  => "externalLink",
        'tooltip'   => "http://example.com/",
        'writeOnce'  => false,
        'widgetHTML' => "<input style=\"width:300px;\" type=\"url\" class=\"uniform\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
        'valCode'   => "OPvalALPH",
        'dbValue'   => false,
        'stripTags'  => true
    );
    $fields[] = array(
        'label'   => "Display Date",
        'dbColName'  => "displayDate",
        'tooltip'   => "",
        'writeOnce'  => false,
        'widgetHTML' => "<input style=\"width:300px;\" type=\"date\" class=\"uniform datepicker\" id=\"FIELD_ID\" name=\"FIELD_ID\" value=\"FIELD_VALUE\" />",
        'valCode'   => "RQvalALPH",
        'dbValue'   => date('Y-m-d'),
        'stripTags'  => false
    );
    $fields[] = array(
        'label'   => "Active",
        'dbColName'  => "sysStatus",
        'tooltip'   => 'Choose to show this article on the public website',
        'writeOnce'  => false,
        'widgetHTML' => '<input type="checkbox" id="FIELD_ID" name="FIELD_ID" class="uniform" value="active" FIELD_VALUE />',
        'valCode'   => "",
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

        //yell($_POST);

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



            $qry = sprintf("INSERT INTO %s (%s, sysUserLastMod, sysDateLastMod, sysDateCreated, sysOpen) VALUES (%s, '%d', %s, %s, '1')",
                (string) $primaryTableName,
                (string) $fieldColNames,
                (string) $fieldColValues,
                $user->id,
                $db->now,
                $db->now
            );
            //yell($qry);
            print $te->commit_a_modify_action($qry, "Insert", true);
            break;


        case "update":


            //this default update query will work for most single table interactions, you may need to cusomize your own
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

                    $fieldColValue = "'" . $db->escape($_POST[$requestFieldID]) . "',";
                    $fieldColNames .= "" . $dbField['dbColName'] . " = " . $fieldColValue;
                }
            }

            //trim the extra comma off the end of the above var
            $fieldColNames = substr($fieldColNames, 0, strlen($fieldColNames) - 1);


            $qry = sprintf("UPDATE %s SET %s, sysUserLastMod='%d', sysDateLastMod=NOW() WHERE itemID = '%s'", (string) $primaryTableName, (string) $fieldColNames, $user->id, (int) $_POST['id']);

            print $te->commit_a_modify_action($qry, "Update", true);
            break;

        case "delete":

            //this delete query will work for most single table interactions, you may need to cusomize your own

            $qry = sprintf("UPDATE %s SET sysOpen = '0' WHERE itemID = '%d'",
                (string) $primaryTableName,
                (int) intval($_GET['id'], 10));

            print $te->commit_a_modify_action($qry, "Delete");
            header('Location:' . $_SERVER['PHP_SELF'] . '?delete=true');
            break;
        }
    } else {
        $_GET['view'] = 'edit';
    }

    if (isset($_GET['view']) && $_GET['view'] == 'edit') {
        array_push($quipp->js['footer'], '/js/tinymce/jscripts/tiny_mce/jquery.tinymce.js', '/js/tinymce/jscripts/tiny_mce/plugins/tinybrowser/tb_tinymce.js.php');

    }
}
include "../../templates/header.php";

?>
<h1>News Manager</h1>
<p>This allows the ability to publish news articles that are viewable on the news page and news widget on the public front end.</p>
<?php

if (!$has_permission) {
    echo "<p>Sorry, you do not have permission to access the news manager.</p>";
}
else {
?>


<div class="boxStyle">
	<div class="boxStyleContent">
		<div class="boxStyleHeading">
			<h2>Edit</h2>
			<div class="boxStyleHeadingRight">
				<?php print "<input class='btnStyle blue' type=\"button\" name=\"newItem\" id=\"newItem\" onclick=\"javascript:window.location.href='" . $_SERVER['PHP_SELF'] . "?view=edit';\" value=\"New\" />"; ?>
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

        if (is_numeric($_GET['id'])) { //if an ID is provided, we assume this is an edit and try to fetch that row from the single table


            $qry = sprintf("SELECT * FROM $primaryTableName WHERE itemID = '%d' AND sysOpen = '1';",
                (int) $_GET['id']);

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


        } else {
            //yell($_GET);
        }


        if ($message != '') {
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
                    if (isset($_POST[$newFieldID]) && $message != '') {
                        $field['dbValue'] = $_POST[$newFieldID];
                    }
                    $field['widgetHTML'] = str_replace("FIELD_VALUE", $field['dbValue'], $field['widgetHTML']);

                    if (isset($field['isWYSIWYG']) && $field['isWYSIWYG'] == true) {
                        $quipp->js['onload'] .= tinyMCE($newFieldID, 'standard');
                    }
                }

            }
            //write in the html
            $formBuffer .= "<td valign=\"top\"><label for=\"".$newFieldID."\">" . $field['label'] . "</label></td><td>" . $field['widgetHTML'] . " <p>" . $field['tooltip'] . "</p></td>";
            $formBuffer .= "</tr>";
        }

        //temp
        $id = null;
        $formAction = null;
        //end temp

        $formBuffer .= "<tr><td>
					<fieldset>
					<input type=\"hidden\" name=\"dbaction\" id=\"dbaction\" value=\"$dbaction\" />";

        if ($dbaction == "update") { //add in the id to pass back for queries if this is an edit/update form
            $formBuffer .= "<input type=\"hidden\" name=\"id\" id=\"id\" value=\"".$_GET['id']."\" />";
        }

        $formBuffer .= "

					</fieldset>
					</td></tr>";
        $formBuffer .= "</table>";
        $formBuffer .= "<div class=\"clearfix\" style=\"margin-top: 10px; height:10px; border-top: 1px dotted #B1B1B1;\">&nbsp;</div>";
        $formBuffer .= "<input class='btnStyle grey' type=\"button\" name=\"cancelUserForm\" id=\"cancelUserForm\" onclick=\"javascript:window.location.href='" . $_SERVER['PHP_SELF'] . "';\" value=\"Cancel\" />
					<input class='btnStyle green' type=\"submit\" name=\"submitUserForm\" id=\"submitUserForm\" value=\"Save Changes\" />";
        $formBuffer .= "</form>";
        //$jsFooter .= "CKEDITOR.replace( 'RQvalALPHTestimonial', {toolbar : 'Basic',uiColor : '#ddd',  width : '600', height : '200', filebrowserUploadUrl : '/js/ckeditor/upload.php'});";
        //print the form
        print $formBuffer;
        break;
    default: //(list)

        //list table query:
        $listqry = "SELECT n.itemID, n.title, n.sysStatus FROM $primaryTableName AS n WHERE n.sysOpen = '1'";
        //list table field titles
        $titles[0] = "Title";
        $titles[2] = "Active";

        //print an editor with basic controls
        print $te->package_editor_list_data($listqry, $titles);
        //to pass more advanced controls, you'll need to create your own $fields array and pass it directly to $te->display_editor_list($fields);
        break;
    }


?>
</div>

		<div class="clearfix">&nbsp;</div>

	</div>

</div>

<?php
}

//end of display logic


include "../../templates/footer.php";




?>
