<?php

require '../../includes/init.php';
require '../classes/Content.php';

$box = new Content();

if (!isset($_POST['list'])) { 
	$_POST['list'] = false;
}
$box->reorder_boxes($_POST['pageID'], $_POST['regionID'], $_POST['list']);
$box->get_boxes($_POST['pageID'], $_POST['regionID']);

print '<script>$("#template .regionbox").sortable({connectWith: ".regionbox", update: function(event, ui) { update_order(this); }}).disableSelection();</script>';

/*$setRegionLink = 
		"INSERT INTO sysTBLPageTemplateRegionContent (contentID, pageID, regionID, myOrder, sysOpen) " .
		"VALUES ('" . $valueIn . "', '$_REQUEST[PageID]', '$_REQUEST[regionID]', '" . $keyIn . "', '1')";
		draggin_query($setRegionLink);
*/
?>