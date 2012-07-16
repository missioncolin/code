<?php

require '../../includes/init.php';

//brendan@resolutionim.com (Feb 2011)


if (!isset($pageUtility)) {
	require_once "../classes/PageUtility.php";
	$pageUtility = new PageUtility($db);
}

if (!isset($approvalUtility)) {
	require_once "../classes/ApprovalUtility.php";
	$approvalUtility = new ApprovalUtility();
}
if (!isset($box)) {
	require_once "../classes/Content.php";
	$box = new Content();
}

switch ($_REQUEST['operation']) {


	case "insert_app_widget":
		header("Content-Type: application/json; charset=utf-8");
	
		if ($box->insert_app_widget($_REQUEST['contentID'], $_REQUEST['pageID'])) {
			$r = array("status" => 1, "message" => "Application was successfully added.");
			print json_encode($r);
		} else {
			$r = array("status" => 0, "message" => "Application could not be added.");
			print json_encode($r);
		}
		break;
		
			
	case "update_page_property":
		header("Content-Type: application/json; charset=utf-8");
	
		if ($pageUtility->update_page_property($_REQUEST['pageID'], $_REQUEST['fieldName'], $_REQUEST['value'])) {
			$r = array("status" => 1, "message" => $_REQUEST['humanReadableName'] . " was successfully updated.");
			print json_encode($r);
		} else {
			$r = array("status" => 0, "message" => $_REQUEST['humanReadableName'] . " Could Not Be updated.");
			print json_encode($r);
		}
		break;
		
	case "adjust_password_protect":
		header("Content-Type: application/json; charset=utf-8");
		
		//break apart the serial
		$permissionGroups = false;
		if(strstr($_REQUEST['serial'], "=")) {
			$fields = explode("&",$_REQUEST['serial']);
			
			foreach($fields as $field){
				$field_key_value = explode("=",$field);
				$permissionGroups[] = urldecode($field_key_value[1]);;
			}
		} 
	
		if ($pageUtility->adjust_password_protect($_REQUEST['pageID'], $permissionGroups)) {
			$r = array("status" => 1, "message" => " Permissions have been adjusted on this page.");
			print json_encode($r);
		} else {
			$r = array("status" => 0, "message" => " Permissions could not be adjusted on this page.");
			print json_encode($r);
		}
		break;
	
	
	
		
	case "approve_draft_and_make_live":
		header("Content-Type: application/json; charset=utf-8");
	
		if ($newDraftPageID = $pageUtility->approve_draft_and_make_live($_REQUEST['pageID'])) {
			$r = array("status" => 1, "message" => " This page is now live, <strong>you are now working on a new draft version.</strong>", "draftPageID" => $newDraftPageID);
			print json_encode($r);
		} else {
			$r = array("status" => 0, "message" => " Page could not be made live.");
			print json_encode($r);
		}
		break;
	
	case "start_over_from_live":
		header("Content-Type: application/json; charset=utf-8");
	
		if ($newDraftPageID = $pageUtility->start_over_from_live($_REQUEST['pageID'])) {
			$r = array("status" => 1, "message" => " This page is now live, <strong>you are now working on a new draft version.</strong>", "draftPageID" => $newDraftPageID);
			print json_encode($r);
		} else {
			$r = array("status" => 0, "message" => " Page could not be made live.");
			print json_encode($r);
		}
		break;
	
	case "submit_for_review":
		header("Content-Type: application/json; charset=utf-8");
	
		//get the properties for this page
		$pageProperties = $pageUtility->get_page_properties($_POST['pageID']);
		
		yell($pageProperties);
		
		$viewLink = "location.href = '/?p=" . $pageProperties['systemName'] . "&draft=preview';";
		$editLink = "location.href = '/admin/content.php?navID=" . $_POST['navID'] . "';";
		$denyLink = "denyApprovalTicket(this);"; //this is the default
		$approveLink = "approvePageVersion(" . $_POST['pageID'].", this);"; //this is custom for pages as special actions are needed
	
	
// @cBtmp
		if ($approvalUtility->new_ticket($pageProperties['instanceID'], "page", $_POST['pageID'], $_SESSION['userID'], $pageProperties['label'], $viewLink, $editLink, $denyLink, $approveLink)) {
			$r = array("status" => 1, "message" => " A review request has been sent. <strong>A content reviewer will make the content live if it is approved.</strong>");
			print json_encode($r);
		} else {
			$r = array("status" => 0, "message" => " Either there was a problem finding a reviewer to audit content <strong>or this content has already had a review requested</strong> that has not yet been seen. Ensure someone is set as a reviewer.");
			print json_encode($r);
		}
		break;
	
	case "deny_approval_request":
		header("Content-Type: application/json; charset=utf-8");
	
		
		if ($approvalUtility->deny_ticket($_POST['ticketID'])) {
			$r = array("status" => 1, "message" => " Review result has been registered as 'denied'.");
			print json_encode($r);
		} else {
			$r = array("status" => 0, "message" => " There was a problem marking the item as denied. It may have expired.");
			print json_encode($r);
		}
		break;
	
		
	case "reload_template":
		$box->build_template($_POST['pageID'], $_POST['templateID']);
		break;
		
		
	case "delete_content":
		header("Content-Type: application/json; charset=utf-8");
		
		if ($pageUtility->delete_content($_REQUEST['contentID'], $_REQUEST['regionID'], $_REQUEST['pageID'])) {
			$r = array("status" => 1, "message" => "Content box was successfully removed.");
			print json_encode($r);
		} else {
			$r = array("status" => 0, "message" => "Content box could not be removed.");
			print json_encode($r);
		}
		break;
		
		
}