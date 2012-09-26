<?php 

require dirname(__DIR__) . '/JobManager.php';
$jobManager = new JobManager($db, $_SESSION['userID']);


function get_user_specific_values($userID, $fields = array()){
	if (preg_match("/^[0-9]+$/",$userID,$matches) && is_array($fields) && count($fields) > 0){
		for ($i = 0; $i < count($fields); $i++){
			if (trim($fields[$i]) != ""){
				$whereData[] = sprintf("(ugv.`userID` = %d AND ugf.`fieldLabel` = '%s')",(int)$userID,(string)$fields[$i]);
			}
		} 
	}
	if (isset($whereData)){
		$qry = "SELECT ugv.`value`, ugf.`fieldLabel` FROM `sysUGFValues` ugv INNER JOIN `sysUGFields` ugf ON ugv.`fieldID` = ugf.`itemID` 
		WHERE ".implode(" OR ",$whereData);
		$res = mysql_query($qry);
		return $res;
		
	}
	return false;
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
        
        //select info from tblAnswers, group by userID, questionID 
        //value is sum saved in //answers table
        $usersQry = sprintf("SELECT userID AS 'userID', jobID AS 'jobID' 
        			FROM tblAnswers 
        			WHERE  jobID = '%d' AND sysActive = '1' and sysOpen = '1'
        			GROUP BY userID, jobID", $_GET['job']);
        $usersRS = mysql_query($usersQry);
        
        //loop through users
        if ($usersRS){
        	while ($row = mysql_fetch_array($usersRS)){
	        	
        		$detailsRS = get_user_specific_values($row['userID'], array(0=>"First Name", 1=>"Last Name"));
        		while($details = mysql_fetch_array($detailsRS)){
        			if($details['fieldLabel'] == "First Name"){$firstName = $details['value'];}
        			if($details['fieldLabel'] == "Last Name"){$lastName = $details['value'];}
        		}
        		
	        	
	             $points = $jobManager->get_points_sum($row['jobID'], $row['userID']);
   
	        	print "<tr>
        			<td><div class=\"imgWrap\"><a href=\"../applications-detail?job=".$row['jobID']."&applicant=".$row['userID']."\"><img src=\"/themes/Intervue/img/profilePicExample.jpg\" alt=\"Full Name\" /></a></div><a href=\"../applications-detail?job=".$row['jobID']."&applicant=".$row['userID']."\"><strong>".$firstName." ".$lastName."</strong></a></td>
        			<td><h2>".$points."<br /><a href=\"../applications-detail?job=".$row['jobID']."&applicant=".$row['userID']."\">Rating Details</a></h2></td>
        			<td><a href=\"#\" class=\"btn green\">Recommend</a></td>
        		</tr>";
	        	
        	}	        
	        
        }else{
	 	print "<tr><td colspan=\"3\">No applicants at this time.</td></tr>";
	        
        } 
        
        ?>
        
        	
    </table>
    
    <div class="pagination">
        <a href="#">Prev</a> // <a href="#">Next</a>
    </div>

</section>