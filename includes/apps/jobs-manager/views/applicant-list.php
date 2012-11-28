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
$searchString = null;
$urlString = "";



// Stores all user year questions to define sliders
$qIDs = array();

$offset  = 0;
$page    = 1;
$display = 2;


$currentSlider = array(); // [questionID]=>[sliderInput]

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
}else{
	$applicants = $j->getApplicants((int)$jobID, $offset, $display); //no search criteria
	$total      = $j->totalApplicants($jobID);
}



$allYearQuestions = $j->getYearsOfExperienceQuestions($_GET['job']);


?>

<script>

var sliderValues = new Array(); // Stores each slider value as updated
var sliderValueString; // Stores joined array of slider values
var page = <?php echo $page;?>;
var jobID = <?php echo $jobID;?>;
var userID = <?php echo $userID;?>;
var offset = <?php echo $offset ?>;

// Initialize all sliders
for (var i = 0; i < <?php echo count($allYearQuestions);?>; i++) {
	sliderValues.push(0);  
}

sliderValueString = sliderValues.join(",");
ajaxFunction();	

$(function() {
    
    //ajaxFunction();
	/*$('div[id^="slider-"]').each(function() {
	
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
	
		    }
		    
		    });
		    
		    // Get id number value 
		    var count = String(this.id).split("-");
		    
		    // Display value of slider & send to process-slider.php
			$( "#amount" + count[1]).html( $( this ).slider( "value" ) );        
	});*/
	
	
	/*$('.name-search').keyup(function() {
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
				'userID' : userID, 
				'offset' : offset
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

	});*/
	
	
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
	//var parameters = 
	//ajaxRequest.open("GET", "/includes/apps/jobs-manager/ajax/process-slider.php?sliderValue=" + sliderValueString + "&jobID=" + jobID + "&page=" + page + "&userID=" + userID+"&offset="+offset, true); // make a relative path
	//ajaxRequest.send();
	
}

//function nameSearch(){
//	alert("namesearch()!");
//}


</script>
<form action="./applicant-list?job=<?php echo $_REQUEST['job']?>" id="searchForm" method="post">
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
		echo "<div id=\"slider-".$i."\">SLIDER COMMENTED OUT - SEE CODE</div>";
		echo "</li>";
		$i++;
		
	} 
	echo "</ul>";

?>

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

<input type="hidden" id="jobID" name="jobID" value="<?php echo $_REQUEST['jobID']; ?>">
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
    
    
    ?>	        
    </table>
    </div>


    <div class="pagination">
        <?php echo pagination($total, $display, $page, '/applicant-list?job=' . (int)$_GET['job'] .$urlString.'&amp;page=', false); ?>
    </div>

</section>