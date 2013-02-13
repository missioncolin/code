<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>

<script>

function ajaxFunction(questionnaireID, inputAction, questionID, typeID, label, idealVal) {
	
	var ajaxRequest;
	
	try {
		
		// Handles Opera, Firefox, Safari, Chrome
		ajaxRequest = new XMLHttpRequest();
		
	} catch(e) {
		// Handles IE...
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch(e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch(e) {
				alert("There has been an error.");
				return false;
			}
		}
	}
	
	// Receive data
	ajaxRequest.onreadystatechange = function(){
		if(ajaxRequest.readyState == 4){
			console.log(ajaxRequest.responseText);
		}
	}
	
	//Send a request:
	// 1. Specify URL of server-side script that will be used in Ajax app
	// 2. Use send function to send request
	var parameters = "questionnaireID=" + questionnaireID + "&action=" + inputAction + "&questionID=" + questionID + "&typeID=" + typeID + "&label=" + label + "&idealValue=" + idealVal; 
	ajaxRequest.open("POST", "/includes/apps/questionnaires/ajax/gateway.php", true); 			
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");	
	ajaxRequest.send(parameters);
	
}

</script>



<?php

if ($this instanceof Quipp) {
    global $message;

    if (!isset($_GET['action'])) {
        $_GET['action'] = "new";
    }
    
    /// MANAGEMENT OF NEWLY CREATED QUESTIONNAIRES
    if (!empty($_POST) && isset($_POST["configure-question"]) && !isset($_POST["submitEditQs"]) ) {
	    
	    print_r($_POST);
	    
        if (validate_form($_POST)) {

            switch ($_GET['action']) {

            case 'new':
                
                if (is_array($_POST['RQvalALPHQuestions'])) {
                    
                    $i = 0;
                    while( $i < count($_POST['RQvalALPHQuestions'])) {
                        
                        	if ($_POST['RQvalALPHQuestions'][$i] != '') {
		                        $qry = sprintf("INSERT INTO tblQuestions (label, type, questionnaireID, idealValue ) VALUES('%s', '%d', '%d', '%d')",
		                            $db->escape(strip_tags($_POST['RQvalALPHQuestions'][$i])),
		                            (int) $_POST['RQvalNUMBType'],
		                            (int) $_GET['qnrID'],
		                            (int) $_POST['idealValues'][$i]);
		                        $db->query($qry);
	                        }
	                        
	                        $i += 1;
                       
                     }
                    
                } else {
                
                	if ($_POST['RQvalALPHQuestion'] != '') {
	                    $qry = sprintf("INSERT INTO tblQuestions (label, type, questionnaireID, idealValue ) VALUES('%s', '%d', '%d', '%d')",
	                        $db->escape(strip_tags($_POST['RQvalALPHQuestion'])),
	                        (int) $_POST['RQvalNUMBType'],
	                        (int) $_GET['qnrID'],
	                        (int) $_POST['idealValues']);
	                    $db->query($qry);
                    }    
                }
                $success = 1;

                break;
                
            case 'edit':

            	
                $qry = sprintf("UPDATE tblQuestions SET label = '%s', type = '%d' WHERE itemID = '%d'",
                    $db->escape(strip_tags($_POST['RQvalALPHQuestion'])),
                    (int) $_POST['RQvalNUMBType'],
                    (int) $_GET['qsnID']);
                $db->query($qry); 
                
                break;


            }
            
            if ($_GET['action'] == "new") {
                $_POST['qsnID'] = $db->insert_id();
            }

            if (($_POST['RQvalNUMBType'] == '1' || $_POST['RQvalNUMBType'] == '2') && isset($_POST['qsnID']) && $_POST['qsnID'] > 0) {

                if ($_GET['action'] == "edit") {
                    $deleteOldOptionsQS = sprintf("UPDATE tblOptions SET sysOpen = '2' WHERE questionID = '%d'", (int) $_POST['qsnID']);
                    $deleteOldOptionsQry = $db->query($deleteOldOptionsQS);
                }

                foreach ($_POST['RQvalALPHOption'] as $k => $v) {
                    if (strip_tags(trim($v)) != '') {

                        $insertOptionQS = sprintf("INSERT INTO tblOptions (label, value, questionID) VALUES ('%s', '%d', '%d')",
                            $db->escape(strip_tags(trim($v))),
                            (int) $_POST['RQvalALPHOptionValues'][$k],
                            (int) $_POST['qsnID']);
                        $insertOptionQry = $db->query($insertOptionQS);
                    }
                }
            }

            if (isset($_GET['step']) && $_GET['step'] == '2') {
                header("Location: /configure-question?step=3&qnrID=".$_GET['qnrID'].'&jobID='.$_GET['jobID']);
            } elseif (isset($_GET['step']) && $_GET['step'] == '3') {
                //header("Location: /applications");
                header("Location: /new-job-info?jobID=".$_GET['jobID']."&step=4");
                //rediect to confirmation page and explanation page instead
               // header("Location: /questionnaire-complete?qnrID=".$_GET['qnrID']);
            } else {
                header("Location: /questionnaires?qnrID=".$_GET['qnrID']);

            }
            
        } else {
            $error_message = "Error: Please review the following fields:<ul>$message</ul>";
        }
    }
    
    // MANAGEMENT OF EDITED PRE-EXISTING QUESTIONNAIRES 
    else if (isset($_POST["submitEditQs"])) {
    
	    /* Update edited questions from pre-existing job and questionnaire */
/* 	    header('Location: /applications?success=Job+edited=successfully'); */

		if ($_GET['editStep'] == 2) {
			
			$editedQuestions = array();
			
			$y = 0;
			foreach ($_POST as $id=>$value) {
				
				if (strpos($id, "QvalALPHQuestion") != false) {
					$editedQuestions[$id] = $value;
					$editedQuestions[$y] = $_POST['idealValues'][$y];
					$y += 1;
				}
				
			}
			
		}

		if ($_GET['editStep'] == 3) {
		
        	echo alert_box("<strong>Success!</strong> Your job has been edited!", 1);
			// Unseralize array passed with all questions
			$serializedEdits = $_REQUEST["editedQuestions"]; 
			$editedQuestions = unserialize(stripslashes($serializedEdits));  	
						
			foreach ($_POST as $id=>$value) {
				if (strpos($id, "QvalALPHQuestion") != false) {
					$editedQuestions[$id] = $value;

				}
			}
			
			$y = 0; 
			
			//**** Process all of that stuff here ****//
			foreach ($editedQuestions as $id=>$label) {
			
				
				
				$explodedId = explode("_", $id);   // then actual $id = $explodedId[1], action = $explodedId[2])
				
				
				if (strpos($id, "_3") != false) {
					/* Sending slider ideal values  to ajax */
					$idealVal = $editedQuestions[$y];
					$y += 1;
				}
				else {
					$idealVal = 0;
				}
				
				?>
				
				<script> ajaxFunction(<?php echo $_GET['qnrID']; ?>, <?php echo "\"".$explodedId[2]."\""; ?>, <?php echo $explodedId[1]; ?>, <?php echo "\"".$explodedId[3]."\""; ?>, <?php echo "\"".$label."\""; ?>, <?php echo "\"".$idealVal."\""; ?>); </script>
				<?php
			}
			
			            
            	
			?>
			<a name="edit-question" class="btn" href="/applications?success=Job+edited=successfully" >Back to Job List</a>

			<?php
			
		}	    
    }


    // GENERAL MANAGEMENT FROM ANY PAGE REDIRECTING TO CONFIGURE-QUESTION
    if (isset($_GET['qnrID'])) {
        $getQuestionnaireDetailsQS = sprintf("SELECT * FROM tblQuestionnaires WHERE hrUserID = '%d' AND sysOpen = '1' AND sysActive = '1' AND itemID='%d' ", $_SESSION['userID'], $_GET['qnrID']);
        $getQuestionnairesDetailsQry = $db->query($getQuestionnaireDetailsQS);
        if (is_resource($getQuestionnairesDetailsQry)) {
            if ($db->num_rows($getQuestionnairesDetailsQry) > 0) {
                $qnr = $db->fetch_assoc($getQuestionnairesDetailsQry);
                $questionnaireIsValid = true;
            } else {
                $questionnaireIsValid = false;
                $feedback = "This questionnaire is no longer accessible.";
            }
        }

        if (isset($_GET['qsnID'])) {

            $_GET['action'] = "edit";

            $getQuestionDetailsQS = sprintf("SELECT * FROM tblQuestions WHERE questionnaireID = '%d' AND sysOpen = '1' AND sysActive = '1' AND itemID='%d' ",$_GET['qnrID'], $_GET['qsnID']);

            $getQuestionDetailsQry = $db->query($getQuestionDetailsQS);
            if (is_resource($getQuestionDetailsQry)) {
                if ($db->num_rows($getQuestionDetailsQry) > 0) {
                    $qsn = $db->fetch_assoc($getQuestionDetailsQry);
                    $_POST['RQvalALPHQuestion'] = $qsn['label'];
                    $_POST['RQvalNUMBType'] = $qsn['type'];
                    if ($qsn['type'] == 1 || $qsn['type'] == 2) {
                        $getOptionsQS = sprintf("SELECT * FROM tblOptions WHERE questionID = '%d' AND sysOpen ='1' AND sysActive = '1'", $_REQUEST['qsnID']);
                        //yell('print', $getOptionsQS);
                        $getOptionsQry = $db->query($getOptionsQS);
                        if ($db->valid($getOptionsQry)) {
                            $i = 1;
                            while ($opt = $db->fetch_array($getOptionsQry)) {
                                $_POST['RQvalALPHOption'][$i] = $opt['label'];
                                $_POST['RQvalALPHOptionValues'][$i] = stripslashes($opt['value']);
                                $i++;
                            }
                        }
                    }
                    $questionIsValid = true;
                } else {
                    $questionIsValid = false;
                    $feedback = "This question is no longer accessible.";
                }
            }
        }



    } else {
        $questionnaireIsValid = false;
        $feedback = "No questionnaire selected.";
    }


    if (isset($success) && $success == 1) {
        echo alert_box("<strong>Success!</strong> You have created a question!", 1);
    } elseif (isset($error_message) && $error_message != '') {
        echo alert_box($error_message, 2);
    }
    
    
    if (isset($_GET['step'])) {
        
        switch($_GET['step']) {
            
            case '2':
                echo alert_box('<h2>Tips</h2><p>When applying the applicant will be shown this list and enter the years experience they have in each skill</p><p>The information can be used to compare and rate applications.', 3);
                break;
            case '3':
                echo alert_box('<h2>Tips</h2><p>Choose a question from the drop down menu or add your own questions</p><p>The answers will be recorded by the applicant using their web cam</p><p>Watch the answers from the applicants profile</p><p>Each question has a 2 minute limit</p>', 3);
                break;
            
        }
        
    }
    ?>
    
    
	<!--- Prevent submission of form on enter press --->
    <body OnKeyPress="return disableKeyPress(event)">
    
    <!--- IF 'STEP' is set to a particular step, display the create a new question wizard; else, edit ! --->
    <?php if (isset($_GET['step'])) { ?>
    <div class="colASplit">
	    <h4>New Job: <?php echo $qnr['label']; ?></h4>
	    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <table id="configure" class="simpleTable">
            <?php 
            if (isset($_GET['step']) && $_GET['step'] == 2){
            	 echo "<tr><th colspan=\"2\">Please Enter Required Skills and Ideal Years of Experience You Are Looking For</th></tr>";
            }else{
               echo "<tr><th colspan=\"3\">Enter Your Questions</th></tr>";
            }
            $label = 'Question';
            $type  = '4';

            if (isset($_GET['step']) && $_GET['step'] == '2') {
                $label = 'How many years experience&hellip;';
                $type  = '3';
                
                $placeholders = array( 1 => "Data Entry", 2 => "Sales", 3 => "Budgeting", 4 => "Cold Calling", 5 => "Customer Service" );
                for($rowCount = 1; $rowCount <= 5; ++$rowCount){ 
                ?>
                <tr>
	                <td colspan="2">
	                    <div class="sliderText"><input size="75" type="text" autocomplete="off" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_<?php echo $rowCount; ?>" placeholder="Example: <?php echo $placeholders[$rowCount]; ?>" value="<?php echo (isset($_POST['RQvalALPHQuestion'][0])) ? $_POST['RQvalALPHQuestion'][0] : ''; ?>" /></div><div class="experienceSlider"><label for="idealSlider">Ideal Years of Experience  </label><span id="idealValue_<?php echo $rowCount; ?>">15</span>
	                    <input size="10" name="idealValues[]" type="hidden" id="hiddenIdealValue_<?php echo $rowCount; ?>" value="<?php echo (isset($_POST['idealValues'][0])) ? $_POST['idealValues'][0] : '15'; ?>"/></br>
	                    <div class="idealSlider" id="idealSlider_<?php echo $rowCount; ?>" data-count="<?php echo $rowCount; ?>" data-value="15"></div></div>
	                    <a href="#" data-count="<?php echo $rowCount; ?>" class="removeSkillQ btn">x</a>
	                    <?php if (isset($_GET['step']) && $_GET['step'] == 3){
	                    		echo "<a href=\"#\" data-count=\""+($rowCount - 1)+"\" data-label=\"".$label."\" class=\"add btn blue\">Add Another Skill</a>";
	                    	}
	                    ?>
	                    <input type="hidden" id="RQvalNUMBType" name="RQvalNUMBType" value="<?php echo $type; ?>" />
	                </td>        
                </tr>    
            <?php
            	   }
            }
            
           /*  else if (isset($_GET['step']) && $_GET['step'] == '5') { */
            
            	// Display all questions from this questionnaire for this job
            	// with editable text boxes
            	
            	/* Query the database and return an array of all questions */
/*
            	$questionQry = sprintf("SELECT %s FROM %s WHERE %s = '%d'", "label", "tblQuestions", "questionnaireID", (int) $_GET['qnrID']);
            	$questionRS = mysql_query($questionQry);
            	$questionArray = mysql_fetch_array($questionQry);
            	print_r($questionArray);	
/*
*/
/*				if ($questionArray == null) {
					
					// Alert that no questions exist
					
				}
				else {
					
					// For each one, list etc.
					foreach ($questionArray as $)
				}
*/

/*
           ?>
            	<tr>
            	<td colspan="2">
            		
            	
            	
           <?php	
*/
       /*      } */
            
            else if (isset($_GET['step']) && $_GET['step'] == '3') { 
	            
	            for ($i = 1; $i < 5; $i++) {
            ?>
            
    
		            <tr>
		                <td><label><?php echo $label; ?></label></td> 
		                <td colspan="2">
		                    <input size="75" type="text" autocomplete="off" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_<?php echo $i; ?>" placeholder="Select a default question, or create your own." value="<?php echo (isset($_POST['RQvalALPHQuestion'][0])) ? $_POST['RQvalALPHQuestion'][0] : ''; ?>" /> 
		                    
			                <!--- JS dropdown menu ---->
			                <select class="DefaultQs_<?php echo $i; ?>" name="Generic Questions" style="width:400px;">
			                	<option>Optionally select a default question.</option>
								<option value="fiveYearPlan">What are your goals and objectives for the next five years?</option>
								<option value="careerGoals">How do you plan to achieve your career goals?</option>
								<option value="rewarding">What do you find most rewarding in your career?</option>
								<option value="chooseCareer">Why did you choose the career for which you are in?</option>
								<option value="strengthWeakness">What are your strengths, weaknesses, and interests?</option>
								<option value="professorDescribe">How do you think a friend or professor who knows you well would describe you?</option>
								<option value="difficultPerson">Describe how you handle working with a difficult person?</option>
								<option value="greatestEffort">What motivates you to put forth your greatest effort? Describe a situation in which you did so.</option>
								<option value="evaluateSuccess">How do you determine or evaluate success?</option>
								<option value="contributionOrganization">In what ways do you think you can make a contribution to our organization?</option>
								<option value="contributionProject">Describe a contribution you have made to a project on which you worked.</option>
								<option value="successfulManager">What qualities should a successful manager/leader/supervisor/etc. possess?</option>
								<option value="occasionDisagree">Describe how you handle an occasion when you disagree with a supervisor's decision?</option>
								<option value="threeAccomplishments">What two or three accomplishments have given you the most satisfaction? Why?</option>
								<option value="workEnvironment">In what kind of work environment are you most comfortable?</option>
								<option value="underPressure">How do you work under pressure?</option>
								<option value="teamEnvironment">What role do you best fit in when working in a team environment? Why?</option>
								<option value="seekPosition">Why did you decide to seek a position with our organization?</option>
								<option value="threeImporatnt">What two or three things would be most important to you in your job?</option>
								<option value="evaluateOrganization">What criteria are you using to evaluate the organization for which you hope to work?</option>
								<option value="relocationConstraints">How would you view needing to relocate for the job? Do you have any constraints on relocation?</option>
								<option value="travelAmount">Are you comfortable with the amount of travel this job requires?</option>
								<option value="sixMonths">Are you willing to spend at least six months as a trainee?</option>
							</select>    
							<a href="#" data-count="<?php echo $i; ?>" class="removeDropDown btn" id="removeDD_<?php echo $i; ?>">x</a>
		                    <input type="hidden" id="RQvalNUMBType" name="RQvalNUMBType" value="<?php echo $type; ?>" />
		                </td>
		            </tr>
   <?php 
   			} ?>
   			
   			<tr>
                <td><label><?php echo $label; ?></label></td> 
                <td colspan="2">
                    <input size="75" autocomplete="off" type="text" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_5" placeholder="Select a default question, or create your own." value="<?php echo(isset($_POST['RQvalALPHQuestion'][0])) ? $_POST['RQvalALPHQuestion'][0] : ''; ?>" /> 
                    
	                <!--- JS dropdown menu ---->
	                <select class="DefaultQs_5" name="Generic Questions" style="width:400px;">
	                	<option>Optionally select a default question.</option>
						<option value="fiveYearPlan">What are your goals and objectives for the next five years?</option>
						<option value="careerGoals">How do you plan to achieve your career goals?</option>
						<option value="rewarding">What do you find most rewarding in your career?</option>
						<option value="chooseCareer">Why did you choose the career for which you are in?</option>
						<option value="strengthWeakness">What are your strengths, weaknesses, and interests?</option>
						<option value="professorDescribe">How do you think a friend or professor who knows you well would describe you?</option>
						<option value="difficultPerson">Describe how you handle working with a difficult person?</option>
						<option value="greatestEffort">What motivates you to put forth your greatest effort? Describe a situation in which you did so.</option>
						<option value="evaluateSuccess">How do you determine or evaluate success?</option>
						<option value="contributionOrganization">In what ways do you think you can make a contribution to our organization?</option>
						<option value="contributionProject">Describe a contribution you have made to a project on which you worked.</option>
						<option value="successfulManager">What qualities should a successful manager/leader/supervisor/etc. possess?</option>
						<option value="occasionDisagree">Describe how you handle an occasion when you disagree with a supervisor's decision?</option>
						<option value="threeAccomplishments">What two or three accomplishments have given you the most satisfaction? Why?</option>
						<option value="workEnvironment">In what kind of work environment are you most comfortable?</option>
						<option value="underPressure">How do you work under pressure?</option>
						<option value="teamEnvironment">What role do you best fit in when working in a team environment? Why?</option>
						<option value="seekPosition">Why did you decide to seek a position with our organization?</option>
						<option value="threeImporatnt">What two or three things would be most important to you in your job?</option>
						<option value="evaluateOrganization">What criteria are you using to evaluate the organization for which you hope to work?</option>
						<option value="relocationConstraints">How would you view needing to relocate for the job? Do you have any constraints on relocation?</option>
						<option value="travelAmount">Are you comfortable with the amount of travel this job requires?</option>
						<option value="sixMonths">Are you willing to spend at least six months as a trainee?</option>
					</select>    
					<a href="#" data-count="5" class="removeDropDown btn" id="removeDD_5">x</a>
                    <input type="hidden" id="RQvalNUMBType" name="RQvalNUMBType" value="<?php echo $type; ?>" />
                </td>
            </tr>
   
    <?php		} 
   else { ?>
            <tr>
                <td><label>Question</label></td>
                <td colspan="2"><input size="80" type="text" name="RQvalALPHQuestion" id="RQvalALPHQuestion" value="<?php echo (isset($_POST['RQvalALPHQuestion'])) ? $_POST['RQvalALPHQuestion'] : ''; ?>" /	></td>
            </tr>

            <tr>
                <td><label>Type</label></td>
                <td colspan="2">
                    <select name="RQvalNUMBType" id="RQvalNUMBType" >
                        <?php /*
                        <option <?php if($_POST['RQvalNUMBType'] == 1) echo "checked='checked'"; ?> value="1">Radio (Single Answer)</option>
                        <option <?php if($_POST['RQvalNUMBType'] == 2) echo "checked='checked'"; ?> value="2">Checkbox (Multiple Answers)</option>
                        */ ?>
                        <option <?php if (isset($_POST['RQvalNUMBType']) && $_POST['RQvalNUMBType'] == 3) echo "checked='checked'"; ?> value="3">Years of Experience Slider (1-20)</option>
                        <option <?php if (isset($_POST['RQvalNUMBType']) && $_POST['RQvalNUMBType'] == 4) echo "checked='checked'"; ?> value="4">Video Response</option>
                        <?php /*
                        <option <?php if($_POST['RQvalNUMBType'] == 5) echo "checked='checked'"; ?> value="5">File Upload</option>
                        */ ?>
                    </select>
                </td>
            </tr>
            <?php /*
            <tr class="option-row">
                <td><label>Options</label></td>
                <td><input size="70" type="text" name="RQvalALPHOption[1]" id="RQvalALPHOption_1" value="<?php echo $_POST['RQvalALPHOption'][1]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[1]" id="RQvalALPHOptionValues_1" value="<?php echo $_POST['RQvalALPHOptionValues'][1]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[2]" id="RQvalALPHOption_2" value="<?php echo $_POST['RQvalALPHOption'][2]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[2]" id="RQvalALPHOptionValues_2" value="<?php echo $_POST['RQvalALPHOptionValues'][2]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[3]" id="RQvalALPHOption_2" value="<?php echo $_POST['RQvalALPHOption'][3]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[3]" id="RQvalALPHOptionValues_2" value="<?php echo $_POST['RQvalALPHOptionValues'][3]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[4]" id="RQvalALPHOption_4" value="<?php echo $_POST['RQvalALPHOption'][4]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[4]" id="RQvalALPHOptionValues_4" value="<?php echo $_POST['RQvalALPHOptionValues'][4]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[5]" id="RQvalALPHOption_5" value="<?php echo $_POST['RQvalALPHOption'][5]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[5]" id="RQvalALPHOptionValues_5" value="<?php echo $_POST['RQvalALPHOptionValues'][5]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[6]" id="RQvalALPHOption_6" value="<?php echo $_POST['RQvalALPHOption'][6]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[6]" id="RQvalALPHOptionValues_6" value="<?php echo $_POST['RQvalALPHOptionValues'][6]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[7]" id="RQvalALPHOption_7" value="<?php echo $_POST['RQvalALPHOption'][7]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[7]" id="RQvalALPHOptionValues_7" value="<?php echo $_POST['RQvalALPHOptionValues'][7]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[8]" id="RQvalALPHOption_8" value="<?php echo $_POST['RQvalALPHOption'][8]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[8]" id="RQvalALPHOptionValues_8" value="<?php echo $_POST['RQvalALPHOptionValues'][8]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[9]" id="RQvalALPHOption_9" value="<?php echo $_POST['RQvalALPHOption'][9]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[9]" id="RQvalALPHOptionValues_9" value="<?php echo $_POST['RQvalALPHOptionValues'][9]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[10]" id="RQvalALPHOption_10" value="<?php echo $_POST['RQvalALPHOption'][10]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[10]" id="RQvalALPHOptionValues_10" value="<?php echo $_POST['RQvalALPHOptionValues'][10]; ?>" /></td>
            </tr>
            */ ?>
            <?php } ?>
            <tr>
                <?php if (isset($_GET['step'])) { ?>
                <!-- <td></td> -->
                <?php } else { ?>
                <td></td>
                <?php } ?>
                <td colspan="2">
                    <div class="submitWrap">
                        <?php if(isset($_GET['step']) && $_GET['step'] == 2 ){ ?>
                             <a href="#" data-count="<?php echo $rowCount;?>" data-label="<?php echo $label; ?>" class="add btn blue addButtonStep2">Add Another Skill</a>
                    	   <?php } 
	                    	   else if (isset($_GET['step']) && $_GET['step'] == 3) { ?>
                	   				<a href="#" data-count="5" data-label="<?php echo $label; ?>" class="add_dropdown_q btn blue">Add Another Question</a>
	                    	<?php   }
                    	   ?>
                    	   <a name="configure-question" class="btn grey" href="/configure-question?qnrID=<?php echo $_REQUEST['qnrID']; ?>" >Reset</a>
                        <input type="submit" value="Save<?php if (isset($_GET['step']) && ($_GET['step'] == '2' || $_GET['step'] == '3')) { echo ' &amp; continue'; } ?>" name="configure-question" class="btn green noEnterSubmit" />
                  </div>
                </td>
            </tr>
        </table>

        <input type="hidden" name="action" id="action" value="<?php echo $_GET['action']; ?>" />
        <input type="hidden" name="qnrID" id="qnrID" value="<?php echo $_GET['qnrID']; ?>" />
        <input type="hidden" name="qsnID" id="qsnID" value="<?php echo (isset($_GET['qsnID'])) ? $_GET['qsnID'] : 0; ?>" />
    </form>

    <?php 
    
    } 
    
    // EDIT an existing questionnaire 
    else if ($_GET['editStep'] == 1 || $_GET['editStep'] == 2) {
	    
	    ?>
	    <h4>Edit <?php echo ($_GET['editStep'] == 1) ? "Skills" : "Questions"; ?> for Job: <?php echo $qnr['label']; ?></h4>
	    <form action="/configure-question?editStep=<?php echo $_GET['editStep'] + 1; ?>&jobID=<?php echo $_GET['jobID']; ?>&qnrID=<?php echo $_GET['qnrID']; ?>" method="post">
        <table id="configure" class="simpleTable">
	    
	    <?php
			
			// Return all questions corresponding to the questionnaire in the database
			$allQuestions = array();
			$selectQQry = sprintf("SELECT * FROM tblQuestions WHERE questionnaireID = '%d' AND sysOpen = '1' AND sysActive='1'", $_GET['qnrID']);
			$selectQRS = $db->query($selectQQry);
	    
			
			
		    if (is_resource($selectQRS)) {
			    
			    if ($db->num_rows($selectQRS) > 0) {
				    while ($selectQ = $db->fetch_assoc($selectQRS)) {
					    $allQuestions[$selectQ['itemID']] = $selectQ['label'];
				    }
			    }
			    else {
				    // No questions
				    echo "<tr><td>No Questions To Edit.</td></tr>";
			    }
		    }
		    else {
			    // No questions
			    echo "<tr><td>No Questions To Edit.</td></tr>";
		    }
			
			$finalID = 0;

			foreach ($allQuestions as $qID => $qLabel) {
				
				// Get type of question by question ID
				// THESE ARE ENCAPSULATED IN JOBMANAGER.PHP
				// (But can't access them w/o a jobmanager object...
				$selectQQry = sprintf("SELECT type, idealValue FROM tblQuestions where itemID='%d'", (int)$qID); 
				$selectQRS = $db->query($selectQQry);
				$qType = "";
				
				$finalID = $qID; // will eventually be set to last ID

			    if (is_resource($selectQRS)) {
				    if ($db->num_rows($selectQRS) > 0) {
					    $selectQType = $db->fetch_assoc($selectQRS);
						
						switch ($selectQType['type'])  {
							
							case 1:
								$qType = "Radio";
								break;
								
							case 2:
								$qType = "Checkbox";
								break;
							
							case 3:
								$qType = "Slider";
								break;
							
							case 4:
								$qType = "Video";
								break;
							
							case 5:
								$qType = "File";
								break;
						}
			
				    }
				    else {
				    	// Type not set!
					    $qType = "Undefined";
				    }
				 }
				 else {
					 // Type not set - or ERROR!
					 $qType = "Undefined";
				 }

				if ($_GET['editStep'] == 1) {
					// Display slider questions	
					if (strcmp($qType, "Slider") == 0) { 
						
						$idealVal = $selectQType['idealValue'];

					?>
						<tr>						
					    	<!--<td width="30%"><label for="RQvalALPHQuestion_<?php echo $qID; ?>">How Many Years Experience...</label></td>-->
							<td colspan="2"><div class="sliderText"><input size="75" type="text" autocomplete="off" class="<?php echo $qID; ?>" name="RQvalALPHQuestion_<?php echo $qID; ?>_edit_3" value="<?php echo $qLabel; ?>"/></div><div class="experienceSlider"><label for="idealSlider">Ideal Years of Experience  </label><span id="idealValue_<?php echo $qID; ?>"><?php echo $idealVal; ?></span><input size="10" name="idealValues[]" type="hidden" id="hiddenIdealValue_<?php echo $qID; ?>" value="<?php echo $idealVal; ?>"/></br><div class="idealSlider" id="idealSlider_<?php echo $qID; ?>" data-count="<?php echo $qID; ?>" data-value="<?php echo $idealVal; ?>"></div></div>
							<td width="5%"><a href="#" data-type="3" id="<?php echo $qID; ?>" class="removeQuestion btn"> x</a></td>
						</tr>								
					<?php
					}
					else { 
						continue;
					}
				}
				else {

					// Display video questions with drop down option
					if (strcmp($qType, "Video") == 0) { ?>
						<tr>
					    	<td><label for="RQvalALPHQuestion_<?php echo $qID; ?>">Question</label></td>
							<td colspan="2"><input size="75" type="text" autocomplete="off" class="<?php echo $qID; ?>" name="RQvalALPHQuestion_<?php echo $qID; ?>_edit_4" value="<?php echo $qLabel; ?>"/><br><select class="DefaultQs_<?php echo $qID; ?>" name="Generic Questions" style="width:400px;"><option>Optionally select a default question.</option><option value="fiveYearPlan">What are your goals and objectives for the next five years?</option><option value="careerGoals">How do you plan to achieve your career goals?</option><option value="rewarding">What do you find most rewarding in your career?</option><option value="chooseCareer">Why did you choose the career for which you are in?</option><option value="strengthWeakness">What are your strengths, weaknesses, and interests?</option><option value="professorDescribe">How do you think a friend or professor who knows you well would describe you?</option><option value="difficultPerson">Describe how you handle working with a difficult person?</option><option value="greatestEffort">What motivates you to put forth your greatest effort? Describe a situation in which you did so.</option><option value="evaluateSuccess">How do you determine or evaluate success?</option><option value="contributionOrganization">In what ways do you think you can make a contribution to our organization?</option><option value="contributionProject">Describe a contribution you have made to a project on which you worked.</option><option value="successfulManager">What qualities should a successful manager/leader/supervisor/etc. possess?</option><option value="occasionDisagree">Describe how you handle an occasion when you disagree with a supervisor\'s decision?</option><option value="threeAccomplishments">What two or three accomplishments have given you the most satisfaction? Why?</option><option value="workEnvironment">In what kind of work environment are you most comfortable?</option><option value="underPressure">How do you work under pressure?</option><option value="teamEnvironment">What role do you best fit in when working in a team environment? Why?</option><option value="seekPosition">Why did you decide to seek a position with our organization?</option><option value="threeImporatnt">What two or three things would be most important to you in your job?</option><option value="evaluateOrganization">What criteria are you using to evaluate the organization for which you hope to work?</option><option value="relocationConstraints">How would you view needing to relocate for the job? Do you have any constraints on relocation?</option><option value="travelAmount">Are you comfortable with the amount of travel this job requires?</option><option value="sixMonths">Are you willing to spend at least six months as a trainee?</option></select> 
							<a href="#" data-type="4" id="<?php echo $qID; ?>" class="removeQuestion btn"> x</a></td>
						</tr>								
					<?php
					}
					else { 
						continue;
					}
					
				}
			}
			
			// Insert submit/edit/continue button
			?>
			<td colspan="100">
                <div class="submitWrap">
                <?php 			
					// Serialize the array & pass to step 2
					if ($_GET['editStep'] == 2) {
						
						$serializedEdits = serialize($editedQuestions); 
					?>
						<input type="hidden" name="editedQuestions" value='<?php echo $serializedEdits; ?>'/>	
					
					<?php
					}
					?>
                  <input type="hidden" name="submitEditQs" value="true"/>
<!--               	  <a name="edit-question" class="btn grey" href="/edit-job?id=<?php echo $_GET['jobID']; ?>" >Back</a>  -->                  
              	  <input type="submit" value="<?php echo ($_GET['editStep'] == 1) ? "Save & Continue" : "Save"; ?>" name="submit-question-edits" class="btn green noEnterSubmit" />
              	  <?php echo ($_GET['editStep'] == 1) ? "<a href='#' data-type='3' data-id='".$finalID."' class='btn blue addEditQuestion'> Add New Question</a>" : "<a href='#' data-type='4' data-id='".$finalID."' class='btn blue addEditQuestion'> Add New Question</a>";?>
                </div>
            </td>
			
			<?php					
	
    }
    ?>
    </table>
    </form>
	</div><!-- colASplit -->
    <script type="text/javascript">

    </script>
<?php
    global $quipp;
    $quipp->js['footer'][] = "/includes/apps/questionnaires/js/questionnaires.js";
}
