<?php

class Notify
{

	var $userID;
	var $success = true;	

	function __construct()
	{
		if (isset($_SESSION['myId'])) {
			$this->userID = (int) $_SESSION['myId'];
		} else if (isset($_SESSION['user_ID'])) {
			$this->userID = (int) $_SESSION['user_id'];
		}
	}
 
	
	
	function send_email($comment, $toWho) 
	{
		global $user, $db, $quipp;
		
		if(!is_array($toWho)) {
			if(is_numeric($toWho)) {
				$toWho = array($toWho); //if only one user is passed in, put them in an array anyway
			} else {
				return false;
			}
		}
		
		foreach($toWho as $who) {
			$thisUser = $user->get_details($who);
			
			$quipp->system_log("Sending E-Mail To: " . $who);
			
			if(isset($thisUser['Send Notification Emails']) && $thisUser['Send Notification Emails'] == '1') {
			
				// multiple recipients
				//$to  = $thisUser['E-Mail']; // note the comma
				$to = $thisUser['First Name']. " " . $thisUser['Last Name'] . " <".$thisUser['E-Mail'].">";
				
				// subject
				$subject = strip_tags($comment);
				
				// message
				$message = "
				<html>
				<head>
				  <title>Web Site Message</title>
				</head>
				<body>
				<img src=\"http://" . $_SERVER['HTTP_HOST'] . "/images/icons/emailHeader.gif\" />
				
				
				<div style=\"background-color: #4788BC; font-family: helvetica; color:#ACDCFC; font-size: 140%; border-top: 1px solid #003270; padding: 2px; margin-top:10px;\">&nbsp; Web Site Notification</div>
				<p>Hello " . $thisUser['First Name']. " " . $thisUser['Last Name'] . ", there has been some activity on the web site which you might want to know about or may require action.</p>
				
				<div style=\"background-color: #E3E3E3; font-family: helvetica; font-size: 100%; border: 1px dashed #CCCCCC; padding: 5px;\">
				<p>$comment</p>
				</div>
				
				<p>This is an automated notification that has been sent to you because you are involved in a role related to the item above. 
				If you do not wish to receive these messages, you can block e-mail notifications in your site profile.</p>
				</body>
				</html>
				";
				
				// To send HTML mail, the Content-type header must be set
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				
				// Additional headers
				//$headers .= "To: ".$thisUser['First Name']. " " . $thisUser['Last Name'] . " <".$thisUser['E-Mail'].">" . "\r\n";
				$headers .= 'From: Web Site Robot <no-reply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
				
				
				// Mail it
				if(!mail($to, $subject, $message, $headers)) {
					break;
				}
			}
		}		
		return true;
	}
	

	


	
	
}

?>