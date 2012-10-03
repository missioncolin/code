<?php

global $message, $user, $quipp;

require dirname(__DIR__) . '/JobManager.php';
require dirname(dirname(__DIR__)) . '/questionnaires/Questionnaire.php';
require dirname(dirname(__DIR__)) . '/credits/Credits.php';

$j = new JobManager($db, $_SESSION['userID']);
$q = new Questionnaire($db);
$questionnaires = $j->getQuestionaires();
$error = '';
$success = false;
$newQnr = false;

$quipp->js['footer'][] = "/includes/apps/jobs-manager/js/jobs-manager.js";

if (!empty($_POST) && !empty($questionnaires)) {
    if (isset($_POST['RQvalALPHTitle'], $_POST['RQvalWEBSLink'], $_POST['RQvalDATEDate_Posted'], $_POST['RQvalNUMBQuestionnaire'])) {
        
        if (!validate_form($_POST)) {
            $error = $message;        
        } else {
        
            if ((int)$_POST['RQvalNUMBQuestionnaire'] == 0 && isset($_POST['RQvalALPHNew_Questionnaire']) && !empty($_POST['RQvalALPHNew_Questionnaire'])){
                $_POST['RQvalNUMBQuestionnaire'] = $q->createQuestionnaire($_POST['RQvalALPHNew_Questionnaire'], $_SESSION['userID']);
                if ((int)$_POST['RQvalNUMBQuestionnaire'] > 0){
                    $newQnr = true;
                }
                else{
                    $success = 'Your questionnaire could not be created. Please retry or use a previously created questionnaire.';
                }
            }
            
            if (isset($_POST['id']) && (int)$_POST['id'] > 0 && $j->canEdit($_POST['id'])) {
                // edit
                $success = $j->editJob($_POST);
            } else if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
                $error = 'No access';
            } else if ((int)$_POST['RQvalNUMBQuestionnaire'] > 0) {
                // insert
                if ((int)$user->info['Job Credits'] > 0) {

                    $success = $j->addJob($_POST);
                } else {
                    $success = 'You do not have a sufficiant amount of job credits to create a new job. Please <a href="/buy-job-credits">purchase more job credits</a> to continue.'; 
                }
            }
        }
    } else {
        $error = 'Missing fields';
    }
}

$edit = false;
if ($this->info['systemName'] == 'edit-job') {
   $edit = true;
}


if ($edit == true && !isset($_GET['id'])) {
    echo alert_box('<strong>Warning</strong>, no job found', 3);
} else if ($edit == true && !$j->canEdit($_GET['id'])) {
    echo alert_box('<strong>Access denied</strong. You do not have access to this job', 2);
} else if ($error == '' && $success === true) {
    if ($edit == false) {
        Credits::assignCredits($user, -1);
        if ($newQnr === true){
            header('Location: /configure-question?step=2&qnrID='.$_POST["RQvalNUMBQuestionnaire"]);
        }
        else {
            header('Location: /applications?success=Job+created=successfully');
        }
    } else {
        header('Location: /applications?success=Job+edited=successfully');
    }

} else {
    if ($success != '') {
        $error = $success;
    }

    if ($error != '') {
        echo alert_box($error, '2');
    }

    
    $title           = '';
    $link            = '';
    $datePosted      = date('Y-m-d');
    $dateExpires     = date('Y-m-d', strtotime('+2 months'));
    $questionnaireID = 0;

    if ($edit == true) {
        list($title, $link, $dateExpires, $datePosted, $questionnaireID, $status) = $j->getJob($_GET['id']);               
    }
    
    if (!empty($_POST)) {
        
        $title           = (isset($_POST['RQvalALPHTitle'])) ? $_POST['RQvalALPHTitle'] : $title;
        $link            = (isset($_POST['RQvalWEBSLink'])) ? $_POST['RQvalWEBSLink'] : $link;
        $datePosted      = (isset($_POST['RQvalDATEDate_Posted'])) ? $_POST['RQvalDATEDate_Posted'] : $datePosted;
        $dateExpires     = (isset($_POST['RQvalDATEDate_Expires'])) ? $_POST['RQvalDATEDate_Expires'] : $dateExpires;
        $questionnaireID = (isset($_POST['RQvalNUMBQuestionnaire'])) ? $_POST['RQvalNUMBQuestionnaire'] : $questionnaireID;    
    }

/* display the form if:
 * GET if you are creating a job
 * POST create job and error
 * GET edit with id and access
 * POST edit with id and access and error
 */
?>
<section id="jobManagerEdit">
    
    <?php
    
    /*
if (empty($questionnaires)) {
        
        echo '<strong>You must <a href="/questionnaires">create a questionnaire</a> first</strong>';
    } else 
*/if ((int)$user->info['Job Credits'] == 0) {
        echo '<strong>You do not have a sufficiant amount of job credits to create a new job. Please <a href="/buy-job-credits?req=createnew">purchase more job credits</a> to continue.</strong>';
        
    } else {
    ?>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <table class="simpleTable singleHeader">
            <thead>
                <tr>
                    <th colspan="5"><?php echo ($edit == true) ? 'Edit' : 'Create'; ?> a Job</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="30%"><label for="title">Job Title</label></td>
                    <td><input type="text" name="RQvalALPHTitle" id="title" placeholder="Job Title" value="<?php echo $title; ?>"  required/></td>
                </tr>
                <tr>
                    <td><label for="link">Job Link</label></td>
                    <td><input type="url" name="RQvalWEBSLink" id="link" placeholder="http://monster.com/jobid" value="<?php echo $link; ?>"  required/></td>
                </tr>
                <tr>
                    <td><label for="datePosted">Date Posted</label></td>
                    <td><input type="text" class="datepicker" name="RQvalDATEDate_Posted" id="datePosted" value="<?php echo $datePosted; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="dateExpires">Date Expires</label></td>
                    <td><?php echo date("Y-m-d", strtotime('+2 months'));?></td>
                </tr>
                <tr>
                    <td><label for="questionnaire">Questionnaire</label></td>
                    <td>
                        <?php
                        
                            echo '<select name="RQvalNUMBQuestionnaire" id="questionnaire" required>';
                            echo '<option>Select a questionnaire</option>';
                            echo '<option value="0">Create a New Questionnaire</option>';
                            foreach ($questionnaires as $qID => $qLabel) {
                                $selected = ($qID == $questionnaireID) ? ' selected="selected"' : '';
                                echo '<option value="' . $qID . '"' . $selected . '>' . $qLabel . '</option>';
                            }
                            echo '</select>';
                        
                        ?>
                    </td>
                </tr>
                <tr style="display:none" id="rCreateNew">
                    <td><label for="newQuestionnaire">New Questionnaire</label></td>
                    <td><input type="text" name="RQvalALPHNew_Questionnaire" id="newQuestionnaire" disabled="disabled" placeholder="Questionnaire Title" /></td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="id" value="<?php echo (isset($_GET['id']) && $edit == true) ? (int)$_GET['id'] : 0; ?>" />
        <input type="submit" value="<?php echo ($edit == true) ? 'Edit' : 'Create &amp; Continue'; ?>" class="btn green" />
    </form>
    <?php
    }
    ?>
</section>
<?php } ?>