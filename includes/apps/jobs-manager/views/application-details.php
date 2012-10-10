<?php 

global $quipp;
    
require dirname(__DIR__) . '/JobManager.php';


$j = new JobManager($db, $_SESSION['userID']);

$application = $j->getApplication($_GET['application']);
$applicant = new User($db, $application['userID']);

$quipp->js['footer'][] = "/includes/apps/jobs-manager/js/jobs-manager.js";

?>


<section id="applicantProfile">

    <div id="card" class="box">
        <div class="heading">
            <h2><?php echo $application['rating'];?><br /><a href="#">Rating Details</a></h2>
            <a href="mailto:<?php echo $applicant->info['Email']; ?>" class="btn">Contact Applicant</a>
        </div>
        <div class="cutout">
            <div class="profilePic"><img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($applicant->info['Email']))); ?>?d=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/themes/Intervue/img/profilePicExample.jpg'); ?>&s=126" alt="" /></div>
        </div>
        <dl>
            <dt>Name</dt>
            <dd><?php echo $applicant->info['First Name']." " . $applicant->info['Last Name'];?></dd>
            <dt>Email</dt>
            <dd><?php echo $applicant->info['Email'];?></dd>
            <dt>Links</dt>
            <dd id="links">
            <?php
                $suppliedLinks = false;
                //website
                if (isset($applicant->info['Website or Blog URL']) && (strlen($applicant->info['Website or Blog URL']) > 0)){
                    echo '<a class="icon blog" href="' . $applicant->info['Website or Blog URL'] . '">Website or Blog</a> ';
                    $suppliedLinks = true;
                }
                //facebook
                if (isset($applicant->info['Facebook Username']) && (strlen($applicant->info['Facebook Username']) > 0)){
                    echo '<a class="icon facebook" href="http://www.facebook.com/' . $applicant->info['Facebook Username'] . '">Facebook</a> ';
                    $suppliedLinks = true;
                }
                //twitter
                if (isset($applicant->info['Twitter Username']) && (strlen($applicant->info['Twitter Username']) > 0)){
                    echo '<a class="icon twitter" href="http://twitter.com/' . $applicant->info['Twitter Username'] . '">Twitter</a> ';
                    $suppliedLinks = true;
                }
                //linkedin
                if (isset($applicant->info['LinkedIn Username']) && (strlen($applicant->info['LinkedIn Username']) > 0)){
                    echo '<a class="icon linkedin" href="http://www.linkedin.com/in/' . $applicant->info['LinkedIn Username'] . '">LinkedIn</a> ';
                    $suppliedLinks = true;
                }
                
                if ($suppliedLinks == false) {
                    echo 'No links supplied';
                }
            ?>
            </dd>
        </dl>
        <div id="grade">
            <h3>Grade Applicant</h3>
            <a href="#" data-application="<?php echo $_GET['application']; ?>" data-grade="recommend" class="grade btn <?php echo ($application['grade'] == 'recommend') ? 'green' : 'black'; ?>">Recommend</a>
            <a href="#" data-application="<?php echo $_GET['application']; ?>" data-grade="average" class="grade btn <?php echo ($application['grade'] == 'average') ? 'yellow' : 'black'; ?>">Average</a>
            <a href="#" data-application="<?php echo $_GET['application']; ?>" data-grade="nq" class="grade btn <?php echo ($application['grade'] == 'nq') ? 'red' : 'black'; ?>">NQ</a>
        </div>
    </div>
    
    <div id="submissions">
        <div id="toolbar">
            <a class="left btn2" href="applicant-list?job=<?php echo $application['jobID']; ?>">Back to List</a>
            <h4><span>Reviewing: </span><?php echo $applicant->info['First Name']." " . $applicant->info['Last Name'];?></h4>
            <span class="right">
                
                <?php
                $applicants = $j->getApplicants($application['jobID']);
                
                $keys    = array_keys($applicants);
                $current = array_search($application['userID'], $keys);
                
                $prev = '';
                $next = '';
                if (isset($keys[$current - 1])) {
                    $prev = $applicants[$keys[$current - 1]];
                }
                if (isset($keys[$current + 1])) {
                    $next = $applicants[$keys[$current + 1]];
                }
                            
                ?>
                <?php if ($prev != '') { ?><a href="/applications-detail?application=<?php echo $prev['itemID']; ?>" class="btn2 green">Prev</a><?php } if ($prev != '' && $next != '') { ?><?php } if ($next != '') { ?> <a href="/applications-detail?application=<?php echo $next['itemID']; ?>" class="btn2 green">Next</a><?php } ?>
            </span>
        </div>
        
        
        <?php

        include dirname(dirname(__DIR__)) . '/questionnaires/views/renderAnswers.php';
        ?>

    </div>
    
</section>