<?php

global $quipp;

require_once dirname(dirname(__DIR__)) . '/jobs-manager/JobManager.php';
require_once dirname(__DIR__) . '/Questionnaire.php';

if (!isset($j) || !$j INSTANCEOF JobManager){
    $j = new JobManager($db, $_SESSION['userID']);
}

list($title, $link, $dateExpires, $datePosted, $questionnaireID, $status, $companyID) = $j->getJob($_GET['job']);

if (time() < strtotime($datePosted) || $status == 'inactive') {
    $quipp->js['onload'] .= 'alertBox("fail", "No job found");';

} elseif (time() > strtotime($dateExpires)) {
    $quipp->js['onload'] .= 'alertBox("fail", "We\'re sorry, this job posting has expired");';

} elseif ($j->hasApplied($_GET['job'])) {
    
    if (isset($_GET['success'])) {
        $quipp->js['onload'] .= 'alertBox("success", "Thank you for applying. Your application has been received.");';
    } else {
        $quipp->js['onload'] .= 'alertBox("fail", "You have already applied");';
    }
    include __DIR__ . '/renderAnswers.php';

} else {
    $q = new Questionnaire($db, $questionnaireID);
    $quipp->js['footer'][] = "/includes/apps/questionnaires/js/questionnaires.js";
    
    $videos = "";
    $videoCount = 1;

    $MIME_TYPES = array(
        'image/jpeg'                    => 'jpg',
        'image/pjpeg'                   => 'jpg',
        'image/gif'                     => 'gif',
        'image/tiff'                    => 'tif',
        'image/x-tiff'                  => 'tif',
        'image/png'                     => 'png',
        'image/x-png'                   => 'png',
        'application/x-shockwave-flash' => 'swf',
        "application/pdf"            => "PDF",
        "text/plain"                 => "Plain text",
        "application/ms-word"        => "Microsoft Word",
        "application/msword"         => "Microsoft Word",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "Microsoft Word"

    );

    if (!empty($_POST)) {
    	
    	if (isset($_FILES['resume']) || isset($_FILES['coverLetter'])) {
	    	
	    	foreach ($_FILES as $f) {
		    	if ($f['error'] == 0) {
			    	
			    	if (!is_dir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job'] . '/' . (int) $_SESSION['userID'])) {
                        mkdir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job']);
                        mkdir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job'] . '/' . (int) $_SESSION['userID']);
                    }

                    $file = upload_file(0, dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job'] . '/' . (int) $_SESSION['userID'] . '/', $MIME_TYPES, false, false, false, base_convert(0, 10, 36));
                    if (substr($file, 0, 8) == '<strong>') {
                        $error = $file;
                    } else {

                        $qry = sprintf("INSERT INTO tblAnswers (applicationID, jobID, userID, questionID, optionID, value, sysDateInserted) VALUES ('%d', '%d', '%d', '%d', '%d', '%s', '%s') ON DUPLICATE KEY UPDATE value='%s', sysDateInserted='%s'",
                            $applicationID,
                            (int) $_GET['job'],
                            (int) $_SESSION['userID'],
                            (int) '0',
                            '',
                            $file,
                            date('Y-m-d H:i:s'),
                            $file,
                            date('Y-m-d H:i:s'));
                        $db->query($qry);
                    }
                    
		    	}
		    	
		    	else {
			    	echo "Error: ".$f['Name']." - ".$f['error']."</br>";
		    	}
	    	}
    	}
    	
        if (is_array($q->questions) && !empty($q->questions)) {

            $qry = sprintf("INSERT INTO tblApplications (jobID, userID, sysDateInserted) VALUES ('%d', '%d', NOW())",
                (int) $_GET['job'],
                (int) $_SESSION['userID']);
            $db->query($qry);
            $applicationID = $db->insert_id();

            foreach ($q->questions as $questionID => $question) {

                // radios
                if ($question['type'] == '1') {
	                
                    $qry = sprintf("INSERT INTO tblAnswers (applicationID, jobID, userID, questionID, optionID, value, sysDateInserted) VALUES ('%d', '%d', '%d', '%d', '%d', '%s', '%s') ON DUPLICATE KEY UPDATE optionID='%s', sysDateInserted='%s'",
                        $applicationID,
                        (int) $_GET['job'],
                        (int) $_SESSION['userID'],
                        (int) $questionID,
                        $db->escape((isset($_POST[$questionID]) && !is_array($_POST[$questionID])) ? $_POST[$questionID] : ''),
                        $db->escape((isset($_POST[$questionID]) && !is_array($_POST[$questionID])) ? $db->return_specific_item($_POST[$questionID], 'tblOptions', 'value', '') : ''),
                        date('Y-m-d H:i:s'),
                        $db->escape((isset($_POST[$questionID]) && !is_array($_POST[$questionID])) ? $_POST[$questionID] : ''),
                        date('Y-m-d H:i:s'));
                    $db->query($qry);

                } elseif ($question['type'] == '2') {     // checkboxes

                    $qry = sprintf("DELETE FROM tblAnswerOptionsLinks WHERE jobID='%d' AND applicantID='%d' AND questionID='%d'",
                        (int) $_GET['job'],
                        (int) $_SESSION['userID'],
                        (int) $questionID);
                    $db->query($qry);

                    if (isset($_POST[$questionID])) {
                        foreach ($_POST[$questionID] as $opt) {
                            // insert option answers
                            $qry = sprintf("INSERT INTO `tblAnswerOptionsLinks` (`applicationID`, `jobID`, `applicantID`, `questionID`, `optionID`) VALUES ('%d', '%d', '%d', '%d', '%d')",
                                $applicationID,
                                (int) $_GET['job'],
                                (int) $_SESSION['userID'],
                                (int) $questionID,
                                (int) $opt);
                            $db->query($qry);
                        }
                    }

                // file upload
                } elseif ($question['type'] == '5') {
                    if (!is_dir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job'] . '/' . (int) $_SESSION['userID'])) {
                        mkdir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job']);
                        mkdir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job'] . '/' . (int) $_SESSION['userID']);
                    }

                    $file = upload_file($questionID, dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job'] . '/' . (int) $_SESSION['userID'] . '/', $MIME_TYPES, false, false, false, base_convert($questionID, 10, 36));
                    if (substr($file, 0, 8) == '<strong>') {
                        $error = $file;
                    } else {

                        $qry = sprintf("INSERT INTO tblAnswers (applicationID, jobID, userID, questionID, optionID, value, sysDateInserted) VALUES ('%d', '%d', '%d', '%d', '%d', '%s', '%s') ON DUPLICATE KEY UPDATE value='%s', sysDateInserted='%s'",
                            $applicationID,
                            (int) $_GET['job'],
                            (int) $_SESSION['userID'],
                            (int) $questionID,
                            '',
                            $file,
                            date('Y-m-d H:i:s'),
                            $file,
                            date('Y-m-d H:i:s'));
                        $db->query($qry);
                    }
                } else {

                    $qry = sprintf("INSERT INTO tblAnswers (applicationID, jobID, userID, questionID, optionID, value, sysDateInserted) VALUES ('%d', '%d', '%d', '%d', '%d', '%s', '%s') ON DUPLICATE KEY UPDATE value='%s', sysDateInserted='%s'",
                        $applicationID,
                        (int) $_GET['job'],
                        (int) $_SESSION['userID'],
                        (int) $questionID,
                        '',
                        $db->escape((isset($_POST[$questionID]) && !is_array($_POST[$questionID])) ? $_POST[$questionID] : ''),
                        date('Y-m-d H:i:s'),
                        $db->escape((isset($_POST[$questionID]) && !is_array($_POST[$questionID])) ? $_POST[$questionID] : ''),
                        date('Y-m-d H:i:s'));
                    $db->query($qry);

                }

            }
            
            header('Location: /apply/' . (int)$_GET['job'] . '?success');
        }

    }

    if (isset($error) && $error != '') {
        $quipp->js['onload'] .= 'alertBox("fail", "' . $error . '");';
    }

?>

<form id="job-form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
    <div id="card" class="box userinfo">
        <div class="heading">
            <h2>Enter Your Information</h2>
        </div>
        <div class="cutout">
            <div class="profilePic"><img src="<?php echo $profileImg;?>" alt="" /></div>
        </div>
        <!--<dl>
            <dt>First Name</dt>
            <dd><input type="text" id="First_Name" name="First_Name" class="full" placeholder="First Name" value="<?php echo $post["First_Name"]["value"];?>" required="required"/></dd>
            
            <dt>Last Name</dt>
            <dd><input type="text" id="Last_Name" name="Last_Name" class="full" placeholder="Last Name" value="<?php echo $post["Last_Name"]["value"];?>" required="required"/></dd>

            <dt>Email</dt>
            <dd><input type="text" id="Email" name="Email" class="full" placeholder="Email Address" value="<?php echo $post["Email"]["value"];?>" required="required"/></dd>

            <dt>Password</dt>
            <dd><input type="password" id="password" name="password" class="half left bottom" placeholder="Password" <?php echo (!isset($_SESSION["userID"]) ? 'required="required"' : ''); ?>/></dd>
            
            <dt>Re-type</dt>
            <dd><input type="password" id="confirmPassword" name="confirmPassword" class="half bottom" placeholder="Re-Type Password" <?php echo (!isset($_SESSION["userID"]) ? 'required="required"' : ''); ?>/></dd>

            <dt>Website</dt>
            <dd><input type="text" id="Website_or_Blog_URL" name="Website_or_Blog_URL" class="half left" placeholder="Website URL" value="<?php echo $post["Website_or_Blog_URL"]["value"];?>"/></dd>

            <dt>Facebook</dt>
            <dd><input type="text" id="Facebook_Username" name="Facebook_Username" class="half" placeholder="Facebook Username" value="<?php echo $post["Facebook_Username"]["value"];?>"/></dd>
            
            <dt>Twitter</dt>
            <dd><input type="text" id="Twitter_Username" name="Twitter_Username" class="half left bottom" placeholder="Twitter Handle" value="<?php echo $post["Twitter_Username"]["value"];?>"/></dd>
            
            <dt>LinkedIn</dt>
            <dd><input type="text" id="LinkedIn_Username" name="LinkedIn_Username" class="half bottom" placeholder="LinkedIn ID" value="<?php echo $post["LinkedIn_Username"]["value"];?>"/></dd>
         </dl>-->
        <dl>
            <dt>First Name</dt>
            <dd><input type="text" id="First_Name" name="First_Name" class="full" placeholder="First Name" value="" required="required"/></dd>
            
            <dt>Last Name</dt>
            <dd><input type="text" id="Last_Name" name="Last_Name" class="full" placeholder="Last Name" value="" required="required"/></dd>

            <dt>Email</dt>
            <dd><input type="text" id="Email" name="Email" class="full" placeholder="Email Address" value="" required="required"/></dd>

            <dt>Password</dt>
            <dd><input type="password" id="password" name="password" class="half left bottom" placeholder="Password" /></dd>
            
            <dt>Re-type</dt>
            <dd><input type="password" id="confirmPassword" name="confirmPassword" class="half bottom" placeholder="Re-Type Password" /></dd>

            <dt>Website</dt>
            <dd><input type="text" id="Website_or_Blog_URL" name="Website_or_Blog_URL" class="half left" placeholder="Website URL" value=""/></dd>

            <dt>Facebook</dt>
            <dd><input type="text" id="Facebook_Username" name="Facebook_Username" class="half" placeholder="Facebook Username" value=""/></dd>
            
            <dt>Twitter</dt>
            <dd><input type="text" id="Twitter_Username" name="Twitter_Username" class="half left bottom" placeholder="Twitter Handle" value=""/></dd>
            
            <dt>LinkedIn</dt>
            <dd><input type="text" id="LinkedIn_Username" name="LinkedIn_Username" class="half bottom" placeholder="LinkedIn ID" value=""/></dd>
         </dl>
    </div>
    
    <div id="submissions">
    <table class="simpleTable">
    <tr><th><?php echo $title; ?></th></tr>
    <?php

    if (is_array($q->questions) && !empty($q->questions)) {
        foreach ($q->questions as $questionID => $question) {
        	
        	if ($question['type'] != 4) { //If question isn't a video question
	        	
		        echo "<tr>";
	            echo "<td>";
	            echo $question['label'];
	            echo "</td>";
	            echo "</tr>";	
        	}
            

            echo "<tr>";
            echo "<td>";
            switch ($question['type']) {
                case 1: //radio
                case 2: //checkbox

                    if (isset($question['options']) && !empty($question['options'])) {
                        echo '<ul>';
                        foreach ($question['options'] as $optionID => $opt) {

                            $id   = $questionID . '_' . $optionID;
                            $name = $questionID;

                            echo '<li>';
                            if ($question['type'] == '1') {
                                $checked = (isset($_POST[$name]) && $_POST[$name] == $opt['itemID']) ? ' checked="checked"' : '';
                                echo '<input type="radio" id="' . $id . '"  name="' . $name . '"  value="' . $opt['itemID'] . '"' . $checked . '/>';
                            } else {
                                $checked = (isset($_POST[$name]) && in_array($opt['itemID'], $_POST[$name])) ? ' checked="checked"' : '';
                                echo '<input type="checkbox" id="' . $id . '"  name="' . $name . '[]"  value="' . $opt['itemID'] . '"' . $checked . ' />';
                            }
                            echo '<label for="' . $id . '">' . $opt['label'] . '</label>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo "No options available currently.";
                    }

                break;

                case 3: //slider
                    $name = $id = $questionID;
                    $val = (isset($_POST[$name])) ? $_POST[$name] : 0;
                    echo "<div class=\"slider\" rel=\"$name\" alt='".$val."'></div><input type=\"hidden\" name=\"$name\" id=\"$id\" value=\"".$val."\" /><div class='sliderValueHolder' rel='$id'>".$val."/20</div>";

                break;

                case 4: //video
                	
                	$videos .= "<div class='video-q-holder' id='video".$videoCount."' data-vidnumber='".$videoCount."'>";
                	$videos .= "<label class='video-label'>".$question['label']."</label>";
                	$videos .= "<div class='video-flash-holder'>";
                	
                    $video   = $db->return_specific_item('', 'tblVideos', 'filename', 0, "jobID='" . (int) $_GET['job'] . "' AND questionID='" . $questionID . "' AND userID='" . (int) $_SESSION['userID'] . "' AND sysOpen='1' AND sysActive='1'") ;
                    $videoID = $db->return_specific_item('', 'tblVideos', 'itemID', 0, "jobID='" . (int) $_GET['job'] . "' AND questionID='" . $questionID . "' AND userID='" . (int) $_SESSION['userID'] . "' AND sysOpen='1'") ;

                    if ($video !== 0) {

                       $videos .= '<embed src="/includes/apps/ams-media/flx/captureModule.swf" quality="high" bgcolor="#000000" width="550" height="400" name="captureModule" FlashVars="reviewFile=<?php echo $video; ?>" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflash" />';

                    } else {

                        if ($videoID == 0) {
                            $qry = sprintf("INSERT INTO tblVideos (userID, jobID, questionID, filename, sysDateInserted, sysDateLastMod) VALUES ('%d', '%d', '%d', '', NOW(), NOW())",
                                (int) $_SESSION['userID'],
                                (int) $_GET['job'],
                                $questionID);
                            $db->query($qry);
                            $videoID = $db->insert_id();
                        }
                        $videos .= '<embed src="/includes/apps/ams-media/flx/captureModule.swf" quality="high" bgcolor="#000000" width="550" height="400" name="captureModule" FlashVars="itemID=' . $videoID . '&securityKey=' . md5("iLikeSalt" . $videoID) . '" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflash" />';

                    }

                    $videos .= '</div>';
                    $videos .= '<input type="hidden" name="' . $questionID . '" value="' . $videoID . '" />';
                    $videos .= '<input type="button" class="btn green nextbutton" value="Next" data-section="video" />';
                    $videos .= "</div>";
                    $videoCount++;

                break;
                case 5: //file

                    echo '<input type="file" name="' . $questionID . '" id="' . $questionID . '" />';
                break;

            }
        }
        
                    
        /* Allow users to upload their resumes/CVs */
        echo "<label for='coverLetter'>Upload Cover Letter: </label><input type='file' name='coverLetter' id='coverLetter'></br>";
        echo "<label for='resume'>Upload Resume: </label><input type='file' name='resume' id='resume'></br>";
        echo "</td>";
        echo "</tr>";
            
    } else {
        $quipp->js['onload'] .= 'alertBox("fail", "This application has no questions");';
    }
    
    

?>
    </table>
    <input type="button" class="btn green nextbutton" value="Next" data-section="questions" />
    </div>
    
    <?php
    echo($videos);
    ?>
    <div id="finalStep">
    <input type="submit" class="btn green" value="Submit" />
    </div>
</form>
<?php
}
