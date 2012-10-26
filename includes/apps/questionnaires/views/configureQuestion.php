<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css" />
<?php

if ($this instanceof Quipp) {
    global $message;

    if (!isset($_GET['action'])) {
        $_GET['action'] = "new";
    }
    if (!empty($_POST) && isset($_POST["configure-question"]) ) {

        if (validate_form($_POST)) {

            switch ($_GET['action']) {

            case 'new':
            
                
                if (is_array($_POST['RQvalALPHQuestions'])) {
                    
                    foreach($_POST['RQvalALPHQuestions'] as $label) {
                        $qry = sprintf("INSERT INTO tblQuestions (label, type, questionnaireID ) VALUES('%s', '%d', '%d')",
                            $db->escape(strip_tags($label)),
                            (int) $_POST['RQvalNUMBType'],
                            (int) $_GET['qnrID']);
                        $db->query($qry);
                    }
                    
                } else {
                    $qry = sprintf("INSERT INTO tblQuestions (label, type, questionnaireID ) VALUES('%s', '%d', '%d')",
                        $db->escape(strip_tags($_POST['RQvalALPHQuestion'])),
                        (int) $_POST['RQvalNUMBType'],
                        (int) $_GET['qnrID']);
                    $db->query($qry);
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
                header("Location: /new-job-info?jobID=".$_GET['jobID']);
                //rediect to confirmation page and explanation page instead
               // header("Location: /questionnaire-complete?qnrID=".$_GET['qnrID']);
            } else {
                header("Location: /questionnaires?qnrID=".$_GET['qnrID']);

            }
            
        } else {
            $error_message = "Error: Please review the following fields:<ul>$message</ul>";
        }
    }

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
                echo alert_box('On this page you can create a list of skills the job requires.  When the applicant applies they will enter the years of experience they have in each skill. Intervue will use this information to rank applicants. Note: Pressing the Tab button on your keyboard will create another question field and hitting Enter will submit the form.', 3);
                break;
            case '3':
                echo alert_box('On this page you can ask questions that will be answered by the applicant using their webcam during the application process.  Below is a list of generic questions in the dropdown menu or you can create your own questions specific to this job.', 3);
                break;
            
        }
        
    }

?>
    <h4>New Job: <?php echo $qnr['label']; ?></h4>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <table id="configure" class="simpleTable">
            <?php 
            if (isset($_GET['step']) && $_GET['step'] == 2){
            	 echo "<tr><th colspan=\"3\">Please Enter The Required Skills You Are Looking For</th></tr>";
            }else{
               echo "<tr><th colspan=\"3\">Enter Your Questions</th></tr>";
            }
            $label = 'Question';
            $type  = '4';

            if (isset($_GET['step']) && $_GET['step'] == '2') {
                $label = 'How many years experience&hellip;';
                $type  = '3';
                
                ?>
                
                <tr>
                <td><label><?php echo $label; ?></label></td> 
                <td colspan="2">
                    <input size="75" type="text" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_1" placeholder="Required Skill" value="<?php echo (isset($_POST['RQvalALPHQuestion']						[0])) ? $_POST['RQvalALPHQuestion'][0] : ''; ?>" /> 
                    <br><a href="#" data-count="1" data-label="<?php echo $label; ?>" class="add">Add Another Question</a>
                    <input type="hidden" id="RQvalNUMBType" name="RQvalNUMBType" value="<?php echo $type; ?>" />
                </td>
            </tr>
            
            <?php
            }
            else if (isset($_GET['step']) && $_GET['step'] == '3') { ?>
            <tr>
                <td><label><?php echo $label; ?></label></td> 
                <td colspan="2">
                    <input size="75" type="text" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_1" placeholder="Select a default question, or create your own." value="<?php echo (isset($_POST['RQvalALPHQuestion']						[0])) ? $_POST['RQvalALPHQuestion'][0] : ''; ?>" /> 
                    
	                <!--- JS dropdown menu ---->
	                <select class="DefaultQs_1" name="Generic Questions" style="width:400px;">
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
                <br><a href="#" data-count="1" data-label="<?php echo $label; ?>" class="add_dropdown_q">Add Another Question</a>
                    <input type="hidden" id="RQvalNUMBType" name="RQvalNUMBType" value="<?php echo $type; ?>" />
                </td>
            </tr>
            <?php } else { ?>
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
                    	<a name="configure-question" class="btn grey" href="/configure-question?qnrID=<?php echo $_REQUEST['qnrID']; ?>" >Reset</a>
                        <input type="submit" value="Save<?php if (isset($_GET['step']) && ($_GET['step'] == '2' || $_GET['step'] == '3')) { echo ' &amp; continue'; } ?>" name="configure-question" class="btn" />
                  </div>
                </td>
            </tr>
        </table>

        <input type="hidden" name="action" id="action" value="<?php echo $_GET['action']; ?>" />
        <input type="hidden" name="qnrID" id="qnrID" value="<?php echo $_GET['qnrID']; ?>" />
        <input type="hidden" name="qsnID" id="qsnID" value="<?php echo (isset($_GET['qsnID'])) ? $_GET['qsnID'] : 0; ?>" />
    </form>

    <script type="text/javascript">

    </script>
<?php
    global $quipp;
    $quipp->js['footer'][] = "/includes/apps/questionnaires/js/questionnaires.js";
}
