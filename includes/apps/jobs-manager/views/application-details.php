
<?php

global $quipp;

require dirname(__DIR__) . '/JobManager.php';


$j = new JobManager($db, $_SESSION['userID']);

$application = $j->getApplication($_GET['application']);

if (!$j->canEdit($application['jobID']) && $_SESSION['userID'] != $application['userID']) {
    $quipp->js['onload'] .= "alertBox('fail', 'You do not have access to view this application');";
    echo '<ul id="steps"></ul>';

} else {
    $applicant = new User($db, $application['userID']);

    $filteredList = array();

    /* Retrieve filtered data */
    if (isset($_SESSION['filterList'])) {
        $filteredList = unserialize($_SESSION['filterList']);
    }

    $currentID = array_search($_GET['application'], $filteredList);

    /* Select out this applicant's id from filtered list and determine index */
    if ($currentID !== false) {

        $currentID = array_search($_GET['application'], $filteredList);

        if ($currentID >= 1 && isset($filteredList[$currentID - 1])) {
            $prev = $currentID - 1;
        }
        else {
            $prev = '';
        }

        if (isset($filteredList[$currentID + 1])) {
            $next = $currentID + 1;

        }
        else {
            $next = '';
        }


    }

    $links = $j->getResumeCoverLetter($application['jobID'], $application['userID']);
    $resumeLink = "";
    $coverLink = "";

    if ($links != false){
        if (isset($links[-1])){
            $resumeLink = "<a href=\"/uploads/applications/" . $application['jobID']. "/" . $application['userID']. "/" . $links[-1]."\" class=\"grade btn black\"><img src=\"/themes/Intervue/img/resumeIcon.png\" alt=\"\" />Resume</a>";
        }
        if (isset($links[0])){
            $coverLink = "<a href=\"/uploads/applications/" . $application['jobID']. "/" . $application['userID']. "/" . $links[0]."\" class=\"grade btn black\"><img src=\"/themes/Intervue/img/coverLetterIcon.png\" alt=\"\" />Cover Letter</a>";
        }
    }


    $quipp->js['footer'][] = "/includes/apps/jobs-manager/js/jobs-manager.js";
?>

<div id="toolbar">

    <?php
    $applicants = $j->getApplicants($application['jobID']);

    //Get user grade

    if ($application['grade'] == "recommend"){
        $recommendColour = "green";
        $averageColour = "black";
    }else if ($application['grade'] == "average"){
            $recommendColour = "black";
            $averageColour = "yellow";
        }else {
        $recommendColour = "black";
        $averageColour = "black";
    }


    /*
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
*/



?>

    <span class="left">
        <?php if (isset($prev) && $prev !== '') { ?><a href="/applications-detail?application=<?php echo $filteredList[$prev]; ?>">&larr; Prev</a><?php } ?>
    </span>
        <h4><a href="applicant-list?job=<?php echo $application['jobID']; ?>&backToList=1">Back to List</a></h4>
    <span class="right">
        <?php if (isset($next) && $next !== '') { ?><a href="/applications-detail?application=<?php echo $filteredList[$next]; ?>">Next &rarr;</a><?php } ?>
    </span>
</div>

<section id="applicantProfile">

    <div id="card" class="box">
        <div class="heading">
            <h2><?php echo $applicant->info['First Name']." " . $applicant->info['Last Name'];?></h2>
            <a href="mailto:<?php echo $applicant->info['Email']; ?>" class="btn"><img src="/themes/Intervue/img/contactIcon.png" alt="" /></a>
        </div>
        <div class="cutout">
            <div class="profilePic"><div class="imgWrapper"><img src="<?php print $j->getApplicantAvatarSrc($application['userID']); ?>" alt="" /></div></div>
        </div>
        <dl>
            <dt>Name</dt>
            <dd><?php echo $applicant->info['First Name']." " . $applicant->info['Last Name'];?></dd>
            <dt>Email</dt>
            <dd><?php echo $applicant->info['Email'];?></dd>
            <?php echo isset($applicant->info['Company Address']) ? '<dt>Address</dt><dd>' . $applicant->info['Company Address'] . '</dd>' : '';?>
            <?php echo isset($applicant->info['Company City']) ? '<dt>City</dt><dd>' . $applicant->info['Company City'] . '</dd>' : '';?>
            <?php echo isset($applicant->info['Phone Number']) ? '<dt>Phone</dt><dd>' . $applicant->info['Phone Number'] . '</dd>' : '';?>
            <dt>Links</dt>
            <dd id="links">
            <?php
    $suppliedLinks = false;
    //website
    if (isset($applicant->info['Website or Blog URL']) && (strlen($applicant->info['Website or Blog URL']) > 0)){
        echo '<a class="icon blog" target="_blank" href="' . $applicant->info['Website or Blog URL'] . '">Website or Blog</a> ';
        $suppliedLinks = true;
    }
    //facebook
    if (isset($applicant->info['Facebook Username']) && (strlen($applicant->info['Facebook Username']) > 0)){
        echo '<a class="icon facebook" target="_blank"  href="http://www.facebook.com/' . $applicant->info['Facebook Username'] . '">Facebook</a> ';
        $suppliedLinks = true;
    }
    //twitter
    if (isset($applicant->info['Twitter Username']) && (strlen($applicant->info['Twitter Username']) > 0)){
        echo '<a class="icon twitter" target="_blank"  href="http://twitter.com/' . $applicant->info['Twitter Username'] . '">Twitter</a> ';
        $suppliedLinks = true;
    }
    //linkedin
    if (isset($applicant->info['LinkedIn Username']) && (strlen($applicant->info['LinkedIn Username']) > 0)){
        echo '<a class="icon linkedIn" target="_blank"  href="http://www.linkedin.com/in/' . $applicant->info['LinkedIn Username'] . '">LinkedIn</a> ';
        $suppliedLinks = true;
    }

    if ($suppliedLinks == false) {
        echo 'No links supplied';
    }
?>
            </dd>
        </dl>
        <div id="grade">
            <a href="#" data-application="<?php echo $_GET['application']; ?>" data-grade="recommend" class="grade btn <?php echo $recommendColour; ?>">Top Candidate</a>
            <a href="#" data-application="<?php echo $_GET['application']; ?>" data-grade="average" class="grade btn <?php echo $averageColour; ?>">Has Potential</a>
        </div>
    </div>

    <div id="work-experience" class="box">
        <div class="heading">
            <h2>Work Experience</h2>
        </div>

        <dl>
        <!------------ ***** THIS IS THE NEW DATA ADDED, TO BE STYLED ****** ----------------->
        <!---- get years of exp questions and answers in one area ---->

			<dt><b>Skill</b></dt><dd><b>Years of Experience</b></dd>
			<?php foreach ($j->getYearsOfExperienceQuestions($application['jobID']) as $id=>$label) { ?>

				<?php echo "<dt>" . $label . "</dt>"; ?>
	        	<?php echo "<dd>" . $j->getYearsofExperienceAnswers($application['userID'], $application['jobID'], $id); ?> years</dd>

			<?php } ?>
       </dl>
       <div id="resumeCoverLetter">
            <?php
    echo $resumeLink;
    echo $coverLink;
?>
        </div>
	</div>

    <div id="submissions">

        <h2>Intervue Answers</h2>

        <?php

    include dirname(dirname(__DIR__)) . '/questionnaires/views/renderVideoAnswers.php';
?>

    </div>

</section>
<?php } ?>