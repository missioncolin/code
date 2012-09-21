<?php
 
$root = dirname(dirname(dirname(__DIR__)));
require $root . '/includes/init.php';
require $root . '/admin/classes/Editor.php';

$meta['title'] = 'Job Credits';
$meta['title_append'] = ' &bull; Quipp CMS';

$hasPermission = false;
if ($auth->has_permission("canEditNotify")){
    $hasPermission = true;
}

if ($hasPermission) {
    
    $canApprove = $auth->has_permission('approvepage');
    
    if (!isset($_GET['id'])) { $_GET['id'] = null; }
    $te = new Editor();

    
    //set the primary table name
    $primaryTableName = "tblJobCreditsPricing";

    //dbaction = database interactivity, these standard queries will do for most single table interactions, you may need to replace with your own
    if (!isset($_POST['dbaction'])) {
        $_POST['dbaction'] = null;

        if (isset($_GET['action'])) {
            $_POST['dbaction'] = $_GET['action'];
        }
    }
    if (isset($_POST["RQvalNUMBprice"]) && is_array($_POST["RQvalNUMBprice"])){

        foreach($_POST["RQvalNUMBprice"] as $itemID => $price){
            if (is_numeric($itemID) && (int)$itemID > 0 && preg_match("%^\d+$%",$price,$matches)){
                $updates[] = array(
                    "itemID" => $itemID,
                    "price" => $price
                );
            }
        }
        
        switch ($_POST['dbaction']) {

        case "update":
            
            if (isset($updates)){
                $affectedRows = 0;
                foreach ($updates as $update){
                    $qry = sprintf("UPDATE %s SET `price` = %d WHERE itemID = '%d'", 
                    (string) $primaryTableName, 
                    (int)$update["price"], 
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
                echo "Update did not work. Please provide valid price (eg. 400)";
            }
            break;

        }
    } 
include $root. "/admin/templates/header.php";

?>
<h1>Job Credits Pricing</h1>
<p>This allows the ability to update cost of job credit packages.</p>

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
        
    $listqry = "SELECT `itemID`, `packageName`, `price`, `credits` FROM $primaryTableName";
    $resQry = $db->query($listqry);
    
    if ($db->valid($resQry) !== false){
    //list table field titles
    $titles[0] = "Package";
    $titles[1] = "Price (CDN)";
    $titles[2] = "Savings";

    $basePrice = 0;
    //print an editor with basic controls
    echo '<form name="frmUpdateNotify" method = "post" action='.$_SERVER['REQUEST_URI'].'>';
    echo '<table id="adminTableList" class="adminTableList tablesorter" width="100%" cellpadding="5" cellspacing="0" border="1">';
    echo '<thead><tr><th>'.$titles[0].'</th><th>'.$titles[1].'</th><th>'.$titles[2].'</th></tr></thead><tbody>';
    while ($row = $db->fetch_assoc($resQry)){
    
        if ($basePrice == 0 && trim($row["credits"]) == '1'){
            $basePrice = trim($row["price"]);
        }
        echo '<tr><td>'.trim($row["packageName"]) .'</td>
        <td><input type="text" name="RQvalNUMBprice['.trim($row["itemID"]).']" value="'.trim($row["price"]).'" id="RQvalNUMBprice'.trim($row["itemID"]).'" class="uniform" style="width:250px"/></td>
        <td>'.($basePrice > 0 ? (100 -floor(((int)$row["price"]/((int)$row["credits"]*$basePrice) * 100))) : 0).'% Savings</td></tr>';
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