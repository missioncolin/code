<!-- probably called somewhere else too -->
<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>


<?php

/*
if (isset($_GET['sliderValue'])) {
	$sliderVal = $_GET['sliderValue'];
	echo $sliderVal;
}
*/

require dirname(__DIR__) . '/JobManager.php';

$ajaxFile = dirname(__DIR__) . '/ajax/process-slider.php';

$j = new JobManager($db, $_SESSION['userID']);

// Variables to pass for processing
$userID = $_SESSION['userID'];
$jobID = $_GET['job'];

$total      = $j->totalApplicants($jobID);


// Stores all user year questions to define sliders
$qIDs = array();

$offset  = 0;
$page    = 1;
$display = 10;

$currentSlider = array(); // [questionID]=>[sliderInput]

if (isset($_GET['page'])) {
    $page   = (int) $_GET['page'];
    $offset = ($page - 1) * $display;
}

$applicants = $j->getApplicants((int)$jobID, $offset, $display);
$allYearQuestions = $j->getYearsOfExperienceQuestions($_GET['job']);


?>

<script>

var sliderValues = new Array(); // Stores each slider value as updated
var sliderValueString; // Stores joined array of slider values
var page = <?php echo $page;?>;
var jobID = <?php echo $jobID;?>;
var userID = <?php echo $userID;?>;

// Initialize all sliders
for (var i = 0; i < <?php echo count($allYearQuestions);?>; i++) {
	sliderValues.push(0);  
}

sliderValueString = sliderValues.join(",");
ajaxFunction();	

$(function() {
    
    //ajaxFunction();
	$('div[id^="slider-"]').each(function() {
	
		$(this).slider({
		    range: "max",
		    min: 0,
		    max: 20,
		    value: 0,
		    // Each slide updates value label
		    slide: function( event, ui ) {
		    	var count = String(this.id).split("-");		    	
		        $( "#amount" + count[1]).html( ui.value );
		        $( "#apps" ).fadeOut(100);
		    },
		    // When user stops sliding, update applicant list
		    stop: function( event, ui ) {
		        //Store value of ID to store slider value
		        var count = String(this.id).split("-");	
		        sliderValues[count[1]] = ui.value;
		        
		        // Create string from values
				// and submit to process-slider.php
				sliderValueString = sliderValues.join(",");
				console.log(sliderValues);
				ajaxFunction();	
			
				/* ajaxFunction(); */
	
		    }
		    
		    });
		    
		    // Get id number value 
		    var count = String(this.id).split("-");
		    
		    // Display value of slider & send to process-slider.php
			$( "#amount" + count[1]).html( $( this ).slider( "value" ) );        
	});
	
	
	$('.name-search').keyup(function() {
		//nameSearch(this);
		var that = this;
		var origValue = $(this).val();
		var value = origValue.replace("'", "");
		
		console.log("called:"+value);
				
				
		$( "#apps" ).fadeOut(100);
		$.ajax({
			type: "GET",
			url: "/includes/apps/jobs-manager/ajax/name-search.php",
			data: {
				'searchKeyword' : value, 
				'jobID' : jobID, 
				'page' : page, 
				'userID' : userID
				},
				dataType: "text",
				success: function(msg){
				//we need to check if the value is the same
					if (value==$(that).val()) {
						console.log(msg);
						//Receiving the result of search here
						var ajaxDisplay = document.getElementById('apps');
		
						if (ajaxDisplay != null) {
							ajaxDisplay.innerHTML = msg;
							$( "#apps" ).fadeIn(100);
						}
					}
				}
		});			

	});
	
	
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

		var ajaxDisplay = document.getElementById('apps');
		
		if (ajaxDisplay != null) {
		
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
			$( "#apps" ).fadeIn(100);
		}			

	}
	
	//Send a request:
	// 1. Specify URL of server-side script that will be used in Ajax app
	// 2. Use send function to send request
	var parameters = 
	ajaxRequest.open("GET", "/includes/apps/jobs-manager/ajax/process-slider.php?sliderValue=" + sliderValueString + "&jobID=" + jobID + "&page=" + page + "&userID=" + userID, true); // make a relative path
	ajaxRequest.send();
	
}

//function nameSearch(){
//	alert("namesearch()!");
//}


</script>

<!-- Slider for each question -->
<!-- get question's question -->
<?php 

	// Display alert with instructions to use sliders
	if (count($allYearQuestions) > 1) {
		$sliders = "sliders";
		$theseStr = "these restrictions";
		$qStr = "each question";
	}
	else {
		$sliders = "slider";
		$theseStr = "this restriction";
		$qStr = "this question";
	}
	
	echo alert_box('Use the following '.$sliders.' to select an inclusive minimum number of years for '.$qStr.'. Applicants who fit '.$theseStr.' will be displayed.', 3);
	echo "<ul class='sliderList'>";
	$i = 0;
	foreach ($allYearQuestions as $id=>$desc) {
	
		echo "<li>";
	
		//Print description
		printf("%s ", $desc);
		
		//Apply range for ID
		echo "<span id=\"amount".$i."\"></span>";
		
		//Display slider for this ID
		echo "<div id=\"slider-".$i."\"></div>";
		echo "</li>";
		$i++;
		
	} 
	echo "</ul>";

?>

<!--Name search box-->
<!--searches first name or last name-->
<?php
	echo alert_box('Use the following input box to search for an applicant by first or last name. Applicant who fit this restriction will be displayed.', 3);
	echo "<div>";
	echo "First or Last Name:<br/>";
	echo "<input id=\"name-search\" class=\"name-search\" type=\"text\">";
	echo "</div>";
	echo "<div>&nbsp;</div>";
?>


<section id="applicantList">

    <table>
        <tr>
            <th>Applicant Details</th>
            <th>Intervue Rating</th>
            <th>Applicant Grade</th>
        </tr>
    </table>
    <div id="apps"></div>


    <div class="pagination">
        <?php echo pagination($total, $display, $page, '/applicant-list?job=' . (int)$_GET['job'] . '&amp;page=', false); ?>
    </div>

</section>