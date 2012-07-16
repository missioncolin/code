/* Author: 

*/


function toggleArrow(myGID) {
    if ($('#'+myGID).attr('src') == "/images/arrow1.gif") {
        $('#'+myGID).attr('src', "/images/arrow2.gif");
    } else {
    	$('#'+myGID).attr('src', "/images/arrow1.gif");
    }
}

//a feedback function that mimics $feedback->message() in Feedback.php, this can be used for ajax calls where a postback isn't possible
function feedback(message, title, type) {

	if($.gritter) {
		
		switch(type) {
			case 1: imageToDisplay = "/admin/js/growl/images/confirm_24.png"; //good 
			break;
			case 2: imageToDisplay = "/admin/js/growl/images/cancel_24.png"; //bad 
			break;
			default: imageToDisplay = "/admin/js/growl/images/info_24.png"; //info
			break;
		}
		
		$.gritter.add({
			// (string | mandatory) the heading of the notification
			title: title,
			// (string | mandatory) the text inside the notification
			text: message,
			// (string | optional) the image to display on the left
			image: imageToDisplay,
			// (bool | optional) if you want it to fade out on its own or just sit there
			sticky: false, 
			// (int | optional) the time you want it to be alive for before fading out
			time: ""
		}); 
	} else {
		console.log(message);
		//alert();	
	}

}







