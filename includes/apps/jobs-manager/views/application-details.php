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
            <div class="profilePic"><img src="/themes/Intervue/img/profilePicExample.jpg" alt="" /></div>
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
        <div id="videoResponses">
            <h3>Video Responses</h3>
            <ul>
                <li>
                    <div class="imgWrap">
                        <a href="#" class="icon video">Video</a>
                        <img src="/themes/Intervue/img/vidSubmissionExample.jpg" alt="" />
                    </div>
                    <h4>Name of Question</h4>    
                </li>
                <li>
                    <div class="imgWrap">
                        <a href="#" class="icon video">Video</a>
                        <img src="/themes/Intervue/img/vidSubmissionExample.jpg" alt="" />
                    </div>
                    <h4>Name of Question</h4>    
                </li>
                <li>
                    <div class="imgWrap">
                        <a href="#" class="icon video">Video</a>
                        <img src="/themes/Intervue/img/vidSubmissionExample.jpg" alt="" />
                    </div>
                    <h4>Name of Question</h4>    
                </li>
                <li>
                    <div class="imgWrap">
                        <a href="#" class="icon video">Video</a>
                        <img src="/themes/Intervue/img/vidSubmissionExample.jpg" alt="" />
                    </div>
                    <h4>Name of Question</h4>    
                </li>
                <li>
                    <div class="imgWrap">
                        <a href="#" class="icon video">Video</a>
                        <img src="/themes/Intervue/img/vidSubmissionExample.jpg" alt="" />
                    </div>
                    <h4>Name of Question</h4>    
                </li>
            </ul>
        </div>
        <div id="resumeCoverLetter">
            <h3>Resume/Cover Letter</h3>
            <ul>
                <li>
                    <div class="imgWrap">
                        <img src="/themes/Intervue/img/resumeExample.jpg" alt="" />
                    </div>
                    <h4>Resume</h4>    
                </li>
                <li>
                    <div class="imgWrap">
                        <img src="/themes/Intervue/img/resumeExample.jpg" alt="" />
                    </div>
                    <h4>Cover Letter</h4>    
                </li>
            </ul>
        </div>
    </div>
    
</section>