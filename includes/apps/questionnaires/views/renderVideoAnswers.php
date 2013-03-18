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

    <?php

    if (is_array($q->questions) && !empty($q->questions)) {
    
    	$videoNavThumbs = "";
    
    	echo '<div id="video-answers">';
        $z = 1;
        foreach ($q->questions as $questionID => $question) {

            $answer = $q->getAnswer($questionID, $application['userID']);
            
            
            
            if ($question['type'] == 4) {
                
                
                $video   = $db->return_specific_item('', 'tblVideos', 'filename', 0, "jobID='" . (int) $application['jobID'] . "' AND questionID='" . $questionID . "' AND userID='" . (int) $application['userID'] . "' AND sysOpen='1' AND sysActive='1'") ;
                $videoID = $db->return_specific_item('', 'tblVideos', 'itemID', 0, "jobID='" . (int) $application['jobID'] . "' AND questionID='" . $questionID . "' AND userID='" . (int) $application['userID'] . "' AND sysOpen='1'") ;
                
                if ($z==1) {
                	$style='';
                } else {
	                $style='style="display:none;"';
                }
                
                echo '<div class="answer-box" '.$style.'>';
                echo '<p><strong>Question '.$z.':</strong> ' . $question['label'] . '</p>';
                
                if ($video !== 0) {
                ?>

                    <embed src="/includes/apps/ams-media/flx/captureModule.swf" quality="high" bgcolor="#000000" width="550" height="400" name="captureModule" FlashVars="reviewFile=<?php echo $video; ?>" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflash" />

                <?php
                } else {
                    // default image here?
                    echo '<div class="no-video-box">Question Not Answered</div>';
                }
                
                $videoNavThumbs .= '<div data-vidnumber="'.$z.'" class="video-thumbnail"></div>';
                
                echo '</div>';
                $z++;
                
            }
            

        }
        echo '</div>'; //video-answers
        
        ?>
        
        <div id="video-answer-nav">
	        <input type="button" id="va-prev" class="btn red" value="Previous" />
	        <input type="button" id="va-next" class="btn green" value="Next" />
        </div>
        
        <div id="video-thumbnail-nav">
        	<?php echo $videoNavThumbs; ?>
        </div>
        
        <?php
        
    } else {
        $quipp->js['onload'] .= 'alertBox("fail", "This application has no questions");';
    }

?>
