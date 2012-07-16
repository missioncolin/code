<?php
require '../includes/init.php';
require_once 'classes/ApprovalUtility.php';

if(!$auth->has_permission("approvepages")) {
	$quipp->system_log("User manager Has Been Blocked Because of Insufficient Privileges.");
	print alert_box("You do not have sufficient privileges to view the user manager.  Would you like to <a href=\"/admin/\">Return to Management System Home</a>?", 2);
	die();
}

if (!isset($approvalUtility)){
    $approvalUtility = new ApprovalUtility();
}
$approvalUtility->get_tickets(); //will actually print tickets
?>
<style>
div.approvalTicket {
	background:url(/images/admin/ticketBack.png) 0px 0px no-repeat transparent;
	display:block;
	padding:5px 5px 5px 8px;
	
	width: 212px;
	height:85px;
	
	margin:0px 0px 3px 10px;
}

div.approvalTicket button {
	
	font-size:9px;
	margin-left:0px;
	
}

span.apDescrip {
	display:block;
	font-weight:bold;
	width:170px;
	height:20px;
	margin:0px 0px 0px 0px;
	padding:6px 0px 0px 25px;
}

span.apType {
	display:block;
	font-size:10px;
	color:#666666;
	margin:0px 0px 5px 0px;
	padding:0px 0px 0px 3px;
}

</style>

