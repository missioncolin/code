<!-- probably called somewhere else too -->
<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>


<?php

require dirname(__DIR__) . '/JobManager.php';

$ajaxFile = dirname(__DIR__) . '/ajax/process-slider.php';

$j = new JobManager($db, $_SESSION['userID']);

// Variables to pass for processing
$userID = $_SESSION['userID'];
$jobID = $_REQUEST['job'];
$searchString = null;
$urlString = "";
$urlSlider = "";
$sliderParam = null;
$urlMaster = "";
$jobTitle = "";

// Stores all user year questions to define sliders
$qIDs = array();

$offset  = 0;
$page    = 1;
$display = 10;

$allYearQuestions = $j->getYearsOfExperienceQuestions($_GET['job']);
$jobInfo = $j->getJob($jobID);

/* Checking to see whether we're back at the main list from the filtered candidates */

/* If visiting for the first time */
if (isset($_SESSION['sliderParams']) && !isset($_REQUEST['backToList']) && !isset($_REQUEST['slider-val'])) {
	unset($_SESSION['sliderParams']);
	unset($_SESSION['masterSlider']);
	unset($_SESSION['setMaster']);
}

/* Check whether sliders have been changed/set */
if (isset($_REQUEST['slider-val'])  && strlen($_REQUEST['slider-val']) > 0) {
	$sliderParam = $_REQUEST['slider-val'];
	$_SESSION['sliderParams'] = $sliderParam;
	unset($_SESSION['setMaster']);
}

/* If master value has been set - store the value in the session in case the page is left */
if (isset($_REQUEST['master-val']) && $_REQUEST['master-val'] != 0) {
	$sliderParam = $_REQUEST['master-val'];
	$_SESSION['masterSlider'] = $sliderParam;
	$_SESSION['setMaster'] = 1;
}

/* Check whether sessions are still in place from details page */
if (isset($_SESSION['sliderParams']) && isset($_REQUEST['backToList'])) {
	$_REQUEST['slider-val'] = $_SESSION['sliderParams'];
	$sliderParam = $_SESSION['sliderParams'];
}

/* Handles master slider setting other sliders */
if (isset($_SESSION['masterSlider']) && isset($_REQUEST['backToList']) && isset($_SESSION['setMaster'])) {
	$_REQUEST['master-val'] = $_SESSION['masterSlider'];
	$sliderParam = $_SESSION['masterSlider'];
}
else if (isset($_SESSION['masterSlider']) && isset($_REQUEST['backToList']) && !isset($_SESSION['setMaster'])) {
	$_REQUEST['master-val'] = 0;
}


if (isset($_GET['page'])) {
    $page   = (int) $_GET['page'];
    $offset = ($page - 1) * $display;
}

if (isset($_REQUEST['name-search']) && strlen($_REQUEST['name-search']) > 0 ){
	$searchString = $_REQUEST['name-search'];
}



if ($searchString != null){
	$applicants = $j->getNameMatches($searchString, (int)$jobID, $offset, $display);
	$total      = $j->getNameMatchCount($searchString, (int)$jobID);
	$urlString  = "&amp;name-search=".$searchString;
}
else if ($sliderParam != null) {

	/* Get all applicants arranged so that pagination
	 * will actually work -- gets all applicants to pass to the function
	 * to get all slider matches, and then displays only those within the offset/display
	 * variables
	*/
	$allApplicants = $j->getApplicants((int)$jobID, 0, 1000);
	$visibleApps = $j->getSliderMatches($allYearQuestions, $allApplicants, $sliderParam, $jobID);
	$applicants = $j->getApplicantInfo($visibleApps, (int)$jobID, $offset, $display);
	
	/* Total applicants that fit the criteria */
	$total = count($visibleApps);
	
	/* Set up pagination url */
	$urlMaster = "&amp;master-val=".$_REQUEST['master-val'];
	$urlSlider  = "&amp;slider-val=".$_REQUEST['slider-val'];

}

/* No search criteria or slider query */
else{
	$applicants = $j->getApplicants((int)$jobID, $offset, $display);
	$total      = $j->totalApplicants($jobID);
}

?>

<script>

/******** Handles all of the slider functionality ********/

var sliderValues = new Array(); // Stores each slider value as updated
var sliderValueString; // Stores joined array of slider values
var page = <?php echo $page;?>;
var jobID = <?php echo $jobID;?>;
var userID = <?php echo $userID;?>;
var offset = <?php echo $offset ?>;

/* Prep for slider values if pre-existing */
var sliderString = '<?php echo isset($_REQUEST['slider-val']) ? $_REQUEST['slider-val'] : '0'; ?>';

var masterSlider = '<?php echo isset($_REQUEST['master-val']) ? $_REQUEST['master-val'] : '0'; ?>'

if (sliderString != '0') {
	var sliderVals = sliderString.split('_');	
}

else {
	var sliderVals = 0;
}


// Initialize all sliders
for (var i = 0; i < <?php echo count($allYearQuestions);?>; i++) {
	if (sliderVals != 0) {
	
		/* If count of array is > 1, not master */
		if (sliderVals.length > 1) {
			sliderValues.push(sliderVals[i]);  
		}
		/* Apply master value to each slider */
		else {
			sliderValues.push(sliderVals[0]);
		}

	}
	/* Sliders haven't been set; default to 0 */
	else {
		sliderValues.push(0);
	}
}

$(function() {
    
    //ajaxFunction();
	$('div[id^="slider-"]').each(function() {
		
		var count = String(this.id).split("-");	

		if (masterSlider != 0) {
			var thisValue = masterSlider; 
		}
		else if (sliderValues.len != 0) {
			var thisValue = sliderValues[count[1]];
		}
		else {
			var thisValue = 0;
		}
		
		$(this).slider({
			animate: true,
		    range: "max",
		    min: 0,
		    max: 20,
		    value: Number(thisValue),
		    // Each slide updates value label
		    slide: function( event, ui ) {	    	
		        $( "#amount" + count[1]).html( ui.value );
		        $( "#apps" ).fadeOut(100);
				
		    },
		    // When user stops sliding, update applicant list
		    stop: function( event, ui ) {
		        //Store value of ID to store slider value
		        sliderValues[count[1]] = ui.value;
		        // Create string from values
				// and submit to process-slider.php
				sliderValueString = sliderValues.join("_");
								
				// Set hidden value to slider number
				$("#slider-val").val(sliderValueString);
				document.sliderForm.submit();
		    }
		    
	    });
		    
		    // Get id number value 
		    var count = String(this.id).split("-");
		    
		    // Display value of slider & send to process-slider.php
			$( "#amount" + count[1]).html( $( this ).slider( "value" ) );        
	});

	$('div#master-slider').slider({
		animate: true,
	    range: "max",
	    min: 0,
	    max: 20,
	    value: Number('<?php echo isset($_REQUEST['master-val']) ? $_REQUEST['master-val'] : '0'; ?>'),
	    create: function( event, ui ) {
		    $( "#master-amount").html( $( this ).slider( "value" ) );   
	    },
	    // Each slide updates value label
	    slide: function( event, ui ) {   	
	        $( "#master-amount").html( ui.value );
	        $( "#apps" ).fadeOut(100);
	        
	        // Set other sliders as master slider slides		        
	        $('.sliders').each(function() {
			
		       $(this).slider('option', 'value', ui.value);
			   // Get id number value 
			    var count = String(this.id).split("-");			    
			    // Display value of slider & send to process-slider.php
				$( "#amount" + count[1]).html( $( this ).slider( "value" ) );  
			});
			
	    },
	    // When user stops sliding, update applicant list
	    stop: function( event, ui ) {
							
			// Set hidden value to slider number
			$("#master-val").val(ui.value);
			// Display value of slider & send to process-slider.php
			$( "#master-amount").html( $( this ).slider( "value" ) );   
			
			document.sliderForm.submit();
	    }
	    
    });
	        
	
});

/********* end jquery UI functionality ***********/

</script>

<section id="applicant-list-sidebar">

<!-- search by name-->
<form action="./applicant-list?job=<?php echo $_REQUEST['job']?>" id="searchForm" method="post">
<!--Name search box-->
<!--searches first name or last name-->
<?php
	echo "<div>";
	echo "Search By Name:<br/>";
	echo "<input id=\"name-search\" name=\"name-search\" type=\"text\"><br /><input type=\"submit\" value=\"Search\" class=\"btn\" style=\"margin-top: 5px;\">";
	echo "</div>";
	if ($searchString != null){
		echo "Searched For: ".$searchString;
	}
	echo "<div>&nbsp;</div>";
?>

<input type="hidden" id="jobID" name="job" value="<?php echo $_REQUEST['job']; ?>">
<!-- Page was request variable with "index not found" but it's being set above, is this right?--> 
<input type="hidden" id="page" name="page" value="<?php echo $page; ?>"> 
</form> 

<!--select list-->
<form action="./applicant-list?job=<?php echo $_REQUEST['job']?>" id="selectForm" method="get">
	<div> 
		Select</br>
		<input type="checkbox" name="selectFilter" value="topCandidate" id="topCandidate"><label for="topCandidate"> Top Candidates</label></br>
		<input type="checkbox" name="selectFilter" value="hasPotential" id="hasPotential"><label for="hasPotential"> Has Potential</label></br>
		<input type="checkbox" name="selectFilter" value="meetsSkills" id="meetsSkills"><label for="meetsSkills"> Meets Required Skills</label></br>
		<input type="checkbox" name="selectFilter" value="unviewed" id="unviewed"><label for="unviewed"> Unviewed Applicant</label></br>
	</div>

</form>



<!-- sliders -->
<form name="sliderForm" action="./applicant-list?job=<?php echo $_REQUEST['job']?>" method="get">
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
	
	//tips box removed at request of client
	//echo alert_box('<h2>Tips</h2>Use the following '.$sliders.' to select an inclusive minimum number of years for '.$qStr.'. Applicants who fit '.$theseStr.' will be displayed.', 3);
	echo "<ul class='sliderList'>";
	
	printf("%s", "Master Slider  ");
	/*** Master Slider ***/
	echo "<span id=\"master-amount\"></span>";
	//Display slider for this ID
	echo "<div id=\"master-slider\"></div>";

	$i = 0;
	foreach ($allYearQuestions as $id=>$desc) {
	
		echo "<li>";
	
		//Print description
		printf("%s ", $desc);
		
		//Apply range for ID
		echo "<span id=\"amount".$i."\"></span>";
		//Display slider for this ID
		echo "<div class=\"sliders\" id=\"slider-".$i."\"></div>";
		echo "</li>";
		$i++;
		
	} 
	echo "</ul>";

?>
<input type="hidden" id="master-val" name="master-val" value="0">
<input type="hidden" id="slider-val" name="slider-val" value="0">
<input type="hidden" id="jobID" name="job" value="<?php echo $_REQUEST['job']; ?>">
<!-- everytime the slider is moved, reset page to the first page so that you don't get an 'empty' notice -->
<input type="hidden" id="page" name="page" value="1">
</form>


</section>


<section id="applicantList">
    <p>Viewing applicants<?php if (isset($jobInfo['title'])){ echo " for <strong>" . $jobInfo['title'] . "</strong>"; }?></p>
    <table>
        <tr>
            <th><!--Intervue -->Rating</th>
            <th>Picture</th>
            <th>Details</th>
            <th>Resume</th>
            <th class="coverLetter">Cover Letter</th>
            <th>Video<!-- Answers--></th>
            <th>Rate Applicant</th>
        </tr>
    <div id="apps">
    <?php 
    if (!empty($applicants)) {
    
    	/* Store and serialize array of applicants */
    	$visList = array();
    	
    	foreach ($applicants as $a) {
	    	$visList[] = $a['itemID'];
    	}
    	
    	/* Serialize the array of visible applicants */
    	$serList = serialize($visList);
		
		/* Store in a session to be received by app detail view */
		$_SESSION['filterList'] = $serList;
		
		foreach ($applicants as $a) {   
			
			$applicant = new User($db, $a['userID']);
			
			//get applicant info fields 
			if (isset($applicant->info["Phone Number"]))	{$phone = $applicant->info["Phone Number"]."<br/>";}	else	{$phone = "";}  
			if (isset($applicant->info["Company City"]))	{$city = $applicant->info["Company City"]."<br/>"; }	else	{$city = "";}
			
			$colours = array(
				'recommend' => 'green',
				'average'   => 'yellow',
				'nq'        => 'red'
			);
			
			$class = $colours[$a['grade']];
			
			?>
	
			<tr id="newUser">
				<td>
					<h2><?php echo $j->getApplicantRating($a['itemID']); ?><br />
						<a href="/applications-detail?application=<?php echo $a['itemID']; ?>">Rating Details</a>
					</h2>
				</td>
				<td>
					<!--<div class="imgWrap">-->
					<div class="imgWrapList">
						<a href="/applications-detail?application=<?php echo $a['itemID']; ?>">
							<img style="width:85px;" src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($applicant->info['Email']))); ?>?d=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/themes/Intervue/img/profilePicExample.jpg'); ?>&s=83" alt="<?php echo $applicant->info['First Name'] . " " . $applicant->info['Last Name']; ?>" />
						</a>
					</div>	
					
				</td>
				<td>
					<a href="/applications-detail?application=<?php echo $a['itemID']; ?>"><strong><?php echo $applicant->info['First Name'] . " " . $applicant->info['Last Name']; ?></strong></a><br>
					<span><?php echo $city; ?></span>
					<span><?php echo $phone; ?></span>
					<span><?php echo $applicant->info['Email']; ?></span><br/>
					<span><?php echo date('M jS', strtotime($a['sysDateInserted'])); ?></span>
					
				</td>
				<td>
				       <a href="#" class="grade btn lightGrey"><img src="/themes/Intervue/img/resumeIconDark.png" alt="" /></a>
				</td>
				<td>
					<a href="#" class="grade btn lightGrey"><img src="/themes/Intervue/img/coverLetterIconDark.png" alt="" /></a>
				</td>
				<td>
					<a href="#" class="grade btn lightGrey playBtn"><img src="/themes/Intervue/img/playBtn.png" alt="" /></a>
				</td>
				<td>
					<a href="#" class="btn green">Top Candidate</a>
					<a href="#" class="btn yellow">Has Potential</a>
					
				</td>
			
			</tr>
			
			<?php
		
		}
	}
	else {
	?>	
		<tr id="newUser">
			<td>
				No users fit this criteria
			</td>
		</tr>
		
	<?php
	} 
    
    
    ?>	        
    </table>
    </div>


    <div class="pagination">
        <?php echo pagination($total, $display, $page, '/applicant-list?job=' . (int)$_GET['job'] . $urlString . $urlSlider . $urlMaster . '&amp;page=', false); ?>
    </div>

</section>