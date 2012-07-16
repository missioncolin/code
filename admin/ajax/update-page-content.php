<?php

require '../../includes/init.php';
require '../classes/Content.php';

$box = new Content();

yell($box, $_POST);

/*$setRegionLink = 
		"INSERT INTO sysTBLPageTemplateRegionContent (contentID, pageID, regionID, myOrder, sysOpen) " .
		"VALUES ('" . $valueIn . "', '$_REQUEST[PageID]', '$_REQUEST[regionID]', '" . $keyIn . "', '1')";
		draggin_query($setRegionLink);
*/
?>