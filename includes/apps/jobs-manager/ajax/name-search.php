<?php 
require '../../../init.php';
require dirname(__DIR__) . '/JobManager.php';


//values
$searchVal = $_GET['searchKeyword'];
$userID = $_GET['userID'];
$jobID = $_GET['jobID'];


$j = new JobManager($db, $userID);

$display = 2;
$page = 1;

if (isset($_GET['page'])) {
    $page   = (int) $_GET['page'];
    $offset = ($page - 1) * $display;
}

$applicants = $j->getApplicants((int)$jobID, $offset, $display);
$nameMatches = array();

if (!empty($applicants)) {
	$nameMatches = $j->getNameMatches($searchVal, $jobID, $offset, $display);
	?>

	<table>
	<?php

        	if (!empty($nameMatches)) {

	                	    
	        	foreach ($applicants as $a) {   
		 	    
		 	    if (in_array($a['userID'], $nameMatches)) {     
		            
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