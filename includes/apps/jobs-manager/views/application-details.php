<?php 
	require dirname(__DIR__) . '/JobManager.php';

	$applicantRS = new User($db, $_GET['applicant']);
	$applicant = $applicantRS->info;

	$jobManager = new JobManager($db, $_SESSION['userID']);
	$points = $jobManager->getApplicantRating($_GET['job'], $_GET['applicant']);
?>


<section id="applicantProfile">

    <div id="card" class="box">
        <div class="heading">
            <h2><?php echo $points;?><br /><a href="#">Rating Details</a></h2>
            <a href="mailto:<?php echo $applicant['Email']; ?>" class="btn">Contact Applicant</a>
        </div>
        <div class="cutout">
            <div class="profilePic"><img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($applicant['Email']))); ?>?d=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/themes/Intervue/img/profilePicExample.jpg'); ?>&s=83" alt="" /></div>
        </div>
        <dl>
            <dt>Name</dt>
            <dd><?php echo $applicant['First Name']." " . $applicant['Last Name'];?></dd>
            <dt>Email</dt>
            <dd><?php echo $applicant['Email'];?></dd>   
            <?php
            	//website
            	if (isset($applicant['Website or Blog URL'])&&(strlen($applicant['Website or Blog URL']) > 0)){
            		print "<dt>Website or Blog</dt><dd><a href=\"".$applicant['Website or Blog URL']."\">".$applicant['Website or Blog URL']."</a></dd>";
            	}
            	//facebook
              if (isset($applicant['Facebook Username'])&&(strlen($applicant['Facebook Username']) > 0)){
            		print "<dt>Facebook</dt><dd><a href=\"".$applicant['Facebook Username']."\">".$applicant['Facebook Username']."</a></dd>";
            	}
            	//twitter
            	if (isset($applicant['Twitter Username'])&&(strlen($applicant['Twitter Username']) > 0)){
            		print "<dt>Twitter</dt><dd><a href=\"".$applicant['Twitter Username']."\">".$applicant['Twitter Username']."</a></dd>";
            	}
            	//linkedin
            	if (isset($applicant['LinkedIn Username'])&&(strlen($applicant['LinkedIn Username']) > 0)){
            		print "<dt>LinkedIn</dt><dd><a href=\"".$applicant['LinkedIn Username']."\">".$applicant['LinkedIn Username']."</a></dd>";
            	}
            ?>
        
        </dl>
        <div id="grade">
            <h3>Grade Applicant</h3>
            <a href="#" class="btn green">Recommend</a>
            <a href="#" class="btn black">Average</a>
            <a href="#" class="btn black">NQ</a>
        </div>
    </div>
    
    <div id="submissions">
        <div id="toolbar">
            <a class="left" href="applicant-list?job=<?php echo $_GET['job']; ?>">Back to List</a>
            <h4><span>Reviewing: </span><?php echo $applicant['First Name']." " . $applicant['Last Name'];?></h4>
            <span class="right">
                <a href="#">Prev</a> // <a href="#">Next</a>
            </span>
        </div>
        
        
        <?php

        include dirname(dirname(__DIR__)) . '/questionnaires/views/renderAnswers.php';
        ?>

    </div>
    
</section>