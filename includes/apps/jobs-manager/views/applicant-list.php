<?php

require dirname(__DIR__) . '/JobManager.php';
$j = new JobManager($db, $_SESSION['userID']);

$offset  = 0;
$page    = 1;
$display = 10;

if (isset($_GET['page'])) {
    $page   = (int) $_GET['page'];
    $offset = ($page - 1) * $display;
}


$applicants = $j->getApplicants($_GET['job'], $offset, $page, $display);
$total      = $j->totalJobs($_GET['job']);

?>


<section id="applicantList">

    <table>
        <tr>
            <th>Applicant Details</th>
            <th>Intervue Rating</th>
            <th>Applicant Grade</th>
        </tr>
        <?php
        
        
    if (!empty($applicants)) {
        foreach ($applicants as $a) {        
            $applicant = new User($db, $a['userID']);
            ?>
            <tr>
    			<td><div class="imgWrap"><img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($applicant->info['Email']))); ?>?d=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/themes/Intervue/img/profilePicExample.jpg'); ?>&s=83" alt="<?php echo $applicant->info['First Name'] . " " . $applicant->info['Last Name']; ?>" /></div><strong><?php echo $applicant->info['First Name'] . " " . $applicant->info['Last Name']; ?></strong></td>
    			<td>
        			<h2><?php echo $j->getApplicantRating($a['jobID'], $a['userID']); ?><br />
        			<a href="/applications-detail?job=<?php echo $a['jobID']; ?>&applicant=<?php echo $a['userID']; ?>">Rating Details</a>
        			</h2>
                </td>
    			<td><a href="#" class="btn green">Recommend</a></td>
    		</tr>
    		<?php

        }
    } else {
        ?><tr><td colspan="3">No applicants at this time.</td></tr><?php
    }

?>
    </table>

    <div class="pagination">
        <?php echo pagination($total, $display, $page, '/applicant-list?job=' . (int)$_GET['job'] . '&amp;page=', false); ?>
    </div>

</section>