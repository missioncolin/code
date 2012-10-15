<?php

global $quipp;
global $user;

if (!class_exists('JobManager')) {
    require dirname(__DIR__) . '/JobManager.php';
}

$j = new JobManager($db, $_SESSION['userID']);

$offset  = 0;
$page    = 1;
$display = 3;

$jobs  = $j->getJobs($offset, $page, $display);
$total = $j->totalJobs();

$quipp->js['footer'][] = "/includes/apps/jobs-manager/js/jobs-manager.js";



?>
<section id="hrListJobs">
    <div id="card" class="profileBox smallHeader"> 
	    <div class="heading">
	        <h2>My Jobs</h2>
	    </div>
	    <table class="simpleTable jobTable">
	        <?php
	        
	        if (empty($jobs)) {
	            echo '<tr><td colspan="2">No jobs found</td></tr>';
	            
	        } else {
	            
	            foreach ($jobs as $jobID => $job) {
	                
	                $totalApplicants = $j->totalApplicants($job['itemID']);
	                
	                if ($totalApplicants == 0) {
	                    $verbiage = "<br />There are no applicants";
	                } elseif ($totalApplicants == 1) {
	                    $verbiage = "<br /><a href=\"/applicant-list?job={$jobID}\">View {$totalApplicants} applicant</a>";
	                } else {
	                    $verbiage = "<br /><a href=\"/applicant-list?job={$jobID}\">View {$totalApplicants} applicants</a>";
	
	                }
	                
	                ?>
	            <tr>
	                <td><strong><a href="/applicant-list?job=<?php echo $jobID; ?>"><?php echo $job['title']; ?></a></strong><?php echo $verbiage; ?></td>
	                
	            </tr>
	
	                <?php
	                
	                //set hasBeenViewed to 1 if(hasBeenViewed == 0)
	                if ($job['hasBeenViewed'] == 0){
		                $j->setJobViewed($jobID);
	                }
	                
	                
	            }
	            
	        }
	        ?>
	    <tr>
	    	<td><a href="/applications">View All</a></td>
	    </tr>
	    </table>
    </div>
</section>


