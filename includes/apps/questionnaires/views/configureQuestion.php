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
                header("Location: /configure-question?step=3&qnrID=".$_GET['qnrID']);
            } elseif (isset($_GET['step']) && $_GET['step'] == '3') {
                header("Location: /applications");
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
                echo alert_box('During the interview you can add ‘Years of Experience’ questions that your applicant can answer. eg. How many years of experience do you have in Project Management. The applicant will supply this answer using slider controls that you can later filter against.', 3);
                break;
            case '3':
                echo alert_box('During the interview you can add multiple Video Response questions. Here you can ask general questions that you would like you applicant to answer via video. eg. What is your five year plan?', 3);
                break;
            
        }
        
    }

?>
    <h4>Questionnaire: <?php echo $qnr['label']; ?></h4>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <table id="configure" class="simpleTable">
            <tr><th colspan="3">Enter Your Questions</th></tr>
            
            <?php   
            $label = 'Question';
            $type  = '4';

            if (isset($_GET['step']) && $_GET['step'] == '2') {
                $label = 'How many years of experience do you have in';
                $type  = '3';
            }
            if (isset($_GET['step'])) { ?>
            <tr>
                <td><label><?php echo $label; ?></label></td>
                <td colspan="2">
                    <input size="75" type="text" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_1" value="<?php echo (isset($_POST['RQvalALPHQuestion'][0])) ? $_POST['RQvalALPHQuestion'][0] : ''; ?>" /> <a href="#" data-count="1" data-label="<?php echo $label; ?>" class="add">Add Another Question</a>
                    <input type="hidden" id="RQvalNUMBType" name="RQvalNUMBType" value="<?php echo $type; ?>" />
                </td>
            </tr>
            <?php } else { ?>
            <tr>
                <td><label>Question</label></td>
                <td colspan="2"><input size="80" type="text" name="RQvalALPHQuestion" id="RQvalALPHQuestion" value="<?php echo (isset($_POST['RQvalALPHQuestion'])) ? $_POST['RQvalALPHQuestion'] : ''; ?>" /></td>
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
                <td></td>
                <td colspan="2">
                    <div class="submitWrap">
                        <input type="submit" value="Save<?php if (isset($_GET['step']) && $_GET['step'] == '2') { echo ' &amp; continue'; } ?>" name="configure-question" class="btn" />
                        <a name="configure-question" class="btn grey" href="/configure-question?qnrID=<?php echo $_REQUEST['qnrID']; ?>" >Reset</a>
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
