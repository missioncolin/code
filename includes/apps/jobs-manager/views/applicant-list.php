<?php 

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
        $usersQry = sprintf("SELECT userID AS 'userID', jobID AS 'jobID', SUM(value) AS 'points', questionID AS 'questionID', optionID AS 'optionID' 
        			FROM tblAnswers 
        			WHERE  jobID = '%d' AND sysActive = '1' and sysOpen = '1'
        			GROUP BY userID, jobID", $_GET['job']);
        $usersRS = mysql_query($usersQry);
        
        //loop through users
        if ($usersRS){
        	while ($row = mysql_fetch_array($usersRS)){
	        	
	        	$points = $row['points'];

	        	//get value of options from tblOptions where answrs.value = none and option id > 0	      
	        	$radioQry = sprintf("SELECT SUM( options.value ) AS  'value'
	        		FROM tblAnswers answers
				INNER JOIN tblOptions options ON answers.optionID = options.itemID
				WHERE answers.jobID = %d
				AND answers.userID = %d
				GROUP BY answers.userID
				LIMIT 0 , 30", $row['jobID'], $row['userID']);

	        	$radioRS = mysql_query($radioQry);
	        	if($radioRS){
		        	$valueRow = mysql_fetch_array($radioRS);
		        	$points += $valueRow['value'];
	        	}
	        
			
	        	//multi select qry
	        	$multiQry = sprintf("SELECT sum(options.value) AS 'value' FROM tblOptions options 
	        	INNER JOIN tblAnswerOptionsLinks links ON options.itemID = links.optionID
	        	WHERE links.jobID = %d AND links.applicantID = %d
	        	GROUP BY applicantID, jobID", $row['jobID'], $row['userID']);
	        	
	        	$multiRS = mysql_query($multiQry);
	        	if ($multiRS){
		        	$valueRow = mysql_fetch_array($multiRS);
		        	$points += $valueRow['value'];	
	        	}
	        	
	        	
        		$detailsRS = get_user_specific_values($row['userID'], array(0=>"First Name", 1=>"Last Name"));
        		while($details = mysql_fetch_array($detailsRS)){
	        		if($details['fieldLabel'] == "First Name"){$firstName = $details['value'];}
	        		if($details['fieldLabel'] == "Last Name"){$lastName = $details['value'];}
	        		
        		}
   
	        	print "<tr>
        			<td><div class=\"imgWrap\"><img src=\"/themes/Intervue/img/profilePicExample.jpg\" alt=\"Full Name\" /></div><strong>".$firstName." ".$lastName."</strong></td>
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