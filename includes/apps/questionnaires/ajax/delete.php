<?php

require dirname(dirname(dirname(__DIR__))) . '/init.php';
require dirname(__DIR__) . '/Questionnaire.php';

$j = new Questionnaire($db, $_SESSION['userID']);

if ($j->canEdit($j->getQuestionnaireID($_POST['question']))) {
    $j->deleteQuestion($_POST['question']);
    
} else {
    header('HTTP/1.0 401 Unauthorized');
}