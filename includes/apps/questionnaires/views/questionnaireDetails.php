<?php

if (!class_exists('Questionnaire')) {
    require dirname(__DIR__) . '/Questionnaire.php';
}
$q = new Questionnaire($db);

if ($this instanceof Quipp) {

    $canEdit = false;

    if (!isset($_GET['qnrID'])) {

        $feedback = 'No questionnaire selected.';

    } else {

        $canEdit = $q->canEdit($_GET['qnrID']);
        $questionnaire = $q->getQuestionnaire($_GET['qnrID']);

    }

    if (validate_form($_POST)) {

        if (isset($_POST["new-qnr"])) {
            $success = $q->createQuestionnaire($_POST['RQvalALPHQuestionnaire_Title'], $_SESSION['userID']);
            if ($success > 0) {
                header('Location: /configure-question?step=2&qnrID=' . $success);
            } else {
                $error_message = "Your questionnaire could not be created";
            }
        } elseif (isset($_POST["update-qnr"])) {
            $actionQS = sprintf("UPDATE tblQuestionnaires SET label = '%s', sysDateLastMod = NOW() WHERE itemID = '%d' ",
                $db->escape(strip_tags($_REQUEST['RQvalALPHQuestionnaire_Title'])),
                (int) $_GET['qnrID']);
            $db->query($actionQS);

            $success  = 1;
            $feedback = "<strong>Success!</strong> You renamed your Questionnaire!";
            $questionnaire = $q->getQuestionnaire($_GET['qnrID']);

        }
    }

    $buttonLabel    = (isset($questionnaire) && is_array($questionnaire) && !empty($questionnaire)) ? "Rename" : "Create";
    $buttonFormName = (isset($questionnaire) && is_array($questionnaire) && !empty($questionnaire)) ? "update-qnr" : "new-qnr";


    if (isset($success) && $success == 1) {
        echo alert_box($feedback, 1);
    } elseif (isset($error_message) && $error_message != '') {
        echo alert_box($feedback, 2);
    }

    if ($canEdit == true || $canEdit == false && !isset($_GET['qnrID'])) {

?>
    <h4 id="toolbar"><?php if (isset($questionnaire) && is_array($questionnaire)) { echo $questionnaire['label']; } else { echo "Create New"; } ?></h4>
    <form id="questionairesForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">

       <input type="text" id="RQvalALPHQuestionnaire_Title" name="RQvalALPHQuestionnaire_Title" value="<?php echo (isset($questionnaire['label'])) ? $questionnaire['label'] : ''; ?>" />
       <input type="submit" class="btn" value="<?php echo $buttonLabel; ?>" name="<?php echo $buttonFormName; ?>" class="btnStyle" />
       <input type="hidden" name="qnrID" id="qnrID" value="<?php echo (isset($_GET['qnrID'])) ? $_GET['qnrID'] : ''; ?>" />
    <form>

<?php
    }

    $questionTypeLabels = array(
        1 => "Radio",
        2 => "Checkbox",
        3 => "Slider",
        4 => "Video Response",
        5 => "File Upload"
    );


    if ($canEdit == true && isset($questionnaire) && is_array($questionnaire)) {

        if (empty($questionnaire['questions'])) {

            echo '<div class="noQuestions">This questionnaire currently has no questions.</div>';
        } else {

            echo  "<table class=\"simpleTable\">";
            if ($questionnaire['isUsed'] == '0') {
                echo  "<tr><th>Question Label</th><th>Type</th><th></th><th></th></tr>";
            } else {
                echo  "<tr><th>Question Label</th><th>Type</th></tr>";
            }

            foreach($questionnaire['questions'] as $question) {
                echo  "<tr>";
                echo  "<td>".$question['label']."</td>";
                echo  "<td>".$questionTypeLabels[$question['type']]."</td>";
                if ($questionnaire['isUsed'] == '0') {
                    echo  "<td><a href='/configure-question?qsnID=".$question['itemID']."&qnrID=".$_GET['qnrID']."' class='btnStyle'>Change</a></td>";
                    echo  "<td><a class='btnStyle'>Delete</a></td>";
                }
                echo  "</tr>";

            }
            echo  "</table>";


        }

        if ($questionnaire['isUsed'] == '0') {
            echo "<a class='btn green' href='/configure-question?qnrID=".$_GET['qnrID']."'>Add A Question</a>";
        } else {
            echo alert_box("Questionnaires in use cannot be edited.", 3);
        }

    } elseif (isset($_GET['qnrID'])) {
        echo alert_box('You have no access to this questionnaire', 2);
    }

}
