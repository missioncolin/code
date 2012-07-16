<?php

class ApprovalUtility
{
	
	//this utility is purely a way for a content administrator to track tickets, NO LOGIC TO ACTUALLY CHANGE CONTENT STATUS SHOULD APPEAR HERE
	//all content that has a versioning system must maintain it's own system for versioning content and simply track approval request tickets here
	//The logic for approving pages for example is in /admin/classes/PageUtility.php which will create tickets using this class but maintain
	//it's own systems for flipping content live/draft/archive.

	function __construct()
	{
	

	}

	function new_ticket($instanceID = 1, $appName, $appItemID, $userID, $description, $viewLink, $editLink, $denyLink, $approveLink) {
		global $quipp, $db, $notify, $auth;
		
		
		//there is a possiblity that the review this ticket requires might already be in queue (multiple requests for the same content), skip this insert if that's the case
		if(is_numeric($db->return_specific_item(false, "sysApprovalTickets", "itemID", "--", " appName = '" . $appName . "' AND appItemID = '".$appItemID."' AND sysStatus = 'active' AND sysOpen = '1'"))) {
			return false;
		}
		//
		
		error_log("Calling new_ticket($instanceID, $appName, $appItemID, $userID, $description, $viewLink, $editLink, $denyLink, $approveLink)   \n", 3, Quipp()->config('yell_log'));
		
		$qry = sprintf("INSERT INTO sysApprovalTickets (instanceID, appName, appItemID, userID, description, viewLink, editLink, denyLink, approveLink, sysDateCreated, sysStatus, sysOpen) VALUES ('%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', NOW(), 'active', '1');",
			$db->escape($instanceID),
			$db->escape($appName),
			$db->escape($appItemID),
			$db->escape($userID),
			$db->escape($description),
			$db->escape($viewLink),
			$db->escape($editLink),
			$db->escape($denyLink),
			$db->escape($approveLink)
			
			
			);
			
			
		yell($qry);
		if($db->query($qry)) {
			
			if(!isset($notify)) {
				require_once $_SERVER['DOCUMENT_ROOT'] . "/inc/quipp/Notify.php";
				$notify = new Notify();
			}
			
			$quipp->system_log("Activity: [" . $description . "] has been submitted for content approval review.");
			
			$whoCanApprove = $auth->get_users_with_this_permission("approvepage");
			$notify->send_email("Review Request [\"" . $description . "\"] - For details, view the <strong><a href=\"http://".$_SERVER['HTTP_HOST']."/admin/stream.php\">content stream</a></strong>\n\n", $whoCanApprove);
			
			return true;
		} else {
		
			return false;
		}
		
		
		
	
	}
	
	function get_list_of_content_approvers() {
		global $quipp, $db, $notify;
		
	
	}
	
	function approve_ticket($ticketID) {
		global $quipp, $db, $notify;
		
		if($ticketID) {		
			
			$qry = sprintf("UPDATE sysApprovalTickets SET sysStatus = 'approved' WHERE itemID = '%d';",
				$db->escape($ticketID)
			);
	
			if($db->query($qry)) {
				
				if(!isset($notify)) {
					require_once $_SERVER['DOCUMENT_ROOT'] . "/inc/quipp/Notify.php";
					$notify = new Notify();
				}
				
				$ticket = $this->get_ticket($ticketID);
				
				$notify->send_email("Content Approved [\"" . $ticket['description'] . "\"] - The content you have submitted for review has been approved. \n\n", $ticket['userID']);
				//$this->delete_ticket($ticketID);
				
				$quipp->system_log("Activity: Content review complete. [" . $ticket['description']  . "] has been approved.");
				
				return true;
			} else {
				return false;
			}
			
		} else {
			return false;
		}		
	
	}
	
	function deny_ticket($ticketID = false) {
		global $quipp, $db, $notify;
		
		if($ticketID) {		
			$qry = sprintf("UPDATE sysApprovalTickets SET sysStatus = 'denied' WHERE itemID = '%d';",
				$db->escape($ticketID)
			);
	
	
			if($db->query($qry)) {
				
				if(!isset($notify)) {
					require_once $_SERVER['DOCUMENT_ROOT'] . "/inc/quipp/Notify.php";
					$notify = new Notify();
				}
				
				$ticket = $this->get_ticket($ticketID);
				
				$notify->send_email("Content Denied [\"" . $ticket['description'] . "\"] - Unfortunately the content you have submitted for review has been denied. \n\n", $ticket['userID']);
				$quipp->system_log("Activity: Content review complete. [" . $ticket['description']  . "] has been denied.");
				
				return true;
			} else {
				return false;
			}
			
		} else {
			return false;
		}

	}
	
	function delete_ticket($ticketID = false) {
		global $quipp, $db, $notify;
		
		if($ticketID) {
			$qry = sprintf("UPDATE sysApprovalTickets SET sysOpen = '0' WHERE itemID = '%d';",
					$db->escape($ticketID)
				);
	
	
				if($db->query($qry)) {
					return true;
				} else {
					return false;
				}
		} else {
			return false;
		}
	
	}
	
	function get_ticket($ticketID = false) {
		
		global $quipp, $db, $notify;
		
		if($ticketID) {
			$res = $db->query($qs = "SELECT * FROM sysApprovalTickets WHERE itemID = '$ticketID'");
		} 

		if ($db->valid($res)) {

			$rs = $db->fetch_assoc($res);
			return $rs;

		}
	
	}
	
	function get_tickets() {
		
		global $quipp, $db, $notify;
		
		
			$res = $db->query($qs = "SELECT * FROM sysApprovalTickets WHERE sysStatus = 'active'");
		

		if ($db->valid($res)) {

			while($rs = $db->fetch_assoc($res)) {
				print "<div id=\"apTicket_".$rs['itemID']."\" class=\"approvalTicket\" style=\"display:block;\">
				<span class=\"apDescrip\">" . $rs['description'] . "</span>
				<span class=\"apType\"><strong>Type:</strong> " . $rs['appName'] . " &nbsp; Posted: " . $rs['sysDateCreated'] . "</span><button id=\"apTicket_View_".$rs['itemID']."\" class=\"btnStyle blue\" onclick=\"" . $rs['viewLink'] . "\">View</button>  <button id=\"apTicket_Edit_".$rs['itemID']."\" class=\"btnStyle blue\" onclick=\"" . $rs['editLink'] . "\">Edit</button>  <button id=\"apTicket_Approve_".$rs['itemID']."\" class=\"btnStyle green\" onclick=\"" . $rs['approveLink'] . "\">Approve</button> <button id=\"apTicket_Deny_".$rs['itemID']."\" class=\"btnStyle red\" onclick=\"" . $rs['denyLink'] . "\">Deny</button> </div>";
			}

		}
	
	}
	
}
?>