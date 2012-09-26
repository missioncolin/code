

<?php 
ini_set('display_errors', 'off');
if ($this INSTANCEOF Quipp){
	//yell('print', $_REQUEST);
	if(validate_form($_REQUEST)){
		
		if (isset($_REQUEST["new-qnr"])) {
				$actionQS = sprintf("INSERT INTO tblQuestionnaires (hrUserID, label, sysDateInserted, sysDateLastMod) VALUES ('%d', '%s', NOW(), NOW())", $_SESSION['userID'], clean($_REQUEST['RQvalALPHQuestionnaire_Title'], true, true));
				$db->query($actionQS);
				$_REQUEST['qnrID'] = $db->insert_id();
				$success = 1;
				header('Location: /questionnaires?action=edit&qnrID=' . $_REQUEST['qnrID']);
				//yell('print', $actionQS);
		}
		if (isset($_REQUEST["update-qnr"])) {
				$actionQS = sprintf("UPDATE tblQuestionnaires SET label = '%s', sysDateLastMod = NOW() WHERE itemID = '%d' ", clean($_REQUEST['RQvalALPHQuestionnaire_Title'], true, true), $_REQUEST['qnrID']);
				$db->query($actionQS);
				$success = 1;
				$feedback = "<strong>Success!</strong> You have renamed your Questionnaire!";
				//yell('print', $actionQS);
		}
	}
	
if(isset($_REQUEST['qnrID'])){
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
		<input type="text" id="RQvalALPHQuestionnaire_Title" name="RQvalALPHQuestionnaire_Title" value="<?php print $_REQUEST['RQvalALPHQuestionnaire_Title']; ?>" />
		<input type="hidden" name="qnrID" id="qnrID" value="<?php print $_REQUEST['qnrID']; ?>" />
		<input type="submit" class="btn" value="<?php print $buttonLabel; ?>" name="<?php print $buttonFormName; ?>" class="btnStyle" />
	<form>
	
<?php

	$questionTypeLabels = array(1 => "Radio", 2 => "Checkbox", 3 => "Slider", 4 => "Video Response", 5 => "File Upload" );

	if($questionnaireIsValid){
		$getQuestionsQS = sprintf("SELECT * FROM tblQuestions WHERE sysOpen = '1' AND sysActive = '1' AND questionnaireID = '%d' ",  $_REQUEST['qnrID']);
		$getQuestionsQry = $db->query($getQuestionsQS);
		if($db->valid($getQuestionsQry)){
			$questionTable .= "<table class=\"simpleTable\">";
				$questionTable .= "<tr><th>Question Label</th><th>Type</th><th></th><th></th></tr>";
				while($question = $db->fetch_assoc($getQuestionsQry)){
					$questionTable .= "<tr>";
						$questionTable .= "<td>".$question['label']."</td>";
						$questionTable .= "<td>".$questionTypeLabels[$question['type']]."</td>";
						$questionTable .= "<td><a href='/configure-question?qsnID=".$question['itemID']."&qnrID=".$_REQUEST['qnrID']."' class='btnStyle'>Change</a></td>";
						$questionTable .= "<td><a class='btnStyle'>Delete</a></td>";
					$questionTable .= "</tr>";
				}
			$questionTable .= "</table>";
			
			print $questionTable;
		}else{
			print "<div class=\"noQuestions\">This questionnaire currently has no questions.</div>";
		}
		
		print "<a class='btn green' href='/configure-question?qnrID=".$_REQUEST['qnrID']."'>Add A Question</a>";
	}
}

