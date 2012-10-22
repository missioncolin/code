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

$qIDs = array();

$offset  = 0;
$page    = 1;
$display = 10;

$currentSlider = array(); // [questionID]=>[sliderInput]

if (isset($_GET['page'])) {
    $page   = (int) $_GET['page'];
    $offset = ($page - 1) * $display;
}


$applicants = $j->getApplicants($_GET['job'], $offset, $display);
$total      = $j->totalApplicants($_GET['job']);

$allYearQuestions = $j->getYearsOfExperienceQuestions($_GET['job']);

// Store all questions for use w Javascript
foreach ($allYearQuestions as $qID => $desc) {
	$qIDs[] = $qID;
}

// For each 'year' question, get list of applicants that match current slider parameter
?>

<script>

var sliderValues = new Array();
var page = <?php echo $page;?>;
var jobID = <?php echo $jobID;?>;
var userID = <?php echo $userID;?>;

$(function() {
    
    $( "#slider-range-max").slider({
        range: "max",
        min: 0,
        max: 20,
        value: 0,
        slide: function( event, ui ) {
            $( "#amount").val( ui.value );
            sliderValue = ui.value; // Update global value
            ajaxFunction();
        }
    });
 	$( "#amount").val( $( "#slider-range-max").slider( "value" ) );
    
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
	ajaxRequest.open("GET", "http://kristina.140b.git.resolutionim.com/includes/apps/jobs-manager/views/process-slider.php?sliderValue=" + sliderValue + "&jobID=" + jobID + "&page=" + page + "&userID=" + userID, true); // make a relative path
	ajaxRequest.send();
	
}




</script>

<?php


/*
if (!empty($applicants)) {
	
	$visibleAppsByQ = array();
	$totalQs = array();
    //Return visible applicants if a slider question exists
    if (!empty($allYearQuestions)) {
    
		foreach ($allYearQuestions as $questionID=>$desc) {
		
				//Add array of visible applicants to list for all questions --> format: [53]=>[[0]=>[userID]];
				$visibleAppsByQ[$questionID] = $j->getApplicantVisibility(5, $_GET['job'], $questionID, $applicants);  ////*** replace first param with slider input ***///
				
/*
				foreach ($visibleAppsByQ as $questionID=>$userArray) {
					$visibleAppsByQ[$questionID] = $userArray;
					
				}
		}
*/

		
		// Find intersection of all question visibility arrays so that 
		// ex, if Q53 has 103, 102 and Q52 has 103
		// final array of visibile applicants will be 103.
		// If only one question, just print it
		
/*
		if (count($visibleAppsByQ) > 1) {
			
			$visibleList = call_user_func_array('array_intersect', $visibleAppsByQ);
			foreach ($visibleList as $key=>$value) {
				$finalVisibleList[] = $value;
			}
			
		}	
		else {
			$visibleList = $visibleAppsByQ;
			foreach ($visibleList as $key=>$value) {
				$finalVisibleList[] = $value;
			}
		}
		
				//print_r($finalVisibleList);
	}

}	
*/
?>


<!-- Slider for each question -->
<!-- get question's question -->
<?php 

	foreach ($allYearQuestions as $id=>$desc) {
	
		//Print description
		printf("%s ", $desc);
		
		//Apply range for ID
		echo "<input type=\"text\" id=\"amount\" onChange=\"ajaxFunction();\" style=\"border:0;\"/>";
		echo "<br><div id=\"stuff\"></div>";
		//Display slider for this ID
		echo "<div id=\"slider-range-max\"></div></br></br>";
		
	} 

?>



<section id="applicantList">

    <table>
        <tr>
            <th>Applicant Details</th>
            <th>Intervue Rating</th>
            <th>Applicant Grade</th>
        </tr>
  <?php
  

    if (!empty($applicants)) {
        	    
        foreach ($applicants as $a) {   
	    
	    	//echo "<div id=\"user".$a['userID']."\>";  
	
/* 	    if (in_array($a['userID'], $finalVisibleList[0])) { */     
            $applicant = new User($db, $a['userID']);
            
            $colours = array(
                'recommend' => 'green',
                'average'   => 'yellow',
                'nq'        => 'red'
            );
            
            $class = $colours[$a['grade']];
            ?>
            <tr>
    			<td>
    			     <div class="imgWrap">
    			     	 
    			         <a href="/applications-detail?application=<?php echo $a['itemID']; ?>"><img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($applicant->info['Email']))); ?>?d=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/themes/Intervue/img/profilePicExample.jpg'); ?>&s=83" alt="<?php echo $applicant->info['First Name'] . " " . $applicant->info['Last Name']; ?>" /></a>
    			     	 
    			     </div>
    			     <a href="/applications-detail?application=<?php echo $a['itemID']; ?>"><strong><?php echo $applicant->info['First Name'] . " " . $applicant->info['Last Name']; ?></strong></a><br>
    			     <span><?php echo date('F jS, Y', strtotime($a['sysDateInserted'])); ?></span>
    			 </td>
    			<td>
        			<h2><?php echo $j->getApplicantRating($a['itemID']); ?><br />
        			<a href="/applications-detail?application=<?php echo $a['itemID']; ?>">Rating Details</a>
        			</h2>
                </td>
    			<td><a class="btn <?php echo $class; ?>"><?php echo $a['grade']; ?></a></td>
    		</tr>
  		<?php
    	}
/* 	       } */
        	    
    } else {
        ?><tr><td colspan="3">No applicants at this time.</td></tr><?php
    }
?>
    </table>

    <div class="pagination">
        <?php echo pagination($total, $display, $page, '/applicant-list?job=' . (int)$_GET['job'] . '&amp;page=', false); ?>
    </div>

</section>