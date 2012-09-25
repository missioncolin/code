<?php

global $message;

require dirname(__DIR__) . '/JobManager.php';

$j = new JobManager($db, $_SESSION['userID']);
$questionnaires = $j->getQuestionaires();
$error = '';
$success = false;

if (!empty($_POST) && !empty($questionnaires)) {
    if (isset($_POST['RQvalALPHTitle'], $_POST['RQvalWEBSLink'], $_POST['RQvalDATEDate_Posted'], $_POST['RQvalDATEDate_Expires'], $_POST['RQvalNUMBQuestionnaire'])) {
        
        if (!validate_form($_POST)) {
            $error = $message;        
        } else {
            if (isset($_POST['id']) && (int)$_POST['id'] > 0 && $j->canEdit($_POST['id'])) {
                // edit
                $success = $j->editJob($_POST);
            } else if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
                $error = 'No access';
            } else {
                // insert
                $success = $j->addJob($_POST);
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
        header('Location: /applications?success=Job+created=successfully');
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
    $dateExpires     = date('Y-m-d', strtotime('+1 month'));
    $questionnaireID = 0;
    $status          = 'inactive';

    if ($edit == true) {
        list($title, $link, $dateExpires, $datePosted, $questionnaireID, $status) = $j->getJob($_GET['id']);               
    }
    
    if (!empty($_POST)) {
        
        $title           = (isset($_POST['RQvalALPHTitle'])) ? $_POST['RQvalALPHTitle'] : $title;
        $link            = (isset($_POST['RQvalWEBSLink'])) ? $_POST['RQvalWEBSLink'] : $link;
        $datePosted      = (isset($_POST['RQvalDATEDate_Posted'])) ? $_POST['RQvalDATEDate_Posted'] : $datePosted;
        $dateExpires     = (isset($_POST['RQvalDATEDate_Expires'])) ? $_POST['RQvalDATEDate_Expires'] : $dateExpires;
        $questionnaireID = (isset($_POST['RQvalNUMBQuestionnaire'])) ? $_POST['RQvalNUMBQuestionnaire'] : $questionnaireID;
        $status          = (isset($_POST['active'])) ? 'active' : $status;
    
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
    
    if (empty($questionnaires)) {
        
        echo '<strong>You must <a href="/questionnaires">create a questionnaire</a> first</strong>';
    } else {
    ?>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <table class="simpleTable singleHeader">
            <thead>
                <tr>
                    <th colspan="5">Create a Job</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><label for="title">Job Title</label></td>
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
                    <td><input type="text" class="datepicker" name="RQvalDATEDate_Expires" id="dateExpires" value="<?php echo $dateExpires; ?>"/></td>
                </tr>
                <tr>
                    <td><label for="questionnaire">Questionnaire</label></td>
                    <td>
                        <?php
                        
                        if (is_array($questionnaires) && !empty($questionnaires)) {
                            echo '<select name="RQvalNUMBQuestionnaire" id="questionnaire" required>';
                            echo '<option>Select a questionnaire</option>';
                            foreach ($questionnaires as $qID => $qLabel) {
                                $selected = ($qID == $questionnaireID) ? ' selected="selected"' : '';
                                echo '<option value="' . $qID . '"' . $selected . '>' . $qLabel . '</option>';
                            }
                            echo '</select>';
                        } else {
                            ?>
                            <strong>You must create a questionnaire first</strong>
                            <?php
                        }
                        
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><label for="active">Active</label></td>
                    <td><input type="checkbox" name="active" id="active" <?php if ($status == 'active') { echo ' checked="checked"'; } ?>></td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="id" value="<?php echo (isset($_GET['id']) && $edit == true) ? (int)$_GET['id'] : 0; ?>" />
        <input type="submit" value="<?php echo ($edit == true) ? 'Edit' : 'Create'; ?>" class="btn green" />
    </form>
    <?php
    }
    ?>
</section>
<?php } ?>