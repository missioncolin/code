<!-- probably called somewhere else too -->
<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css" />

<?php

/*
if (isset($_GET['sliderValue'])) {
	$sliderVal = $_GET['sliderValue'];
	echo $sliderVal;
}
*/

require dirname(__DIR__) . '/JobManager.php';

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
/*

// Store all questions for use w Javascript
foreach ($allYearQuestions as $qID => $desc) {
	$qIDs[] = $qID;
}
*/

// For each 'year' question, get list of applicants that match current slider parameter
?>

<script>

var sliderValues = new Array();
var page = <?php echo $page;?>;
var jobID = <?php echo $jobID;?>;
var userID = <?php echo $userID;?>;
var i = 0;

$(function() {
    
    //ajaxFunction();
    for (i = 0; i < <?php echo count($allYearQuestions); ?>; i++) {
	    
	    $( "#" + i).slider({
		    range: "max",
		    min: 0,
		    max: 20,
		    value: 0,
		    // Each slide updates value label
		    create: function( event, ui ) {
		        //ajaxFunction();
		    },
		    slide: function( event, ui ) {
		        $( "#amount" + i).val( ui.value );

		    },
		    // When user stops sliding, update applicant list
		    stop: function( event, ui ) {
		        //Store value of ID
		        sliderValues[this.id] = ui.value;
		        //ajaxFunction();
		        callAjax();
		    }
    });
    
 		$( "#amount" + i).val( $( "#" + i).slider( "value" ) );

	    
    }
        
});

function callAjax() {
	
	if (i == <?php echo count($allYearQuestions); ?>) {
		
		// All sliders have been loaded, call ajax
		ajaxFunction();
	}
}

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
/* 	    $( "#stuff" ).fadeOut(300); */
		var ajaxDisplay = document.getElementById('stuff');
		
		if (ajaxDisplay != null) {
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
		    $( "#stuff" ).fadeIn(300);			
		}

	}
	
	//Send a request:
	// 1. Specify URL of server-side script that will be used in Ajax app
	// 2. Use send function to send request
	sliderValues = sliderValues.join(",");
	alert(sliderValues);
	ajaxRequest.open("GET", "http://kristina.140b.git.resolutionim.com/includes/apps/jobs-manager/views/process-slider.php?sliderValue=" + sliderValues + "&jobID=" + jobID + "&page=" + page + "&userID=" + userID, true); // make a relative path
	ajaxRequest.send();
	
}


</script>

<!-- Slider for each question -->
<!-- get question's question -->
<?php 

	$i = 0;
	foreach ($allYearQuestions as $id=>$desc) {
	
		//Print description
		printf("%s ", $desc);
		
		//Apply range for ID
		echo "<input type=\"text\" id=\"amount".$i."\" style=\"border:0;\"/>";
		
		//Display slider for this ID
		echo "<div id=\"".$i."\"></div></br></br>";
		$i++;
		
	} 

?>



<section id="applicantList">

    <table>
        <tr>
            <th>Applicant Details</th>
            <th>Intervue Rating</th>
            <th>Applicant Grade</th>
        </tr>
    </table>
    <div id="stuff"></div>


    <div class="pagination">
        <?php echo pagination($total, $display, $page, '/applicant-list?job=' . (int)$_GET['job'] . '&amp;page=', false); ?>
    </div>

</section>