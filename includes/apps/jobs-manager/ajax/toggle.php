<?php

require dirname(dirname(dirname(__DIR__))) . '/init.php';
require dirname(__DIR__) . '/JobManager.php';

$j = new JobManager($db, $_SESSION['userID']);

if ($j->canEdit($_POST['job'])) {
    $j->toggle($_POST['job']);
    
} else {
    header('HTTP/1.0 401 Unauthorized');
}