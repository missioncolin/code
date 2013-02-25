<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>


<!-- Javascript - whether can transition to video -->

<script>
	
$(function() {

	/* Store variables for whether the form has submitted successfully */
	var successfulApp = <?php echo isset($_REQUEST['submitted']) ? '1' : '0'; ?>;
	var jobTitle = "<?php echo $title; ?>";
	var isSession = "<?php echo isset($_SESSION['userID']); ?>";
	
	/* If successfulApp == 1, transition to video - otherwise
	   first time on the page, display welcome message for applying */
	if (successfulApp == 1) {
		$(".userinfo").fadeOut();
			$("#submissions").fadeOut(400, function() {
				
				/*if ($("#video1").is('*')) {
					$("#video1").fadeIn();
					$activeVideo = 1;
				}*/
				$(".instructions").show();
				$(".instructions").fadeIn();
			});
		
		$('.current').removeClass().next().addClass('current');
				
				
		/*$(".userinfo").fadeOut();
			$("#submissions").fadeOut(400, function() {
				
				if ($("#video1").is('*')) {
					$("#video1").fadeIn();
					$activeVideo = 1;
				}
			});
		
		$('.current').removeClass().next().addClass('current');
		*/
	}
	
	/* Handle displaying the welcome popup */
	else if (successfulApp != 1 && isSession == 0) {
		$('.popUp').addClass('success');
		confirmAction("Thank you for applying to " + jobTitle + "!", "Begin by filling out your profile below, use the sliders to select the years of experience you have in each skill and upload your resume and cover letter.");

	}
	
	/* Clears the pop-up when user confirms */
	$('#confirmWelcome').on('click', function() {
			
        $('#confirm').fadeOut('fast', function() {
	    	$('.popUp h2').empty();
	        $('.popUp p').empty();
	        $('.popUp #popUpOk').off('click');
	        $('.popUp #popUpNo').off('click'); 
	        $('.popUp').removeClass('success');
	        $('.popUp').removeClass('fail');
	        $('.popUp #popUpNo').show();
        });
	});
	
		/* Clears the pop-up when user confirms */
	$('#takeAwayOk').on('click', function() {
			
        $('#takeAway').fadeOut('fast', function() {
	    	$('.popUp h2').empty();
	        $('.popUp p').empty();
	        $('.popUp #popUpOk').off('click');
	        $('.popUp #popUpNo').off('click'); 
	        $('.popUp').removeClass('success');
	        $('.popUp').removeClass('fail');
	        $('.popUp #popUpNo').show();
        });
	});

});

</script>


<?php

global $quipp, $message;

require_once dirname(dirname(__DIR__)) . '/jobs-manager/JobManager.php';
require_once dirname(dirname(__DIR__)) . '/job-info/JobInfo.php';
require_once dirname(__DIR__) . '/Questionnaire.php';

if (!isset($f) || !$f INSTANCEOF Forms){
    $f = new Forms($db);
}


//create a new blank user if there is no session ID

if (!isset($_SESSION['userID']) || !$_SESSION['userID'] > 0){

	$blankUser = array();

    //build array
    $blankUser["First_Name"] 			= array("code" => "RQvalALPH", "value" => "", "label" => "First Name"			);
    $blankUser["Last_Name"] 			= array("code" => "RQvalALPH", "value" => "", "label" => "Last Name"			);
    $blankUser["Company_Address"] 		= array("code" => "RQvalALPH", "value" => "", "label" => "Company Address"		);
    $blankUser["Company_City"] 			= array("code" => "RQvalALPH", "value" => "", "label" => "Company City"			);
    $blankUser["Company_Postal_Code"] 	= array("code" => "RQvalALPH", "value" => "", "label" => "Company Postal Code"	);
    $blankUser["Phone_Number"] 			= array("code" => "RQvalPHON", "value" => "", "label" => "Phone Number"			);
    $blankUser["Email"] 				= array("code" => "RQvalMAIL", "value" => "newuser".rand(99999999, 999999999)."@res.im", "label" => "Email"				);
    $blankUser["Confirm_Email"] 		= array("code" => "RQvalMAIL", "value" => "newuser".rand(99999999, 999999999)."@res.im", "label" => "Confirm Email"		);
    $blankUser["Facebook_Username"] 	= array("code" => "OPvalALPH", "value" => "", "label" => "Facebook Username"	);
    $blankUser["Twitter_Username"] 		= array("code" => "OPvalALPH", "value" => "", "label" => "Twitter Username"		);
    $blankUser["LinkedIn_Username"] 	= array("code" => "OPvalALPH", "value" => "", "label" => "LinkedIn Username"	);	
	
	$_SESSION['userID'] = $f->createUserAccount($blankUser, "buPa55w0rDjdafjdm", "applicants");

}else{
	//load details 
		 //$firstName = $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 1 AND userID = " . $_SESSION['userID']); 
		$post = array();
		$post['First_Name']['value'] 			= $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 1  AND userID = " . $_SESSION['userID']);
		$post['Last_Name']['value']  			= $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 2  AND userID = " . $_SESSION['userID']);
		$post['Company_Address']['value']   	= $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 6  AND userID = " . $_SESSION['userID']);
		$post['Company_City']['value']	   		= $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 11 AND userID = " . $_SESSION['userID']);
		$post['Company_Postal_Code']['value'] 	= $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 12 AND userID = " . $_SESSION['userID']);
		$post['Phone_Number']['value']			= $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 4  AND userID = " . $_SESSION['userID']); 
		$post['Email']['value'] 				= $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 3  AND userID = " . $_SESSION['userID']); 
		$post['Facebook_Username']['value'] 	= $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 27 AND userID = " . $_SESSION['userID']); 
		$post['Twitter_Username']['value']		= $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 30 AND userID = " . $_SESSION['userID']); 
		$post['LinkedIn_Username']['value']		= $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 26 AND userID = " . $_SESSION['userID']); 
		
		if (!strpos($post['Email']['value'], "newuser")){ 
			$post['Confirm_Email']['value'] = $post['Email']['value'];
		}else{
			$post['Confirm_Email']['value'] = "";
			$post['Email']['value'] = "";
		}
}

if (isset($_GET['user']) || isset($_SESSION['userID'])) {
	/* Revisiting page - check whether already applied */
	
	if (isset($_GET['user'])) {
		$usrQry = sprintf("SELECT * FROM tblApplications WHERE userID = '%d' AND jobID = '%d' AND sysActive = '1'", (int)$_GET['user'], (int)$_GET['job']);
		$usrRes = $db->query($usrQry);
		
		$returnedThis = $db->fetch_assoc($usrRes);		
	}
	
	if (isset($_SESSION['userID'])) {
		$usrQry = sprintf("SELECT * FROM tblApplications WHERE userID = '%d' AND jobID = '%d' AND sysActive = '1'", (int)$_SESSION['userID'], (int)$_GET['job']);
		$usrRes = $db->query($usrQry);		
		$secondThis = $db->fetch_assoc($usrRes);	
	}
	
	if (!empty($returnedThis) || !empty($secondThis)) {
		$message = "You have already applied to this job.";
	}	
}

if (isset($_POST['Email']) && isset($_POST['Confirm_Email']) && $_POST['Email'] != $_POST['Confirm_Email']) {
	
	$message = "Your email addresses do not match.";
	
} else if(isset($_POST) && !empty($_POST) && empty($message)){  
    //UPDATE ACCOUNT  


    $meta = array(
    	array("fieldLabel" => "First Name", 			"validationCode" => "RQvalALPH"),
    	array("fieldLabel" => "Last Name", 				"validationCode" => "RQvalALPH"),
    	array("fieldLabel" => "Company Address", 		"validationCode" => "RQvalALPH"),
    	array("fieldLabel" => "Company City", 			"validationCode" => "RQvalALPH"),
    	array("fieldLabel" => "Company Postal Code",	"validationCode" => "RQvalALPH"),
    	array("fieldLabel" => "Phone Number",		 	"validationCode" => "RQvalPHON"),
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
    
    foreach($post as $field => $nfo) {
    
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
	    	echo '<div id="steps" style="margin-top: 20px;"><li class="alert fail"><span></span>';
			echo $message;
			echo "</li></div>";
    }

    if ($valid == true){
        $message = "";

//merge conflict line        
        if (0 === ($userID = $f->updateUserAccountApplicant($post, "buPa55w0rDjdafjdm"))){

   /*
   	//merge conflict code
        if (isset($_SESSION['success'])) {
        	$userID = $f->updateUserAccount($post, "");
        	
        	if ($message != "") {
	        	$valid = false;
	        	$quipp->js['onload'] .= 'alertBox("fail", "'.$message.'")';
        	}
        }
        
        elseif (0 === ($userID = $f->createUserAccount($post, NULL, "applicants"))){
        */
            $valid = false;
			echo '<div id="steps" style="margin-top: 20px;"><li class="alert fail"><span></span>';
			echo $message;
			echo "</li></div>";       
		}
    }

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


} elseif (!empty($message) && $message == "You have already applied to this job.") { 

	echo '<div id="steps" style="margin-top: 20px;"><li class="alert fail"><span></span>';
	echo $message;
	echo "</li></div>";
	
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
    	
    	if (($_FILES['resume']['size'] != 0) || ($_FILES['coverLetter']['size'] != 0)) {
	    		    	
	    	foreach ($_FILES as $f=>$values) {
		    	if ($values['error'] == 0) {
			    	
			    	/* Holds separate 'question IDs' for each in tblAnswers */
			    	if ($f == 'coverLetter') {
			    		$questionID = 0;
			    	}			    	
			    	else {
				    	$questionID = -1;
			    	}			    	 
			    	               
                	/* See whether /applications exists, if not - create it */
                	if (!is_dir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications')) {
	                	mkdir(dirname(dirname(dirname(dirname(__DIR__)))) . 'uploads/applications');
                	}
                	
			    	if (!is_dir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job'] . '/' . (int) $_SESSION['userID'])) {
                        $successfulMkdir = mkdir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job']);
                        $successfulMkdir = mkdir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job'] . '/' . (int) $_SESSION['userID']);
                    }
                    else {
	                    $successfulMkdir = 1;
                    }

                    if ($successfulMkdir) {
	                    $file = upload_file($f, dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications/' . (int) $_GET['job'] . '/' . (int) $_SESSION['userID'] . '/', $MIME_TYPES, false, false, false, base_convert(0, 10, 36));
	                    
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
	                 }
                    
	    	     }
		    	
		    	else {
			    	$message .= "Error: ".$f['Name']." - ".$f['error']."</br>";
		    	}
	    	}
    	}

    	//SAVE QUESTIONS
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
                
                	/* See whether /applications exists, if not - create it */
                	if (!is_dir(dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/applications')) {
	                	mkdir(dirname(dirname(dirname(dirname(__DIR__)))) . 'uploads/applications');
                	}
                	
                	/* Create new directory for this application and user if DNE */
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
            
           $_SESSION['success'] = 1;
        }

    }

    if (isset($post['Email']) && strpos($post['Email']['value'], "@res.im") > 0){ 
		$post['Confirm_Email']['value'] = "";
		$post['Email']['value'] = "";
	}

    if (isset($error) && $error != '') {
       	echo '<div id="steps" style="margin-top: 20px;"><li class="alert fail"><span></span>';
		echo $message;
		echo "</li></div>";
    }



?>

<form id="job-form" name="jobForm" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
<!--<form id="job-form" method="post" action="<?php //echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">-->
<!-- <form id="job-form" method="post"> -->
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
            <dd><input type="text" id="Company_Address" name="Company_Address" class="full" placeholder="Address" value="<?php echo isset($post['Company_Address']) ? $post['Company_Address']['value'] : ""; ?>" required="required"/></dd>
            
            <dt>City</dt>
            <dd><input type="text" id="Company_City" name="Company_City" class="full" placeholder="City" value="<?php echo isset($post['Company_City']) ? $post['Company_City']['value'] : ""; ?>" required="required"/></dd>

            <dt>Postal Code</dt>
            <dd><input type="text" id="Company_Postal_Code" name="Company_Postal_Code" class="full" placeholder="Postal Code" value="<?php echo isset($post['Company_Postal_Code']) ? $post['Company_Postal_Code']['value'] : ""; ?>" required="required"/></dd>
            
            <dt>Phone</dt>
            <dd><input type="text" id="Phone_Number" name="Phone_Number" class="full" placeholder="Phone" value="<?php echo isset($post['Phone_Number']) ? $post['Phone_Number']['value'] : ""; ?>" required="required"/></dd>

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
    <tr><td>How many years of experience to do you have in these skills?</td></tr>
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
                    $val = $db->return_specific_item(false, "tblAnswers", "value", "0", "jobID = ".$_GET['job']." AND questionID = ".$name." AND userID = " . $_SESSION['userID']);
                    echo "<div class=\"slider\" rel=\"$name\" alt='".$val."'></div><input type=\"hidden\" name=\"$name\" id=\"$id\" value=\"".$val."\" /><div class='sliderValueHolder' rel='$id'>".$val."/20 years of experience</div>";

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
                    $videos .= '<input type="button" class="btn red prevbutton" value="Previous" data-section="video" />';
                    $videos .= '<input type="button" class="btn green nextbutton" value="Next" data-section="video" />';
                    $videos .= "</div>";
                    $videoCount++;

                break;
                case 5: //file

                    echo '<input type="file" name="' . $questionID . '" id="' . $questionID . '" />';
                break;

            }
        }
                
        if ((isset($_FILES['resume']['name']) || isset($_FILES['coverLetter']['name']))) {
        
        	$currentFiles = "";
        	
        	foreach ($_FILES as $f=>$info) {
	        	
	        	if (isset($info['name']) && $info['name'] != '') {
		        	$currentFiles .= "<li>".$info['name']."</li></br> "; 
	        	}
        	}
        	
	        $fileMessage = "You have uploaded the following files: </br></br>".$currentFiles." </br>";
        
	        ?>
	        <div id="steps">
		        <li class="alert success">
		        	<?php echo $fileMessage; ?>
		        </li>
	        </div>

        <?php 
        }
        
        
        ?>
        
                         
        <!---- Allow users to upload their docs ----> 
        <label for='coverLetter'>Upload Cover Letter: </label><input type='file' name='coverLetter' id='coverLetter'></br>
        <label for='resume'>Upload Resume: </label><input type='file' name='resume' id='resume'></br>
        </td>
        </tr>
        
    <?php
            
    } else {
        	echo '<div id="steps" style="margin-top: 20px;"><li class="alert fail"><span></span>';
			echo "This application has no questions.";
			echo "</li></div>";
    }
    
    

?>
    </table>
    <input type="hidden" name="submitted" value="1"/>
    <input type="submit" class="btn green" value="Next" data-section="questions" />
    </div>
    
    <?php
    echo($videos);
    
    ?>
   <div id="finalStep">
   		<div id="thankYouMsg">
	   		 <div id="steps">
			        <li class="alert success">
			        	<span></span>
			        	Thank you for applying to the position of <?php echo $title; ?> with <?php echo $db->return_specific_item(false, "sysUGFValues", "value", "--", "fieldID = 10  AND userID = " . $companyID);?>.</br> 							<center>Your application has been successfully submitted. Only those qualified will be contacted for an interview.</center>
			        </li>
		        </div>
   		</div>
   		<input type="button" id="finalPrev" class="btn red prevbutton" value="Previous" data-section="final" />
    	<input type="button" class="btn green thankYou" data-user="<?php echo $_SESSION['userID']; ?>" data-job="<?php echo $_GET['job']; ?>" value="Submit" />
    </div>
    
    
    <!--instructions page-->
    <div id="instructions">
    	<div class="ColA">
	    	<div>Answer the hiring managers questions using your web cam</div>
	    	<div>
	    		<strong>Important information</strong>
	    		<ul>
	    			<li>You need a web cam and microphone</li>
	    			<li>Test it out to see if it works</li>
	    			<li>Refresh page if you are having issues</li>
	    			<li>Your videos cannot be shared or downloaded</li>
	    		</ul>
		    </div> 
		    <div>
	    		<strong>The intervuew process</strong>
	    		<ul>
	    			<li>There are 5 questions</li>
	    			<li>Each question has a 2 minute time limit</li>
	    			<li>Click done when you are finished answering</li>
	    			<li>You can revue your answers and do retakes</li>
	    		</ul>
		    </div> 
		    <div>
	    		<strong>Make the best impression</strong>
	    		<ul>
	    			<li>Dress professionally</li>
	    			<li>Make sure the room is well lit</li>
	    			<li>Clear the area around you</li>
	    			<li>Be yourself</li>
	    		</ul>
		    </div> 
		    <div>
		    	<span>
		    		Accept <a id="privacyPolicy" name="privacyPolicy" href="#">privacy policy</a>
		    		<input type="checkbox">
		    	</span>
		    </div>
    	</div>
    	
    	<div class="ColB">
    		<div>
    			<span>Test your camera and microphone</span>
    		</div>
    		<div>
    			VIDEO
    			-video title
    		</div>
    		<div>
    			<input type="button" class="saveButton" value="Save now and do interview later"/>
    			<input type="button" class="nextbutton" data-section="instructions" value="Continue and begin interview"/>
    		</div>
    	</div>
    </div>

</form>


<!-- Takeaway Link Popup -->
<div id="takeAway" style="display:none; z-index: 1000;">
	<div class="popUp success">	
		<h2>Your application is now saved.</h2>
		<p>An email has been sent to you. When you are ready please click the link in the email to continue your application.</br> <!-- <a href="http://<?php echo $_SERVER["SERVER_NAME"]."/apply/".$_GET['job']."?user=".$_SESSION['userID']; ?>"><?php echo $_SERVER["SERVER_NAME"]."/apply/".$_GET['job']."?user=".$_SESSION['userID']; ?></a></p> -->		
		<a class="btn" style="margin-top: 10px;" id="takeAwayOk">Ok</a>

		
	</div>
</div>

<!-- Welcome Popup --->
<div id="confirm" style="display:none; z-index: 1000;">
<div class="popUp">
<h2></h2>
<p></p>
<a class="btn" id="confirmWelcome">Ok</a>
</div>
</div>

<?php }
