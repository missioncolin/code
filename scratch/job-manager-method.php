<?php
require dirname(__DIR__) . '/includes/init.php';
require dirname(__DIR__) . '/includes/apps/jobs-manager/JobManager.php';

$j = new JobManager($db, $_SESSION['userID']);


$arr = $j->getYearsOfExperienceQuestions(1);
print_r($arr);


?>
