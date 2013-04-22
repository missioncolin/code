<?php

global $quipp;
global $user;

require dirname(__DIR__) . '/JobManager.php';

$j = new JobManager($db, $_SESSION['userID']);

$offset  = 0;
$page    = 1;
$display = 10;

if (isset($_GET['page'])) {
    $page   = (int) $_GET['page'];
    $offset = ($page - 1) * $display;
}

$jobs  = $j->getJobs($offset, $page, $display);
$total = $j->totalJobs();

$quipp->js['footer'][] = "/includes/apps/jobs-manager/js/jobs-manager.js";

if (isset($_GET['req']) && preg_match('%^reactivate[\s\+](\d+)$%', $_GET['req'], $matches)){
    $quipp->js['onload'] = 'alertBox("success", "Success! Your joblink was re-activated and (1) credit was debited");';
}


?>

<div class="pagination top">
        <?php echo pagination($total, $display, $page, '/applications?page=', false); ?>
 </div>

<a href="/create-job?step=1" class="btn green newJob" style="float:right; margin-bottom:10px;">Create a New Job</a>
<a href="/buy-job-credits" class="btn green buyCredits" style="float:right; margin: 0px 10px 10px 10px;">Buy Job Credits</a>

<section id="hrListJobs">
    <table class="simpleTable jobTable">
        <tr>
            <th>Job Title</th>
            <th>Link</th>
            <th>Expiry date</th>
            <th></th>
            <th></th>
        </tr>
        <?php
        
        if (empty($jobs)) {
            echo '<tr><td colspan="5">No jobs found</td></tr>';
            
        } else {
            
            foreach ($jobs as $jobID => $job) {
                
                $totalApplicants = $j->totalApplicants($job['itemID']);
               // $qName = $j->getQuestionnaireName($job['itemID']);
                
                if ($totalApplicants == 0) {
                    $verbiage = "<br />There are no applicants";
                } elseif ($totalApplicants == 1) {
                    $verbiage = "<br /><a href=\"/applicant-list?job={$jobID}\">View {$totalApplicants} applicant</a>";
                } else {
                    $verbiage = "<br /><a href=\"/applicant-list?job={$jobID}\">View {$totalApplicants} applicants</a>";

                }
                
?>
            <tr>
                <td>
                	<strong><a href="/applicant-list?job=<?php echo $jobID; ?>"><?php echo $job['title']; ?></a></strong>
                	<?php echo $verbiage; ?>
                </td>

<?php
	       
                if (date("U") > strtotime($job["dateExpires"])){
?>	
		      <td></td>
                    <td>
                    		<a href="<?php echo ($user->info['Job Credits'] > 0 ? "" : "/buy-job-credits?req=reactivate+{$jobID}");?>" data-job="<?php echo $jobID; ?>" data-expiry="<?php echo $job["dateExpires"]; ?>" class="btn <?php echo ($user->info['Job Credits'] > 0 ? "green reactivate" : "red buy");?>">Expired - Reactivate</a>
                    </td>
                    <td></td>
                    <td></td>
<?php
                }else{
                	//set button verbiage
               	 if(ucfirst($job['sysStatus']) == 'Active'){ $btnLabel = "Live - Un-Publish"; }else{ $btnLabel = "Not Live - Activate Link";}              
?>
            		<td>
<?php 
						$printClass = '';
	            	  	if ($job['hasBeenViewed'] == 0){
	            	  		$printClass = " newJobAlert";
	            	  	}
	            	  	if (ucfirst($job['sysStatus']) == 'Active'){  
	            	  		echo "<span class=\"boldLink".$printClass."\">http://".$_SERVER['SERVER_NAME']."/apply/".$jobID."</span>"; 
	            	  	} 
	            	  	else { echo "<span class=\"disabledLink\">(You must publish this job to use a link)</span>"; } ?>
	            	</td>

	            	<td>
		            	<?php 
		            	if(ucfirst($job['sysStatus']) == 'Active'){	            	
		       		print $job["dateExpires"];
		       	}else{
/* 		       		$link = ($user->info['Job Credits'] > 0) ? "" : "/buy-job-credits?req=activate+{$jobID}"; */
		       		//$color = ($job['sysStatus'] == 'active') ? 'black' : 'grey';
		       		//$class = ($user->info['Job Credits'] > 0) ? "green reactivate" : "red buy";
		       		print "<a href=\"#\" data-credits=\"".$user->info['Job Credits']."\" data-job=\"".$jobID."\" data-expiry=\"".$job["dateExpires"]."\" class=\"btn activateList green\">".$btnLabel."</a>";	

		       		//print "<a href=\"".$link."\" data-job=\"".$jobID."\" class=\"btn ".$class."\">Activate</a>";
		       	
		       	}
		       	?>

            		</td>
            		<td><a href="/edit-job?id=<?php echo $jobID; ?>" class="btn">Edit</a></td>
          	 	<td><a href="#" data-job="<?php echo $jobID; ?>" class="btn red delete">Delete</a></td>
                <?php
                }
                ?>
            </tr>
                <?php
                
                //set hasBeenViewed to 1 if(hasBeenViewed == 0)
                if ($job['hasBeenViewed'] == 0){
	                $j->setJobViewed($jobID);
                }
                
                
            }
            
        }
        ?>
    </table>
    
    <div class="pagination">
        <?php echo pagination($total, $display, $page, '/applications?page=', false); ?>
    </div>
    
</section>



<div id="confirm" style="display:none">
<div class="popUp">
<h2></h2>
<p></p>
<a class="btn green" id="popUpOk">Ok</a>&nbsp;<a class="btn red" id="popUpNo">Cancel</a>
</div>
</div>
