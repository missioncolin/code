<?php 
	require dirname(__DIR__) . '/JobManager.php';


	$j = new JobManager($db, $_SESSION['userID']);

	$application = $j->getApplication($_GET['application']);
	$applicant = new User($db, $application['userID']);



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
            <?php
            	//website
            	if (isset($applicant->info['Website or Blog URL'])&&(strlen($applicant->info['Website or Blog URL']) > 0)){
            		print "<dt>Website or Blog</dt><dd><a href=\"".$applicant->info['Website or Blog URL']."\">".$applicant->info['Website or Blog URL']."</a></dd>";
            	}
            	//facebook
              if (isset($applicant->info['Facebook Username'])&&(strlen($applicant->info['Facebook Username']) > 0)){
            		print "<dt>Facebook</dt><dd><a href=\"".$applicant->info['Facebook Username']."\">".$applicant->info['Facebook Username']."</a></dd>";
            	}
            	//twitter
            	if (isset($applicant->info['Twitter Username'])&&(strlen($applicant->info['Twitter Username']) > 0)){
            		print "<dt>Twitter</dt><dd><a href=\"".$applicant->info['Twitter Username']."\">".$applicant->info['Twitter Username']."</a></dd>";
            	}
            	//linkedin
            	if (isset($applicant->info['LinkedIn Username'])&&(strlen($applicant->info['LinkedIn Username']) > 0)){
            		print "<dt>LinkedIn</dt><dd><a href=\"".$applicant->info['LinkedIn Username']."\">".$applicant->info['LinkedIn Username']."</a></dd>";
            	}
            ?>
        
        </dl>
        <div id="grade">
            <h3>Grade Applicant</h3>
            <a href="#" class="btn <?php echo ($application['grade'] == 'recommend') ? 'green' : 'black'; ?>">Recommend</a>
            <a href="#" class="btn <?php echo ($application['grade'] == 'average') ? 'green' : 'black'; ?>">Average</a>
            <a href="#" class="btn <?php echo ($application['grade'] == 'nq') ? 'green' : 'black'; ?>">NQ</a>
        </div>
    </div>
    
    <div id="submissions">
        <div id="toolbar">
            <a class="left" href="applicant-list?job=<?php echo $application['jobID']; ?>">Back to List</a>
            <h4><span>Reviewing: </span><?php echo $applicant->info['First Name']." " . $applicant->info['Last Name'];?></h4>
            <span class="right">
                <a href="#">Prev</a> // <a href="#">Next</a>
            </span>
        </div>
        
        
        <?php

        include dirname(dirname(__DIR__)) . '/questionnaires/views/renderAnswers.php';
        ?>

    </div>
    
</section>