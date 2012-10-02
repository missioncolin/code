<?php
ini_set('display_errors', 'off');
if ($this instanceof Quipp) {
    global $message;

    if (!isset($_REQUEST['action'])) {
        $_REQUEST['action'] = "new";
    }
    if (!empty($_POST) && isset($_POST["configure-question"]) ) {

        //yell('print', $_REQUEST);
        if (validate_form($_POST)) {

            switch ($_REQUEST['action']) {

            case 'new':
                $qry = sprintf("INSERT INTO tblQuestions
                                (
                                    label,
                                    type,
                                    questionnaireID
                                )VALUES(
                                    '%s',
                                    '%d',
                                    '%d'
                                )",

                    addslashes($_REQUEST['RQvalALPHQuestion']),
                    $_REQUEST['RQvalNUMBType'],
                    $_REQUEST['qnrID']

                );

                $success = 1;

                break;
            case 'edit':
                $qry = sprintf("UPDATE tblQuestions
                                    SET label = '%s',
                                        type = '%d'
                                    WHERE itemID = '%d'",
                    addslashes($_REQUEST['RQvalALPHQuestion']),
                    $_REQUEST['RQvalNUMBType'],
                    $_REQUEST['qsnID']);
                break;
            }

            $db->query($qry);
            if($_REQUEST['action'] == "new") $_REQUEST['qsnID'] = $db->insert_id();
            //yell('print', $qry);

            if ($_REQUEST['RQvalNUMBType'] == 1 || $_REQUEST['RQvalNUMBType'] == 2) {

                if ($_REQUEST['action'] == "edit") {
                    $deleteOldOptionsQS = sprintf("UPDATE tblOptions SET sysOpen = '2' WHERE questionID = '%d'", $_REQUEST['qsnID']);
                    $deleteOldOptionsQry = $db->query($deleteOldOptionsQS);
                }

                foreach ($_REQUEST['RQvalALPHOption'] as $k => $v) {
                    if (trim($v) != "") {
                        $label = addslashes(trim(clean($v)));
                        $insertOptionQS = sprintf("INSERT INTO tblOptions (label, value, questionID) VALUES ('%s', '%d', '%d')", $label, $_REQUEST['RQvalALPHOptionValues'][$k], $_REQUEST['qsnID']);
                        $insertOptionQry = $db->query($insertOptionQS);
                        //yell('print', $insertOptionQS);
                    }
                }
            }

            header("Location:/questionnaires?qnrID=".$_REQUEST['qnrID']);

        } else {
            $error_message = "Error: Please review the following fields:<ul>$message</ul>";
        }
    }

    if (isset($_REQUEST['qnrID'])) {
        $getQuestionnaireDetailsQS = sprintf("SELECT * FROM tblQuestionnaires WHERE hrUserID = '%d' AND sysOpen = '1' AND sysActive = '1' AND itemID='%d' ", $_SESSION['userID'], $_REQUEST['qnrID']);
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
    } else {
        $questionnaireIsValid = false;
        $feedback = "No questionnaire selected.";
    }

    if (isset($_REQUEST['qsnID'])) {

        $_REQUEST['action'] = "edit";

        $getQuestionDetailsQS = sprintf("SELECT * FROM tblQuestions WHERE questionnaireID = '%d' AND sysOpen = '1' AND sysActive = '1' AND itemID='%d' ",$_REQUEST['qnrID'], $_REQUEST['qsnID']);

        $getQuestionDetailsQry = $db->query($getQuestionDetailsQS);
        if (is_resource($getQuestionDetailsQry)) {
            if ($db->num_rows($getQuestionDetailsQry) > 0) {
                $qsn = $db->fetch_assoc($getQuestionDetailsQry);
                $_REQUEST['RQvalALPHQuestion'] = $qsn['label'];
                $_REQUEST['RQvalNUMBType'] = $qsn['type'];
                if ($qsn['type'] == 1 || $qsn['type'] == 2) {
                    $getOptionsQS = sprintf("SELECT * FROM tblOptions WHERE questionID = '%d' AND sysOpen ='1' AND sysActive = '1'", $_REQUEST['qsnID']);
                    //yell('print', $getOptionsQS);
                    $getOptionsQry = $db->query($getOptionsQS);
                    if ($db->valid($getOptionsQry)) {
                        $i = 1;
                        while ($opt = $db->fetch_array($getOptionsQry)) {
                            $_REQUEST['RQvalALPHOption'][$i] = $opt['label'];
                            $_REQUEST['RQvalALPHOptionValues'][$i] = stripslashes($opt['value']);
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

    if ($success == 1) {
        print alert_box("<strong>Success!</strong> You have created a question!", 1);
    } elseif (isset($error_message) && $error_message != '') {
        print alert_box($error_message, 2);
    }

?>
    <h4>Questionnaire: <?php print $qnr['label']; ?></h4>
    <form action="<?php print $_SERVER['REQUEST_URI']; ?>" method="post">
        <table id="configure" class="simpleTable">
            <tr><th colspan="2">Configure Question</th><th>Points</th></tr>
            <tr>
                <td><label>Question</label></td>
                <td colspan="2"><input size="80" type="text" name="RQvalALPHQuestion" id="RQvalALPHQuestion" value="<?php print $_REQUEST['RQvalALPHQuestion']; ?>" /></td>
            </tr>
            <tr>
                <td><label>Type</label></td>
                <td colspan="2">
                    <select name="RQvalNUMBType" id="RQvalNUMBType" >
                        <option <?php if($_REQUEST['RQvalNUMBType'] == 1) print "checked='checked'"; ?> value="1">Radio (Single Answer)</option>
                        <option <?php if($_REQUEST['RQvalNUMBType'] == 2) print "checked='checked'"; ?> value="2">Checkbox (Multiple Answers)</option>
                        <option <?php if($_REQUEST['RQvalNUMBType'] == 3) print "checked='checked'"; ?> value="3">Years of Experience Slider (1-20)</option>
                        <option <?php if($_REQUEST['RQvalNUMBType'] == 4) print "checked='checked'"; ?> value="4">Video Response</option>
                        <option <?php if($_REQUEST['RQvalNUMBType'] == 5) print "checked='checked'"; ?> value="5">File Upload</option>
                    </select>
                </td>

            </tr>
            <tr class="option-row">
                <td><label>Options</label></td>
                <td><input size="70" type="text" name="RQvalALPHOption[1]" id="RQvalALPHOption_1" value="<?php print $_REQUEST['RQvalALPHOption'][1]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[1]" id="RQvalALPHOptionValues_1" value="<?php print $_REQUEST['RQvalALPHOptionValues'][1]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[2]" id="RQvalALPHOption_2" value="<?php print $_REQUEST['RQvalALPHOption'][2]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[2]" id="RQvalALPHOptionValues_2" value="<?php print $_REQUEST['RQvalALPHOptionValues'][2]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[3]" id="RQvalALPHOption_2" value="<?php print $_REQUEST['RQvalALPHOption'][3]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[3]" id="RQvalALPHOptionValues_2" value="<?php print $_REQUEST['RQvalALPHOptionValues'][3]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[4]" id="RQvalALPHOption_4" value="<?php print $_REQUEST['RQvalALPHOption'][4]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[4]" id="RQvalALPHOptionValues_4" value="<?php print $_REQUEST['RQvalALPHOptionValues'][4]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[5]" id="RQvalALPHOption_5" value="<?php print $_REQUEST['RQvalALPHOption'][5]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[5]" id="RQvalALPHOptionValues_5" value="<?php print $_REQUEST['RQvalALPHOptionValues'][5]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[6]" id="RQvalALPHOption_6" value="<?php print $_REQUEST['RQvalALPHOption'][6]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[6]" id="RQvalALPHOptionValues_6" value="<?php print $_REQUEST['RQvalALPHOptionValues'][6]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[7]" id="RQvalALPHOption_7" value="<?php print $_REQUEST['RQvalALPHOption'][7]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[7]" id="RQvalALPHOptionValues_7" value="<?php print $_REQUEST['RQvalALPHOptionValues'][7]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[8]" id="RQvalALPHOption_8" value="<?php print $_REQUEST['RQvalALPHOption'][8]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[8]" id="RQvalALPHOptionValues_8" value="<?php print $_REQUEST['RQvalALPHOptionValues'][8]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[9]" id="RQvalALPHOption_9" value="<?php print $_REQUEST['RQvalALPHOption'][9]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[9]" id="RQvalALPHOptionValues_9" value="<?php print $_REQUEST['RQvalALPHOptionValues'][9]; ?>" /></td>
            </tr>
            <tr class="option-row">
                <td></td>
                <td><input size="70" type="text" name="RQvalALPHOption[10]" id="RQvalALPHOption_10" value="<?php print $_REQUEST['RQvalALPHOption'][10]; ?>" /></td>
                <td><input size="1" type="text" name="RQvalALPHOptionValues[10]" id="RQvalALPHOptionValues_10" value="<?php print $_REQUEST['RQvalALPHOptionValues'][10]; ?>" /></td>
            </tr>

            <tr>
                <td></td><td colspan="2"><div class="submitWrap"><input type="submit" value="Save" name="configure-question" class="btn" /><a name="configure-question" class="btn green" href="/configure-question?qnrID=<?php print $_REQUEST['qnrID']; ?>" >Add More Questions</a></div></td>
            </tr>
        </table>

        <input type="hidden" name="action" id="action" value="<?php print $_REQUEST['action']; ?>" />
        <input type="hidden" name="qnrID" id="qnrID" value="<?php print $_REQUEST['qnrID']; ?>" />
        <input type="hidden" name="qsnID" id="qsnID" value="<?php print $_REQUEST['qsnID']; ?>" />
    </form>

    <script type="text/javascript">

    </script>
<?php
    global $quipp;
    $quipp->js['footer'][] = "/includes/apps/questionnaires/js/questionnaires.js";
}
