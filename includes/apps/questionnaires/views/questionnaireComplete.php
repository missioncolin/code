<?php 
	if (isset($_GET['qnrID'])) {	
		//the problem here is that we only have the questionniare ID and not the job ID - what do we do, carry, the job ID through via querystring? 
		print alert_box("Your questionnaire has been saved!",3);
	}		
//return to job list

?>
