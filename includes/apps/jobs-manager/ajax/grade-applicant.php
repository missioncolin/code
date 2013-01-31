<?php

require dirname(dirname(dirname(__DIR__))) . '/init.php';
require dirname(__DIR__) . '/JobManager.php';

$j = new JobManager($db, $_SESSION['userID']);

$application = $j->getApplication($_POST['application']);



if ($j->canEdit($application['jobID'])) {
    
    print $j->gradeApplicant((int)$_POST['application'], $_POST['grade']);
    
} else {
    header('HTTP/1.0 401 Unauthorized');
}