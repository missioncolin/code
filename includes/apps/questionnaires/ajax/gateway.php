<?php

require dirname(dirname(dirname(__DIR__))) . '/init.php';
require dirname(__DIR__) . '/Questionnaire.php';

$j = new Questionnaire($db, $_SESSION['userID']);

//http://kristina.140b.git.resolutionim.com/includes/apps/questionnaires/ajax/gateway.php?action=edit&questionnaireID=306&typeID=3&label=PHP
//create question POST: action=new&questionaireID=1234&typeID=3
//edit question POST: action=edit&questionaireID=1234&questionID=123&label=What+is+you+five+year+plan
//delete question POST: action=delete&questionaireID=1234&questionID=123

print_r($_POST);

if ($j->canEdit($_POST['questionnaireID'])) {
	
    if($_POST['action'] == "new") {
       print $j->createQuestion($_POST['questionnaireID'], $_POST['typeID']);    
    } elseif($_POST['action'] == "edit") {
      if ($j->editQuestion($_POST['questionID'], $_POST['label']) == 0) { echo "Error.";}
    } elseif($_POST['action'] == "delete") {
      print $j->deleteQuestion($_POST['questionID'], $_POST['questionnaireID']);
    } else {
        header('HTTP/1.0 401 Unauthorized');
    }

    
} else {
    header('HTTP/1.0 401 Unauthorized');
}
