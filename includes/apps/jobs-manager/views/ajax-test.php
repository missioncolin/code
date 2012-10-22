<!-- probably called somewhere else too -->
<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css" />

<?php $userID = 108; $jobID = 15; 

$sliderVal = 0;
?>

<script language="javascript" type="text/javascript">

var sliderValue = 0;

$(function() {
    $( "#slider-range-max" ).slider({
        range: "max",
        min: 0,
        max: 20,
        value: 0,
        slide: function( event, ui ) {
            $( "#amount" ).val( ui.value );
            sliderValue = ui.value; // Update global value
            ajaxFunction();

        }
     });
    $( "#amount" ).val( sliderValue );
});
    
function ajaxFunction() {
	
	var ajaxRequest;
	
	try {
		
		// Handles Opera, Firefox, Safari, Chrome
		ajaxRequest = new XMLHttpRequest();
		
	} catch(e) {
		// Handles IE...
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch(e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch(e) {
				alert("There has been an error.");
				return false;
			}
		}
	}
	
	// Receive data
	ajaxRequest.onreadystatechange = function() {
	if (ajaxRequest.readyState == 4) // Ready to receive {
		var ajaxDisplay = document.getElementById('stuff');
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
	}
	
	//Send a request:
	// 1. Specify URL of server-side script that will be used in Ajax app
	// 2. Use send function to send request
	ajaxRequest.open("GET", "process-slider.php?sliderValue=" + sliderValue, true);
	ajaxRequest.send();
	
}



</script>


<form name="myForm"/>

<!-- <input type="text" id="amount" style="border:0;"> -->
<div id = "slider-range-max" style="width:200px;"</div></br>

<!-- Set as input so that each time slider changes, this updates -->
<div id="stuff"></div>
Time: <input type="text" name="time"/>
Value: <input type="text" onChange="ajaxFunction();" id="amount" name="amount" style="border:0;"/>
</form>
