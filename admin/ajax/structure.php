<?php

require '../../includes/init.php';

//brendan@resolutionim.com (Oct 2010)
//this will print the entire tree for now, it shouldn't be, but if performance becomes an issue on very large sites this could possibly be optimized for specific parentID ajax calls from the tree. This approach is the best UI experience as we can expand the tree fully on initial load.


$nav = new Nav();
global $relType;
$relType = null;

header("Content-Type: application/json; charset=utf-8");
switch($_REQUEST['operation']) {
	case "move_node":
			if($nav->move_nav_item_recursive($_REQUEST['id'], $_REQUEST['ref'], $_REQUEST['position'], $_REQUEST['siblings'])) {
				$r = array("status" => 1, "id" => 1); 
				print json_encode($r);
			} else {
				print "error reported";
			}
	break;
	case "rename_node":
			if($nav->rename_nav_item($_REQUEST['id'], $_REQUEST['title'])) {
				$r = array("status" => 1, "id" => 1); 
				print json_encode($r);
			} else {
				print "error reported";
			}
	break;
	case "remove_node":
			if($nav->delete_nav_item_recursive($_REQUEST['id'])) {
				$r = array("status" => 1, "id" => 1); 
				print json_encode($r);
			} else {
				print "error reported";
			}
	break;
	case "deactivate_node":
			$relType = $_REQUEST['rel'];
			if($nav->deactivate_nav_item($_REQUEST['id'])) {
				
				$relType.= "inactive";
				
				$r = array("status" => 1, "reltype" => $relType, "id" => $_REQUEST['id']); 
				print json_encode($r);
			} else {
				print "error reported";
			}
	break;
	case "activate_node":
			$relType = $_REQUEST['rel'];
			if($nav->activate_nav_item($_REQUEST['id'])) {
				
				$relType = str_replace("inactive", "", $relType);
			
				$r = array("status" => 1, "reltype" => $relType, "id" => $_REQUEST['id']);
				print json_encode($r);
			} else {
				print "error reported";
			}
	break;
	
	default: print json_encode($nav->get_everything());
	break;

}
$db->close();
?>