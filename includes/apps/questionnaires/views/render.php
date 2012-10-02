<?php

global $quipp;

require dirname(dirname(__DIR__)) . '/jobs-manager/JobManager.php';
require dirname(__DIR__) . '/Questionnaire.php';

$j = new JobManager($db, $_SESSION['userID']);

list($title, $link, $dateExpires, $datePosted, $questionnaireID, $status) = $j->getJob($_GET['job']);

if (time() < strtotime($datePosted) || $status == 'inactive') {
    echo alert_box('<strong>Error</strong>. No job found', 2);

} elseif (time() > strtotime($dateExpires)) {
    echo alert_box('<strong>Warning</strong>. We\'re sorry, this job posting has expred.', 3);
} elseif ($j->hasApplied($_GET['job'])) {

    echo '<div class="payment-errors" style="display:block"><strong>You have already applied</strong></div>';
    include __DIR__ . '/renderAnswers.php';

} else {
    $q = new Questionnaire($db, $questionnaireID);
    $quipp->js['footer'][] = "/includes/apps/questionnaires/js/questionnaires.js";

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
        }

    }

    if (isset($error) && $error != '') {
            echo alert_box($error, 2);

    }

?>

<form id="job-form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
    <table class="simpleTable">
    <tr><th><?php echo $title; ?></th></tr>
    <?php

    if (is_array($q->questions) && !empty($q->questions)) {
        foreach ($q->questions as $questionID => $question) {
            echo "<tr>";
            echo "<td>";
            echo $question['label'];
            echo "</td>";
            echo "</tr>";

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

                    $video   = $db->return_specific_item('', 'tblVideos', 'filename', 0, "jobID='" . (int) $_GET['job'] . "' AND questionID='" . $questionID . "' AND userID='" . (int) $_SESSION['userID'] . "' AND sysOpen='1' AND sysActive='1'") ;
                    $videoID = $db->return_specific_item('', 'tblVideos', 'itemID', 0, "jobID='" . (int) $_GET['job'] . "' AND questionID='" . $questionID . "' AND userID='" . (int) $_SESSION['userID'] . "' AND sysOpen='1'") ;

                    if ($video !== 0) {

                    ?>

                        <embed src="/includes/apps/ams-media/flx/captureModule.swf" quality="high" bgcolor="#000000" width="550" height="400" name="captureModule" FlashVars="reviewFile=<?php echo $video; ?>" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflash" />

                    <?php
                    } else {

                        if ($videoID == 0) {
                            $qry = sprintf("INSERT INTO tblVideos (userID, jobID, questionID, filename, sysDateInserted, sysDateLastMod) VALUES ('%d', '%d', '%d', '', NOW(), NOW())",
                                (int) $_SESSION['userID'],
                                (int) $_GET['job'],
                                $questionID);
                            $db->query($qry);
                            $videoID = $db->insert_id();
                        }
                        echo '<embed src="/includes/apps/ams-media/flx/captureModule.swf" quality="high" bgcolor="#000000" width="550" height="400" name="captureModule" FlashVars="itemID=' . $videoID . '&securityKey=' . md5("iLikeSalt" . $videoID) . '" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflash" />';

                    }

                    echo '<input type="hidden" name="' . $questionID . '" value="' . $videoID . '" />';

                break;
                case 5: //file

                    echo '<input type="file" name="' . $questionID . '" id="' . $questionID . '" />';
                break;

            }
            echo "</td>";
            echo "</tr>";

        }
    } else {
        $feedback = "This questionnaire has no questions.";
    }

?>
    </table>
    <input type="submit" class="btn green" value="Submit" />
</form>
<?php
}
