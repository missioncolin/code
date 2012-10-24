<?php

require dirname(dirname(dirname(__DIR__))) . '/init.php';
require dirname(__DIR__) . '/JobManager.php';
require dirname(dirname(__DIR__)) . '/credits/Credits.php';

$j = new JobManager($db, $_SESSION['userID']);

if ($j->canEdit($_POST['job'])) {
    echo $j->activate($_POST['job'], $user);
    
    
} else {
    header('HTTP/1.0 401 Unauthorized');
}