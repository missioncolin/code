<?php 

//this is a class to manage user feedback and error handling
class Feedback {

	var $userID;
	
	
	function __construct() 
	{	
		//if (isset($_SESSION['myId'])) { 
			//$this->userID = (int) $_SESSION['myId'];
		//} 
	}
	
	function display_messages_direct() 
	{
		
	
	
	}
	
	
	function display_messages($flush = false) 
	{
		if(isset($_SESSION['quipp']['feedback']) && count($_SESSION['quipp']['feedback']) > 0) {
			
			print "<script type=\"text/javascript\">";
			foreach($_SESSION['quipp']['feedback'] as $messageContent) {
			
				switch($messageContent['type']) {
				case 1: $iconImagePath = "/admin/js/growl/images/confirm_24.png"; //good 
				break;
				case 2: $iconImagePath = "/admin/js/growl/images/cancel_24.png"; //bad 
				break;
				default: $iconImagePath = "/admin/js/growl/images/info_24.png"; //info
				break;
				}
				print "
				$.gritter.add({
					// (string | mandatory) the heading of the notification
					title: \"" . addslashes($messageContent['title']) . "\",
					// (string | mandatory) the text inside the notification
					text: \"" . addslashes($messageContent['message']) . "\",
					// (string | optional) the image to display on the left
					image: \"$iconImagePath\",
					// (bool | optional) if you want it to fade out on its own or just sit there
					sticky: false, 
					// (int | optional) the time you want it to be alive for before fading out
					time: \"\"
				}); ";
			
			}
			
			print "</script>";
		
				
			
			
						
		} 
		
		if($flush) {
			$_SESSION['quipp']['feedback'] = array();
		}
	}
	
	//register a message to display to the user
	function message($message, $title = "Notice", $type = "3") {
		global $DRAGGIN;
		
		if(!isset($_SESSION['quipp']['feedback'])) {
			$_SESSION['quipp']['feedback'] = array();
		}
		
		
		array_push($_SESSION['quipp']['feedback'], array("message"=>$message, "title"=>$title, "type"=>$type));
		
	}
	

}


?>