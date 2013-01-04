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
    <?php

    if (is_array($q->questions) && !empty($q->questions)) {
        $z = 1;
        echo "<tr>";
        foreach ($q->questions as $questionID => $question) {

            $answer = $q->getAnswer($questionID, $application['userID']);
            
            
            
            if ($question['type'] == 4) {
                
                if ($z == 4) {
                    echo '</tr><tr>';
                    $z = 1;
                }
                
                $video   = $db->return_specific_item('', 'tblVideos', 'filename', 0, "jobID='" . (int) $application['jobID'] . "' AND questionID='" . $questionID . "' AND userID='" . (int) $application['userID'] . "' AND sysOpen='1' AND sysActive='1'") ;
                $videoID = $db->return_specific_item('', 'tblVideos', 'itemID', 0, "jobID='" . (int) $application['jobID'] . "' AND questionID='" . $questionID . "' AND userID='" . (int) $application['userID'] . "' AND sysOpen='1'") ;
                
                echo '<td>';
                if ($video !== 0) {

                ?>

                    <embed src="/includes/apps/ams-media/flx/captureModule.swf" quality="high" bgcolor="#000000" width="550" height="400" name="captureModule" FlashVars="reviewFile=<?php echo $video; ?>" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflash" />

                <?php
                } else {
                    // default image here?
                    echo '<i>Question not answered</i>';
                }
                echo '<p>' . $question['label'] . '</p>';
                echo '</td>';
                $z++;
                
            }
            

        }
        echo "</tr>";
    } else {
        $quipp->js['onload'] .= 'alertBox("fail", "This application has no questions");';
    }

?>
    </table>
