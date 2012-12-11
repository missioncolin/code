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

// Stores all user year questions to define sliders
$qIDs = array();

$offset  = 0;
$page    = 1;
$display = 1;

$allYearQuestions = $j->getYearsOfExperienceQuestions($_GET['job']);

/* Check whether sliders have been changed/set */
if (isset($_REQUEST['slider-val'])) {
	$sliderParam = $_REQUEST['slider-val'];
}

if (isset($_REQUEST['master-val']) && $_REQUEST['master-val'] != 0) {
	$sliderParam = $_REQUEST['master-val'];
}

if (isset($_GET['page'])) {
    $page   = (int) $_GET['page'];
    $offset = ($page - 1) * $display;
}

if(isset($_REQUEST['name-search']) && strlen($_REQUEST['name-search']) > 0 ){
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
else{
	$applicants = $j->getApplicants((int)$jobID, $offset, $display); //no search criteria
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
	var sliderVals = sliderString.split(',');	
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
		console.log(count);
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
				sliderValueString = sliderValues.join(",");
								
				// Set hidden value to slider number
				$("#slider-val").val(sliderValueString);
				console.log($("#slider-val").val());
				document.sliderForm.submit();
		    }
		    
	    });
		    
		    console.log($(this).slider("value"));
		    // Get id number value 
		    var count = String(this.id).split("-");
		    
		    // Display value of slider & send to process-slider.php
			$( "#amount" + count[1]).html( $( this ).slider( "value" ) );        
	});
	
	/* Master Slider */
	
/*
	$('div#master-slider').bind('slide', function() {
		
		var masterVal = $(this).slider('option', 'value');
		
		$('div[id^="slider-"]').each('slide', function() {
				
			$(this).slider('option', 'value', masterVal);
			
		});
	
	});
*/
	
	$('div#master-slider').slider({
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
	        
	        // Set slider for all other sliders
			$('div[id^="slider-"]').live('each', function() {
				$(this).val(ui.value);
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

</script>
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
	
	echo alert_box('Use the following '.$sliders.' to select an inclusive minimum number of years for '.$qStr.'. Applicants who fit '.$theseStr.' will be displayed.', 3);
	echo "<ul class='sliderList'>";
	
	printf("%s", "Master Slider  ");
	/*** Master Slider ***/
	echo "<span id=\"master-amount\"></span>";
	//Display slider for this ID
	echo "<div id=\"master-slider\"></div><br>";

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
<input type="hidden" id="master-val" name="master-val" value="0">
<input type="hidden" id="slider-val" name="slider-val" value="0">
<input type="hidden" id="jobID" name="job" value="<?php echo $_REQUEST['job']; ?>">
<!-- everytime the slider is moved, reset page to the first page so that you don't get an 'empty' notice -->
<input type="hidden" id="page" name="page" value="1">
</form>


<form action="./applicant-list?job=<?php echo $_REQUEST['job']?>" id="searchForm" method="post">
<!--Name search box-->
<!--searches first name or last name-->
<?php
	echo "<div>";
	echo "Search By Name:<br/>";
	echo "<input id=\"name-search\" name=\"name-search\" type=\"text\"><input type=\"submit\" value=\"Search\" class=\"btn\" style=\"margin-left:10px;\">";
	echo "</div>";
	if ($searchString != null){
		echo "Searched For: ".$searchString;
	}
	echo "<div>&nbsp;</div>";
?>

<input type="hidden" id="jobID" name="job" value="<?php echo $_REQUEST['job']; ?>">
<input type="hidden" id="page" name="page" value="<?php echo $_REQUEST['page']; ?>">
</form> 



<section id="applicantList">

    <table>
        <tr>
            <th>Applicant Details</th>
            <th>Intervue Rating</th>
            <th>Applicant Grade</th>
        </tr>
    </table>
    <div id="apps">
    <table>
    <?php 
    if (!empty($applicants)) {
		foreach ($applicants as $a) {   
			
			$applicant = new User($db, $a['userID']);
			
			$colours = array(
				'recommend' => 'green',
				'average'   => 'yellow',
				'nq'        => 'red'
			);
			
			$class = $colours[$a['grade']];
			?>
	
			<tr id="newUser">
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