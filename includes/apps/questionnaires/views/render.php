<?php

global $quipp;

require_once dirname(dirname(__DIR__)) . '/jobs-manager/JobManager.php';
require_once dirname(dirname(__DIR__)) . '/job-info/JobInfo.php';
require_once dirname(__DIR__) . '/Questionnaire.php';



if (!isset($f) || !$f INSTANCEOF Forms){
    $f = new Forms($db);
}

/*
CREATE A USERRRR -----------------
*/ 
if (isset($_POST['Email']) && isset($_POST['Confirm_Email']) && $_POST['Email'] != $_POST['Confirm_Email']) {
	
	$message = "Your email addresses do not match.";
	
}
else if(isset($_POST) && !empty($_POST)){  
    //get values from form 
    
    $firstName 		= 	str_replace("'", "", $_POST['First_Name']);
    $lastName 		= 	str_replace("'", "", $_POST['Last_Name']);
    $address 		= 	str_replace("'", "", $_POST['Address']);
    $city 		    = 	str_replace("'", "", $_POST['City']);
    $postalCode     = 	str_replace("'", "", $_POST['Postal_Code']);
    $phone 		    = 	str_replace("'", "", $_POST['Phone']);
    $email  		= 	str_replace("'", "", $_POST['Email']);
    $confirmEmail 	= 	str_replace("'", "", $_POST['Confirm_Email']);
    $facebook 		= 	str_replace("'", "", $_POST['Facebook_Username']);
    $twitter 		= 	str_replace("'", "", $_POST['Twitter_Username']);
    $linkedIn 		= 	str_replace("'", "", $_POST['LinkedIn_Username']);


    $meta = array(
    	array("fieldLabel" => "First Name", 			"validationCode" => "RQvalALPH"),
    	array("fieldLabel" => "Last Name", 				"validationCode" => "RQvalALPH"),
    	array("fieldLabel" => "Address", 				"validationCode" => "RQvalALPH"),
    	array("fieldLabel" => "City", 					"validationCode" => "RQvalALPH"),
    	array("fieldLabel" => "Postal Code",			"validationCode" => "RQvalALPH"),
    	array("fieldLabel" => "Phone",		 			"validationCode" => "RQvalPHON"),
    	array("fieldLabel" => "Email", 					"validationCode" => "RQvalMAIL"),
    	array("fieldLabel" => "Confirm Email", 			"validationCode" => "RQvalMAIL"),
    	array("fieldLabel" => "Facebook Username", 		"validationCode" => "OPvalALPH"),
    	array("fieldLabel" => "Twitter Username", 		"validationCode" => "OPvalALPH"),
    	array("fieldLabel" => "LinkedIn Username", 		"validationCode" => "OPvalALPH")

    );
    
    $post   = array();
    foreach($meta as $fields){
    	$post[str_replace(" ","_",$fields["fieldLabel"])] = array("code" => $fields["validationCode"], "value" => "", "label" => $fields["fieldLabel"]);
    }
    
/*     if (isset($_POST["job-form"])){ */
    	
        
    $submitted = true;
    $valid = false;
    
    $validate = array();
    foreach($post as $field => $nfo){
    
        $validate[$nfo["code"].$field] = "";
        
        if (isset($_POST[$field])){
        	
            $validate[$nfo["code"].$field] = $_POST[$field];
            $post[$field]["value"] = $_POST[$field];
        }
        //else if ($field == "Job_Credits"){
        //    $post[$field]["value"] = "2";
        //}
    }
    
    
    if (validate_form($validate)){
        $valid = true;
        unset($post[2]); //don't want to pass this to createUserAccount
    }
    else {
	    $message = "Please fill in all fields.";
    }

    if ($valid == true){
        $message = "";

        if (0 === ($userID = $f->createUserAccount($post, NULL, "applicants"))){
            $valid = false;
        }
    }
    
    var_dump($valid); 

/*     } */
    
 }
/*
*********************************
*/

if (!isset($j) || !$j INSTANCEOF JobInfo){
    $j = new JobInfo($db);
}

list($title, $link, $dateExpires, $datePosted, $questionnaireID, $status, $companyID) = $j->getJob($_GET['job']);

if (time() < strtotime($datePosted) || $status == 'inactive') {
    $quipp->js['onload'] .= 'alertBox("fail", "No job found");';

} elseif (time() > strtotime($dateExpires)) {
    $quipp->js['onload'] .= 'alertBox("fail", "We\'re sorry, this job posting has expired");';

/* this doesn't matter any more because they're creating a new account every time. 

} elseif ($j->hasApplied($_GET['job'])) {
    
    if (isset($_GET['success'])) {
        $quipp->js['onload'] .= 'alertBox("success", "Thank you for applying. Your application has been received.");';
    } else {
        $quipp->js['onload'] .= 'alertBox("fail", "You have already applied");';
    }
    include __DIR__ . '/renderAnswers.php';
*/
}  
/*
elseif (isset($message) && !empty($message)) {

	$quipp->js['onload'] .= 'alertBox("fail", "'.$message.'");';

}
*/
else {
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

    if (!empty($_POST) && empty($message)) {
    	
/*
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
    	
*/
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
            
/*             header('Location: /apply/' . (int)$_GET['job'] . '?success'); */
        }

    }

    if (isset($error) && $error != '') {
        $quipp->js['onload'] .= 'alertBox("fail", "' . $error . '");';
    }
?>

<form id="job-form" name="jobForm" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
    <div id="card" class="box userinfo">
        <div class="heading">
            <h2>Enter Your Information</h2>
        </div>
        <div class="cutout">
<!--             <div class="profilePic"><img src="<?php echo $profileImg;?>" alt="" /></div> -->
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
            <dd><input type="text" id="First_Name" name="First_Name" class="full" placeholder="First Name" value="<?php echo isset($post['First_Name']) ? $post['First_Name']['value'] : ""; ?>" required="required"/></dd>
            
            <dt>Last Name</dt>
            <dd><input type="text" id="Last_Name" name="Last_Name" class="full" placeholder="Last Name" value="<?php echo isset($post['Last_Name']) ? $post['Last_Name']['value'] : ""; ?>" required="required"/></dd>

            <dt>Address</dt>
            <dd><input type="text" id="Address" name="Address" class="full" placeholder="Address" value="<?php echo isset($post['Address']) ? $post['Address']['value'] : ""; ?>" required="required"/></dd>
            
            <dt>City</dt>
            <dd><input type="text" id="City" name="City" class="full" placeholder="City" value="<?php echo isset($post['City']) ? $post['City']['value'] : ""; ?>" required="required"/></dd>

            <dt>Postal Code</dt>
            <dd><input type="text" id="Postal_Code" name="Postal_Code" class="full" placeholder="Postal Code" value="<?php echo isset($post['Postal_Code']) ? $post['Postal_Code']['value'] : ""; ?>" required="required"/></dd>
            
            <dt>Phone</dt>
            <dd><input type="text" id="Phone" name="Phone" class="full" placeholder="Phone" value="<?php echo isset($post['Phone']) ? $post['Phone']['value'] : ""; ?>" required="required"/></dd>

            <dt>Email</dt>
            <dd><input type="text" id="Email" name="Email" class="full" placeholder="Email Address" value="<?php echo isset($post['Email']) ? $post['Email']['value'] : ""; ?>" required="required"/></dd>

            <dt>Confirm</dt>
            <dd><input type="text" id="Confirm_Email" name="Confirm_Email" class="full" placeholder="Confirm Email" value="<?php echo isset($post['Confirm_Email']) ? $post['Confirm_Email']['value'] : ""; ?>" required="required"/></dd>

            <dt>Facebook</dt>
            <dd><input type="text" id="Facebook_Username" name="Facebook_Username" class="half" placeholder="Facebook Username" value="<?php echo isset($post['Facebook_Username']) ? $post['Facebook_Username']['value'] : ""; ?>"/></dd>
            
            <dt>Twitter</dt>
            <dd><input type="text" id="Twitter_Username" name="Twitter_Username" class="half left bottom" placeholder="Twitter Handle" value="<?php echo isset($post['Twitter_Username']) ? $post['Twitter_Username']['value'] : ""; ?>"/></dd>
            
            <dt>LinkedIn</dt>
            <dd><input type="text" id="LinkedIn_Username" name="LinkedIn_Username" class="half bottom" placeholder="LinkedIn ID" value="<?php echo isset($post['LinkedIn_Username']) ? $post['LinkedIn_Username']['value'] : ""; ?>"/></dd>
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
    
<!--
    <?php
    echo($videos);
    ?>
   <div id="finalStep">
    	<input type="submit" class="btn green" value="Submit" />
    </div>
-->
</form>

<?php }
