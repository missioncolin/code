<?php

// Calculate which users to display based on slider input
// Process and display applicants as return value 

if (!empty($_GET)) {
	$sliderVal = $_GET['sliderValue'];
	$userID = $_GET['userID'];
	$jobID = $_GET['jobID'];
	$page = $_GET['page'];
}
else { echo "not assigned?!"; }

echo "Slider val ".$sliderVal;
echo "JobID ".$jobID;
echo "Page ".$page;
echo "UserID ".$userID;
echo "done!";


require dirname(__DIR__) . '/JobManager.php';

$j = new JobManager($db, $userID);

$offset  = 0;
$page    = 1;
$display = 10;

$currentSlider = array(); // [questionID]=>[sliderInput]

$offset = ($page - 1) * $display;

//$applicants = $j->getApplicants((int)$jobID, $offset, $display);
$total      = $j->totalApplicants($jobID);
echo "total: ".$total;

$allYearQuestions = $j->getYearsOfExperienceQuestions($jobID);

if (!empty($applicants)) {
	
	$visibleAppsByQ = array();
	$totalQs = array();
    //Return visible applicants if a slider question exists
    if (!empty($allYearQuestions)) {
    
		foreach ($allYearQuestions as $questionID=>$desc) {
		
				//Add array of visible applicants to list for all questions --> format: [53]=>[[0]=>[userID]];
				$visibleAppsByQ[$questionID] = $j->getApplicantVisibility(5, $jobID, $questionID, $applicants);  ////*** replace first param with slider input ***///
				
				foreach ($visibleAppsByQ as $questionID=>$userArray) {
					$visibleAppsByQ[$questionID] = $userArray;
					
				}
		}
		
		// Find intersection of all question visibility arrays so that 
		// ex, if Q53 has 103, 102 and Q52 has 103
		// final array of visibile applicants will be 103.
		// If only one question, just print it
		
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

print_r($finalVisibleList);
	



?>
