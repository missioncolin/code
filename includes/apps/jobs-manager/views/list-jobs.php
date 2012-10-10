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
    $quipp->js['onload'] = 'alertBox("success", "Success! Your job was re-published and one (1) credit was debited");';
}


?>
<section id="hrListJobs">
    
    <a href="/create-job?step=1" class="btn green newJob">Add a New Job</a>
    <a href="/buy-job-credits" class="btn green buyCredits">Buy Job Credits</a>
    <table class="simpleTable jobTable">
        <tr>
            <th colspan="5">Job Title</th>
        </tr>
        <?php
        
        if (empty($jobs)) {
            echo '<tr><td colspan="5">No jobs found</td></tr>';
            
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
                <td width="20%"><strong><a href="/applicant-list?job=<?php echo $jobID; ?>"><?php echo $job['title']; ?></a></strong><?php echo $verbiage; ?></td>
                
<?php
                if (date("U") > strtotime($job["dateExpires"])){
?>
                    <td colspan="4"><a href="<?php echo ($user->info['Job Credits'] > 0 ? "" : "/buy-job-credits?req=reactivate+{$jobID}");?>" data-job="<?php echo $jobID; ?>" class="btn <?php echo ($user->info['Job Credits'] > 0 ? "green reactivate" : "red buy");?>">Re-Publish</a></td>
            <?php
                }
                else{
                //set button verbiage
                
                if(ucfirst($job['sysStatus']) == 'Active'){ $btnLabel = "Remove"; }else{ $btnLabel = "Publish";}
                
                ?>
            <td><?php echo $_SERVER['SERVER_NAME']."/apply/".$jobID;?></td>
            <td><a href="#" data-job="<?php echo $jobID; ?>" class="activate btn <?php echo ($job['sysStatus'] == 'active') ? 'black' : 'grey'; ?>"><?php echo $btnLabel; ?></a></td>
            <td><a href="#" data-job="<?php echo $jobID; ?>" class="btn red delete">Delete</a></td>
            <td><a href="/edit-job?id=<?php echo $jobID; ?>" class="btn">Edit</a></td>
                <?php
                }
                ?>
            </tr>
                <?php
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
<a class="btn" id="popUpOk">Ok</a>&nbsp;<a class="btn red" id="popUpNo">Cancel</a>
</div>
</div>