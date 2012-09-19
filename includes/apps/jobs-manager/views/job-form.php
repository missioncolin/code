<?php

require dirname(__DIR__) . '/JobManager.php';

$j = new JobManager($db, $_SESSION['userID']);
$questionnaires = $j->getQuestionaires();
$error = '';
$success = false;

if (!empty($_POST) && !empty($questionnaires)) {
    if (isset($_POST['title'], $_POST['link'], $_POST['datePosted'], $_POST['dateExpires'], $_POST['questionnaire'])) {
        
        if (isset($_POST['id']) && (int)$_POST['id'] > 0 && $j->canEdit($_POST['id'])) {
            // edit
            $j->editJob($_POST);
        } else if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
            $error = 'No access';
        } else {
            // insert
            $success = $j->addJob($_POST);
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
    echo 'No job found';
} else if ($edit == true && !$j->canEdit($_GET['id'])) {
    echo 'No access to job';
} else if ($edit == false && $error == '' && $success === true) {
    echo 'Nice new job';
} else {

    if ($success != '') {
        $error = $success;
    }

    if ($error != '') {
        print alert_box($error, '2');
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
        
        $title           = (isset($_POST['title'])) ? $_POST['title'] : $title;
        $link            = (isset($_POST['link'])) ? $_POST['link'] : $link;
        $datePosted      = (isset($_POST['datePosted'])) ? $_POST['datePosted'] : $datePosted;
        $dateExpires     = (isset($_POST['dateExpires'])) ? $_POST['dateExpires'] : $dateExpires;
        $questionnaireID = (isset($_POST['questionnaire'])) ? $_POST['questionnaire'] : $questionnaireID;
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
        <table>
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Date Posted</th>
                    <th>Date Expires</th>
                    <th><label for="questionnaire">Questionnaire</label></th>
                    <th><label for="active">Active</label></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="title" id="title" placeholder="Job Title" value="<?php echo $title; ?>" />
                    <br />
                    <input type="url" name="link" id="link" placeholder="http://monster.com/jobid" value="<?php echo $link; ?>" /></td>
                    <td><input type="text" class="datepicker" name="datePosted" id="datePosted" value="<?php echo $datePosted; ?>"/></td>
                    <td><input type="text" class="datepicker" name="dateExpires" id="dateExpires" value="<?php echo $dateExpires; ?>"/></td>
                    <td>
                        <?php
                        
                        if (is_array($questionnaires) && !empty($questionnaires)) {
                            echo '<select name="questionnaire" id="questionnaire">';
                            echo '<option value="0">Select a questionnaire</option>';
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
                    <td><input type="checkbox" name="active" id="active" <?php if ($status == 'active') { echo ' checked="checked"'; } ?>></td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="id" value="<?php echo (isset($_GET['id']) && $edit == true) ? (int)$_GET['id'] : 0; ?>" />
        <input type="submit" value="Create" />
    </form>
    <?php
    }
    ?>
</section>
<?php } ?>