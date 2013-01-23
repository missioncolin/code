<?php
global $user, $quipp;
require_once dirname(dirname(__DIR__)) . '/jobs-manager/JobManager.php';
require_once dirname(__DIR__) . '/Questionnaire.php';

$post   = array();

if (!isset($j) || !$j INSTANCEOF JobManager){
    $j = new JobManager($db, $_SESSION['userID']);
}

list($title, $link, $dateExpires, $datePosted, $questionnaireID, $status, $companyID) = $j->getJob($_GET['job']);

if (time() < strtotime($datePosted) || $status == 'inactive') {
    $quipp->js['onload'] .= 'alertBox("fail", "No job found");';

} elseif (time() > strtotime($dateExpires)) {
    $quipp->js['onload'] .= 'alertBox("fail", "We\'re sorry, this job posting has expired");';

} else { 
    
    $provs  = $db->query("SELECT `itemID`, `provName` FROM `sysProvince` WHERE countryID IN (38, 213) ORDER BY `countryID`, `provName`");
    $provList = array();
    $countries = array(38 => "CAN", 213 => "USA");
    if ($db->valid($provs)){
        while ($row = $db->fetch_assoc($provs)){
            $provList[$row["itemID"]] = trim($row["provName"]);
        }
    }
    
    $cmpnyImg   = $user->get_meta('Company Logo', $companyID);
    $profileImg = (!empty($cmpnyImg)) ? "/uploads/profiles/".$companyID."/".$cmpnyImg : "http://www.gravatar.com/avatar/305b241d5a8fab92c5f2984f51c155ba?d=http%3A%2F%2Flocalhost%2Fthemes%2FIntervue%2Fimg%2FprofilePicExample.jpg&s=126";
    
    $industry   = $user->get_meta('Industry', $companyID);
    $bio        = $user->get_meta('Company Bio', $companyID);
    $website    = $user->get_meta('Website or Blog URL', $companyID);
    $fb         = $user->get_meta('Facebook Username', $companyID);
    $linkedIn   = $user->get_meta('LinkedIn Username', $companyID);
    $twitter    = $user->get_meta('Twitter Username', $companyID);
    $founded    = $user->get_meta('Year Founded', $companyID);
    $size       = $user->get_meta('Company Size', $companyID);
?>

<ul id="steps">
<li class="current"><span>1</span>Contact Information, Resume, and Cover</li>
<li><span>2</span>Interview Questions</li>
<li><span>3</span>Submit Application</li>
</ul>

<section id="applicantProfile" class="apply">
        <?php include __DIR__ . '/render.php'; ?>
</section>
<?php
}
