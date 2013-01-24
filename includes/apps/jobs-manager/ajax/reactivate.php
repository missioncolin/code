<?php

require dirname(dirname(dirname(__DIR__))) . '/init.php';
require dirname(__DIR__) . '/JobManager.php';
require dirname(dirname(__DIR__)) . '/credits/Credits.php';

$j = new JobManager($db, $_SESSION['userID']);
$jobID = $_REQUEST['job'];

log("try it");
if ($j->canEdit($jobID)) {
    echo $j->reactivate($jobID, $user);    
    log("reactivate");
} else {
    header('HTTP/1.0 401 Unauthorized');
}