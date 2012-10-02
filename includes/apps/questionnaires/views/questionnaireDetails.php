

<?php 
ini_set('display_errors', 'off');
require dirname(__DIR__) . '/Questionnaire.php';
$q = new Questionnaire($db);

if ($this INSTANCEOF Quipp){
	//yell('print', $_REQUEST);
	if(validate_form($_REQUEST)){
		
		if (isset($_REQUEST["new-qnr"])) {
				/*$actionQS = sprintf("INSERT INTO tblQuestionnaires (hrUserID, label, sysDateInserted, sysDateLastMod) VALUES ('%d', '%s', NOW(), NOW())", $_SESSION['userID'], clean($_REQUEST['RQvalALPHQuestionnaire_Title'], true, true));
				$db->query($actionQS);
				$_REQUEST['qnrID'] = $db->insert_id();
				$success = 1;*/
				$success = $q->createQuestionnaire($title, $userID);
				if ($success > 0){
				    header('Location: /questionnaires?action=edit&qnrID=' . $success);
				}
				else{
    				$error_message = "Your questionnaire could not be created";
				}
				//yell('print', $actionQS);
		}
		if (isset($_REQUEST["update-qnr"])) {
				$actionQS = sprintf("UPDATE tblQuestionnaires SET label = '%s', sysDateLastMod = NOW() WHERE itemID = '%d' ", clean($_REQUEST['RQvalALPHQuestionnaire_Title'], true, true), $_REQUEST['qnrID']);
				$db->query($actionQS);
				$success = 1;
				$feedback = "<strong>Success!</strong> You renamed your Questionnaire!";
				//yell('print', $actionQS);
		}
	}

$canEditQuestionnaire = true;	
if(isset($_REQUEST['qnrID'])){
	$canEditQuestionnaire = false;
	$getQuestionnaireDetailsQS = sprintf("SELECT * FROM tblQuestionnaires WHERE hrUserID = '%d' AND sysOpen = '1' AND sysActive = '1' AND itemID='%d' ", $_SESSION['userID'], $_REQUEST['qnrID']);
	$getQuestionnairesDetailsQry = $db->query($getQuestionnaireDetailsQS);
	if(is_resource($getQuestionnairesDetailsQry)){
		if($db->num_rows($getQuestionnairesDetailsQry) > 0){
			$qnr = $db->fetch_assoc($getQuestionnairesDetailsQry);
			$_REQUEST['RQvalALPHQuestionnaire_Title'] = $qnr['label'];
			$questionnaireIsValid = true;
		}else{
			$questionnaireIsValid = false;
			$feedback = "This questionnaire is no longer accessible.";
		}
	}
	//Is questionnaire already in use? If so, it can't be edited
	$getIsInUseQry = sprintf("SELECT count(jobs.itemID) AS 'countUse' FROM tblJobs jobs INNER JOIN tblQuestionnaires qs ON jobs.questionnaireID = qs.itemID WHERE questionnaireID = '%d' AND qs.isUsed = '0'", $_REQUEST['qnrID']);
	$getIsInUseRS = $db->query($getIsInUseQry);
	if(is_resource($getIsInUseRS)){
		if($db->num_rows($getIsInUseRS) > 0){
			$getIsInUse = $db->fetch_assoc($getIsInUseRS);
			if ($getIsInUse['countUse'] < 1){
				$canEditQuestionnaire = true;
			}
		}
	}
}else{
	$questionnaireIsValid = false;
	$feedback = "No questionnaire selected.";
}
	
	 
$buttonLabel = ($questionnaireIsValid) ? "Rename" : "Create";
$buttonFormName = ($questionnaireIsValid) ? "update-qnr" : "new-qnr";
	
	
if($success == 1){
	print alert_box($feedback, 1);
}else if (isset($error_message) && $error_message != '') {
	print alert_box($feedback, 2);
}



	
?>	
	<h4 id="toolbar"><?php if($qnr != NULL) { print $qnr['label']; } else { print "Create New"; } ?></h4>
	<form id="questionairesForm" action="<?php print $_SERVER['REQUEST_URI']; ?>" method="post">
		<?php
		//hide if can't edit
		if($canEditQuestionnaire){
			print "<input type=\"text\" id=\"RQvalALPHQuestionnaire_Title\" name=\"RQvalALPHQuestionnaire_Title\" value=\"".$_REQUEST['RQvalALPHQuestionnaire_Title']."\" />";
			print "<input type=\"submit\" class=\"btn\" value=\"".$buttonLabel."\" name=\"".$buttonFormName."\" class=\"btnStyle\" />";
		}
		?>
		<input type="hidden" name="qnrID" id="qnrID" value="<?php print $_REQUEST['qnrID']; ?>" />
	<form>
	
<?php

	$questionTypeLabels = array(1 => "Radio", 2 => "Checkbox", 3 => "Slider", 4 => "Video Response", 5 => "File Upload" );

	if($questionnaireIsValid){
		$getQuestionsQS = sprintf("SELECT * FROM tblQuestions WHERE sysOpen = '1' AND sysActive = '1' AND questionnaireID = '%d' ",  $_REQUEST['qnrID']);
		$getQuestionsQry = $db->query($getQuestionsQS);
		if($db->valid($getQuestionsQry)){
			$questionTable .= "<table class=\"simpleTable\">";
				if($canEditQuestionnaire){
					$questionTable .= "<tr><th>Question Label</th><th>Type</th><th></th><th></th></tr>";
				}else{
					$questionTable .= "<tr><th>Question Label</th><th>Type</th></tr>";
				}
				
				while($question = $db->fetch_assoc($getQuestionsQry)){
					
					$questionTable .= "<tr>";
						$questionTable .= "<td>".$question['label']."</td>";
						$questionTable .= "<td>".$questionTypeLabels[$question['type']]."</td>";
						if ($canEditQuestionnaire){
							$questionTable .= "<td><a href='/configure-question?qsnID=".$question['itemID']."&qnrID=".$_REQUEST['qnrID']."' class='btnStyle'>Change</a></td>";
							$questionTable .= "<td><a class='btnStyle'>Delete</a></td>";
						}
					$questionTable .= "</tr>";
				}
			$questionTable .= "</table>";
			
			print $questionTable;
		}else{
			print "<div class=\"noQuestions\">This questionnaire currently has no questions.</div>";
		}
		if($canEditQuestionnaire){
			print "<a class='btn green' href='/configure-question?qnrID=".$_REQUEST['qnrID']."'>Add A Question</a>";
		}else{
			print "<div>&nbsp;</div><br/><div>Questionnaires in use cannot be edited.</div>";
		}
	}
}

