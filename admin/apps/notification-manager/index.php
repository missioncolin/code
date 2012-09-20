<?php
 
$root = dirname(dirname(dirname(__DIR__)));
require $root . '/includes/init.php';
require $root . '/admin/classes/Editor.php';

$meta['title'] = 'Notification Manager';
$meta['title_append'] = ' &bull; Quipp CMS';

$hasPermission = false;
if ($auth->has_permission("canEditNotify")){
    $hasPermission = true;
}

if ($hasPermission) {
    
    $canApprove = $auth->has_permission('approvepage');
    
    if (!isset($_GET['id'])) { $_GET['id'] = null; }
    $te = new Editor();

    $domains = false;
    $domQry = sprintf("SELECT s.`itemID`, s.`title` FROM `sysSites` AS s INNER JOIN `sysUSites` AS us ON s.`itemID` = us.`siteID` WHERE us.`userID` = %d",
        (int)$user->id
    );
    $domRes = $db->query($domQry);
    if ($db->valid($domRes)){
        $domains = array();
        while ($row = $db->fetch_assoc($domRes)){
            $domains[trim($row["itemID"])] = trim($row["title"]);
        }
    }
    $sites = (is_array($domains))?array_keys($domains):false;
    
    //set the primary table name
    $primaryTableName = "sysStorageTable";

    //dbaction = database interactivity, these standard queries will do for most single table interactions, you may need to replace with your own
    if (!isset($_POST['dbaction'])) {
        $_POST['dbaction'] = null;

        if (isset($_GET['action'])) {
            $_POST['dbaction'] = $_GET['action'];
        }
    }
    if (isset($_POST["RQvalMAILnotify"]) && is_array($_POST["RQvalMAILnotify"])){

        foreach($_POST["RQvalMAILnotify"] as $itemID => $email){
            if (is_numeric($itemID) && (int)$itemID > 0 && preg_match("%^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$%",$email,$matches)){
                $updates[] = array(
                    "itemID" => $itemID,
                    "email" => $email
                );
            }
        }
        
        switch ($_POST['dbaction']) {

        case "update":
            
            if (isset($updates)){
                $affectedRows = 0;
                foreach ($updates as $update){
                    $qry = sprintf("UPDATE %s SET `value` = '%s', sysUserLastMod = '%d', sysDateLastMod = NOW() WHERE itemID = '%d'", 
                    (string) $primaryTableName, 
                    (string) $db->escape($update["email"],true), 
                    $user->id, 
                    (int)$update["itemID"]);
                    
                    $res = $db->query($qry);
                    if ($db->error() === false){
                        $affectedRows++;
                    }
                }
                if ($affectedRows == count($updates)){
                    header('Location:' . $_SERVER['PHP_SELF']);
                }
                else{
                    echo 'Update did not work';
                }
            }
            else{
                echo "Update did not work. Please provide valid email addresses";
            }
            break;

        }
    } 
include $root. "/admin/templates/header.php";

?>
<h1>Notification Manager</h1>
<p>This allows the ability to update the email address that requests made from the website will go to.</p>

<div class="boxStyle">
	<div class="boxStyleContent">
		<div class="boxStyleHeading">
			<h2>Edit</h2>
			<div class="boxStyleHeadingRight"></div>
		</div>
		<div class="clearfix">&nbsp;</div>
		<div id="template">

	<?php
    //display logic

    //view = view state, these standard views will do for most single table interactions, you may need to replace with your own
        
    $listqry = sprintf("SELECT `itemID`, `application`, `value`, `sysStatus`, (SELECT `domain` FROM `sysSitesDomains` AS sd WHERE sd.`siteID` = $primaryTableName.`siteID` ORDER BY myOrder LIMIT 1) AS domain FROM $primaryTableName WHERE cast(sysOpen as UNSIGNED) > 0 AND `siteID` IN (%s)",
    	   implode(",",$sites)
    );
    $resQry = $db->query($listqry);
    
    if ($db->valid($resQry) !== false){
    //list table field titles
    $titles[0] = "Application";
    $titles[1] = "Email Address";
    $titles[2] = "Site";
    $titles[3] = "Status";

    //print an editor with basic controls
    echo '<form name="frmUpdateNotify" method = "post" action='.$_SERVER['REQUEST_URI'].'>';
    echo '<table id="adminTableList" class="adminTableList tablesorter" width="100%" cellpadding="5" cellspacing="0" border="1">';
    echo '<thead><tr><th>'.$titles[0].'</th><th>'.$titles[1].'</th><th>'.$titles[2].'</th><th>'.$titles[3].'</th></tr></thead><tbody>';
    while ($row = $db->fetch_assoc($resQry)){
        echo '<tr><td>'.trim($row["application"]) .'</td>
        <td><input type="text" name="RQvalMAILnotify['.trim($row["itemID"]).']" value="'.trim($row["value"]).'" id="RQvalMAILnotify_'.trim($row["itemID"]).'" class="uniform" style="width:250px"/></td>
        <td>'.trim($row["domain"]) .'</td><td>'.trim($row["sysStatus"]) . '</td></tr>';
    }    
    echo '</tbody></table>';
    //to pass more advanced controls, you'll need to create your own $fields array and pass it directly to $te->display_editor_list($fields);
    echo '<p><br /><input class="btnStyle green" type="submit" name="submitUserForm" id="submitUserForm" value="Save Changes" /><input type="hidden" name="dbaction" value="update" /></p></form>';
    }
    else{
        echo 'no data present';
    }

?>
    </div><!-- end template -->
    <div class="clearfix">&nbsp;</div>

</div><!-- boxStyleContent -->
</div><!-- boxStyle -->
<?php

//end of display logic


include $root. "/admin/templates/footer.php";

}
else{
    echo 'no permission';

}
?>