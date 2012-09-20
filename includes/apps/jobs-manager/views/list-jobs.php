<?php

global $quipp;

require dirname(__DIR__) . '/JobManager.php';

$j = new JobManager($db, $_SESSION['userID']);

$offset  = 0;
$page    = 1;
$display = 3;

if (isset($_GET['page'])) {
    $page   = (int) $_GET['page'];
    $offset = ($page - 1) * $display;
}

$jobs  = $j->getJobs($offset, $page, $display);
$total = $j->totalJobs();

$quipp->js['footer'][] = "/includes/apps/jobs-manager/js/jobs-manager.js";



?>
<section id="hrListJobs">
    
    <a href="/create-job" class="btn green newJob">Add a New Job</a>
    
    <table>
        <tr>
            <th colspan="4">Job Title</th>
        </tr>
        <?php
        
        if (empty($jobs)) {
            echo '<tr><td colspan="4">No jobs found</td></tr>';
            
        } else {
            
            foreach ($jobs as $jobID => $job) {
                ?>
        <tr>
            <td><strong><a href="/applicant-list?job=<?php echo $jobID; ?>"><?php echo $job['title']; ?></a></strong><br /><?php echo $job['link']; ?></td>
            <td><a href="#" data-job="<?php echo $jobID; ?>" class="activate btn <?php echo ($job['sysStatus'] == 'active') ? 'black' : 'grey'; ?>"><?php echo ucfirst($job['sysStatus']); ?></a></td>
            <td><a href="#" data-job="<?php echo $jobID; ?>" class="btn red delete">Delete</a></td>
            <td><a href="/edit-job?id=<?php echo $jobID; ?>" class="btn">Edit</a></td>
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