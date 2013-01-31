<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css" />

<?php

require '../../../init.php';
require dirname(__DIR__) . '/JobManager.php';

// Calculate which users to display based on slider input
// Process and display applicants as return value 


$sliderVal = $_GET['sliderValue'];

$sliderValues = explode(",", $sliderVal);

$userID = $_GET['userID'];
$jobID = $_GET['jobID'];

$j = new JobManager($db, $userID);

$display = 2;
$page = 1;

if (isset($_GET['page'])) {
    $page   = (int) $_GET['page'];
    $offset = ($page - 1) * $display;
}

$currentSlider = array(); // [questionID]=>[sliderInput]

$offset = ($page - 1) * $display;

$applicants = $j->getApplicants((int)$jobID, $offset, $display);
$finalVisibleList = array();

$allYearQuestions = $j->getYearsOfExperienceQuestions($jobID);

if (!empty($applicants)) {
	
	$visibleAppsByQ = array();
	$totalQs = array();
    //Return visible applicants if a slider question exists
    if (!empty($allYearQuestions)) {
    
    	$y = 0;
		foreach ($allYearQuestions as $questionID=>$desc) {
		
				// If empty, has not been changed - default 0
				if ($sliderValues[$y] == "") {
					$sliderVal = 0;
				}
				
				else {
					$sliderVal = $sliderValues[$y];
				}
				
				//Add array of visible applicants to list for all questions --> format: [53]=>[[0]=>[userID]];
				$visibleAppsByQ[$questionID] = $j->getApplicantVisibility($sliderVal, $jobID, $questionID, $applicants); 
								
				foreach ($visibleAppsByQ as $questionID=>$userArray) {
					$visibleAppsByQ[$questionID] = $userArray;
					
				}
				
				$y++;
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
				$finalVisibleList = $value;
			}
		}
		
	}


?>

<table>
<?php

        if (!empty($finalVisibleList)) {

	                	    
	        foreach ($applicants as $a) {   
		 	    
		 	    if (in_array($a['userID'], $finalVisibleList)) {     
		            
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
		        	    
		}
		else { 
				
			?><tr><td colspan="3">No applicants fit this criteria.</td></tr><?php
	    
	    } 
    
}else {
    ?><tr><td colspan="3">No applicants at this time.</td></tr><?php
}
	



?>

</table>
