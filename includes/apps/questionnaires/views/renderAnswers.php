<?php

global $quipp;

if (!class_exists('Questionnaire')) {
    require dirname(__DIR__) . '/Questionnaire.php';
} else {
    $application['userID'] = $_SESSION['userID'];
    $application['jobID']  = (int) $_GET['job'];
}
$j = new JobManager($db, $application['userID']);

    list($title, $link, $dateExpires, $datePosted, $questionnaireID, $status) = $j->getJob($application['jobID']);

    $q = new Questionnaire($db, $questionnaireID);

?>

    <table class="simpleTable">
        <thead>
        <tr><th><?php echo $title; ?></th></tr></thead>
    <?php

    if (is_array($q->questions) && !empty($q->questions)) {
        foreach ($q->questions as $questionID => $question) {

            $answer = $q->getAnswer($questionID, $application['userID']);

            echo "<tr>";
            echo "<td><strong>";
            echo $question['label'];

            echo "</strong></td>";
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
                                $checked = (isset($answer['optionID']) && $answer['optionID'] == $opt['itemID']) ? ' checked="checked"' : '';
                                echo '<input type="radio" id="' . $id . '"  name="' . $name . '"  value="' . $opt['itemID'] . '"' . $checked . ' disabled />';
                            } else {

                                $checked = (isset($answer[$optionID])) ? ' checked="checked"' : '';

                                echo '<input type="checkbox" id="' . $id . '"  name="' . $name . '[]"  value="' . $opt['itemID'] . '"' . $checked . ' disabled />';
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
                    $val = (isset($answer['value'])) ? $answer['value'] : 0;
                    echo "<div class=\"slider\" rel=\"$name\" alt='".$val."'></div><input type=\"hidden\" name=\"$name\" id=\"$id\" value=\"".$val."\" /><div class='sliderValueHolder' rel='$id'>".$val."/20</div>";

                break;

                case 4: //video

                    $video   = $db->return_specific_item('', 'tblVideos', 'filename', 0, "jobID='" . (int) $application['jobID'] . "' AND questionID='" . $questionID . "' AND userID='" . (int) $application['userID'] . "' AND sysOpen='1' AND sysActive='1'") ;
                    $videoID = $db->return_specific_item('', 'tblVideos', 'itemID', 0, "jobID='" . (int) $application['jobID'] . "' AND questionID='" . $questionID . "' AND userID='" . (int) $application['userID'] . "' AND sysOpen='1'") ;

                    if ($video !== 0) {

                    ?>

                        <embed src="/includes/apps/ams-media/flx/captureModule.swf" quality="high" bgcolor="#000000" width="550" height="400" name="captureModule" FlashVars="reviewFile=<?php echo $video; ?>" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflash" />

                    <?php
                    } else {
                        echo '<i>Question not answered</i>';
                    }

                break;
                case 5: //file

                    if (isset($answer['value'])) {
                        echo '<a href="/uploads/applications/' . (int) $application['jobID'] . '/' . (int) $application['userID'] . '/' . $answer['value'] . '" class="' . pathinfo($answer['value'], PATHINFO_EXTENSION) . '">Download file</a>';
                    } else {
                        echo '<strong>No file was uploaded</strong>';
                    }
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
