<?php

require '../../includes/init.php';

//brendan@resolutionim.com (Feb 2011)

//this uses the global Nav class instead of a NavUtility since it has so few functions
if (!isset($nav)) {
	require_once "../../includes/quipp/Nav.php";
	$nav = new Nav();
}


switch ($_REQUEST['operation']) {


	case "update_outbound_link":
		header("Content-Type: application/json; charset=utf-8");
	
		 if($nav->update_nav_property($_REQUEST['navID'], "label", $_REQUEST['linkLabel']) && $nav->update_nav_property($_REQUEST['navID'], "url", $_REQUEST['linkURL']) && $nav->update_nav_property($_REQUEST['navID'], "target", $_REQUEST['linkBehaviour'])) {
		 	$r = array("status" => 1, "message" => "Link was successfully updated.");
			print json_encode($r);
		} else {
			$r = array("status" => 0, "message" => "Link could not be updated.");
			print json_encode($r);
		}
	break;
		
}
$db->close();
?>