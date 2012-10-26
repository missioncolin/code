<?php

require dirname(dirname(dirname(__DIR__))) . '/init.php';
require dirname(__DIR__) . '/Questionnaire.php';




$j = new Questionnaire($db, $_SESSION['userID']);


//create question POST: action=new&questionaireID=1234&typeID=3
//edit question POST: action=edit&questionaireID=1234&questionID=123&label=What+is+you+five+year+plan
//delete question POST: action=delete&questionaireID=1234&questionID=123

if ($j->canEdit($_POST['questionnaireID'])) {

    if($_POST['action'] == "new") {
       print $j->createQuestion($_POST['questionnaireID'], $_POST['typeID']);    
    } elseif($_POST['action'] == "edit") {
      print $j->editQuestion($_POST['questionID'], $_POST['label'])
    } elseif($_POST['action'] == "edit") {
      print $j->deleteQuestion($_POST['questionID']);
    } else {
        header('HTTP/1.0 401 Unauthorized');
    }

    
} else {
    header('HTTP/1.0 401 Unauthorized');
}