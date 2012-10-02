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


$applicants = $j->getApplicants($_GET['job'], $offset, $display);
$total      = $j->totalApplicants($_GET['job']);

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
            
            $colours = array(
                'recommend' => 'green',
                'average'   => 'yellow',
                'nq'        => 'red'
            );
            
            $class = $colours[$a['grade']];
            ?>
            <tr>
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
    } else {
        ?><tr><td colspan="3">No applicants at this time.</td></tr><?php
    }

?>
    </table>

    <div class="pagination">
        <?php echo pagination($total, $display, $page, '/applicant-list?job=' . (int)$_GET['job'] . '&amp;page=', false); ?>
    </div>

</section>