<?php
global $user, $quipp;
require_once dirname(dirname(__DIR__)) . '/jobs-manager/JobManager.php';
require_once dirname(__DIR__) . '/Questionnaire.php';

if (!isset($j) || !$j INSTANCEOF JobManager){
    $j = new JobManager($db, $_SESSION['userID']);
}

list($title, $link, $dateExpires, $datePosted, $questionnaireID, $status, $companyID) = $j->getJob($_GET['job']);

if (time() < strtotime($datePosted) || $status == 'inactive') {
    $quipp->js['onload'] .= 'alertBox("fail", "No job found");';

} elseif (time() > strtotime($dateExpires)) {
    $quipp->js['onload'] .= 'alertBox("fail", "We\'re sorry, this job posting has expred");';

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
<section id="applicantProfile" class="apply">

    <div id="card" class="box">
        <div class="heading">
            <h2><?php echo $user->get_meta('Company Name', $companyID);?></h2>
        </div>
        <div class="cutout">
            <div class="profilePic"><img src="<?php echo $profileImg;?>" alt="" /></div>
        </div>
        <dl>
            <dt>Location</dt>
            <dd><?php echo $user->get_meta('Company City', $companyID)." ".$provList[$user->get_meta('Company Province', $companyID)].", ".$countries[$user->get_meta('Company Country', $companyID)];?></dd>
        <?php
        if (!empty($industry)){
        ?>
            <dt>Industry</dt>
            <dd><?php echo $industry;?></dd>
        <?php
        }
        if (!empty($website) || !empty($fb) || !empty($linkedIn) || !empty($twitter)){
        ?>
            <dt>Links</dt>
            <dd><?php 
                if (!empty($website)){
                    echo '<a class="icon blog" href="' . $website . '">Website or Blog</a> ';
                }
                if (!empty($fb)){
                    echo '<a class="icon facebook" href="http://www.facebook.com/' . $fb . '">Facebook</a> ';
                }
                if (!empty($twitter)){
                    echo '<a class="icon twitter" href="http://twitter.com/' . $twitter . '">Twitter</a> ';
                }
                if (!empty($linkedIn)){
                    echo '<a class="icon linkedin" href="http://www.linkedin.com/in/' . $linkedIn . '">LinkedIn</a> ';
                }
               
            ?></dd>
        <?php
        }
        if (!empty($founded)){
        ?>
            <dt>Founded</dt>
            <dd><?php echo $founded;?></dd>
        <?php
        }
        if (!empty($size)){
        ?>
            <dt>Size</dt>
            <dd><?php echo $size;?></dd>
        <?php
        }
        ?>
        </dl>
        <?php
        if (!empty($bio)){
        ?>
            <p style="margin-left:100px;width:70%"><span style="color:#cfcfcf">Bio</span><br />
            <?php echo $bio;?>
            </p>
        <?php
        }
        ?>
    </div>
    
    <div id="submissions">
        
        <?php include __DIR__ . '/render.php'; ?>
        
    </div>

</section>
<?php
}