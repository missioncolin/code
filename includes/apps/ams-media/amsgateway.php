<?php
require '../../../includes/init.php';
print "var1=Resolution";
//$message = "AMS GATEWAY - INTERVUE FILE CAPTURED\n" . print_r($_POST, true);
//error_log($message, 3, '/resolutionDevSiteRoot/dev.log');


//$_POST['myKey'] = "hi_1_hi";

if(isset($_POST['myKey'])) {
    $videoID = false;
    
    $parts = explode("_", $_POST['myKey']);
    
    print_r($parts);
    
    $videoID = $parts[1];
    
    if(is_numeric($videoID)) {
    
    //set the video
        $qry = sprintf("UPDATE tblVideos 
        				SET 
        				    fileName = '%s', 
        					sysActive = '1' 
        				WHERE itemID = '%d'", 
        				addslashes($_POST['myKey']), 
        				(int)$videoID);
        print $qry;
        $db->query($qry);
    }
}
?>