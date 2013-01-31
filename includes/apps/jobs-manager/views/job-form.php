<?php

global $message, $user, $quipp;

require dirname(__DIR__) . '/JobManager.php';
require dirname(dirname(__DIR__)) . '/questionnaires/Questionnaire.php';
require dirname(dirname(__DIR__)) . '/credits/Credits.php';

$j = new JobManager($db, $_SESSION['userID']);
$q = new Questionnaire($db);
$questionnaires = $j->getQuestionaires();
$error = false;
$newQnr = false;
$jobID = 0;
$editReturn;

$quipp->js['footer'][] = "/includes/apps/jobs-manager/js/jobs-manager.js";

if (!empty($_POST)) {
    if (isset($_POST['RQvalALPHTitle'], $_POST['OPvalWEBSLink'], $_POST['RQvalDATEDate_Posted'], $_POST['RQvalNUMBQuestionnaire'])) {
        
        if (!validate_form($_POST)) {
            $error = $message;        
        } else {
        
        	if ($_POST['RQvalNUMBQuestionnaire'] == 0) {
	        	
/*             if ((int)$_POST['RQvalNUMBQuestionnaire'] == 0 && isset($_POST['RQvalALPHNew_Questionnaire']) && !empty($_POST['RQvalALPHNew_Questionnaire'])){ */
/*             if ((int)$_POST['RQvalNUMBQuestionnaire'] == 0){ */
                $_POST['RQvalNUMBQuestionnaire'] = $q->createQuestionnaire($_POST['RQvalALPHTitle'], $_SESSION['userID']);
                
                if ((int)$_POST['RQvalNUMBQuestionnaire'] > 0){
                    $newQnr = true;
                }
                else{
                    $error = 'Your questionnaire could not be created. Please retry or use a previously created questionnaire.';
                }
/*             } */
            }
            else {
	            
	            // Get questionnaire ID for this job to edit
	            $questionnaireID = $j->getQuestionnaireID($_GET['id']); 
                	
            }
            
            if (isset($_POST['id']) && (int)$_POST['id'] > 0 && $j->canEdit($_POST['id'])) {
                // edit
                $editReturn = $j->editJob($_POST);
                if (is_numeric((int)$editReturn)){
	                $jobID = $editReturn;
                }else{
	                $error = $editReturn;
                }
            } else if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
                $error = 'No access';
            } else if ((int)$_POST['RQvalNUMBQuestionnaire'] > 0) {
                // insert
               //REMOVING THE CHECK FOR CREDITS BEFORE ADDING A JOB.
                 //if ((int)$user->info['Job Credits'] > 0) {
                    $addJobReturn = $j->addJob($_POST);
                    if (is_numeric($addJobReturn)){
	                    $jobID = $addJobReturn;
                    } else{
	                    $error = "There was a problem with your entry, please try again";
                    }
                // } else {
                 //    $success = 'You do not have a sufficiant amount of job credits to create a new job. Please <a href="/buy-job-credits">purchase more job credits</a> to continue.'; 
                //}
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


if (isset($_GET['step'])) {
        
    switch($_GET['step']) {
        
        case '1':
            echo alert_box('<h2>Tips</h2><p>The job title will be shown to the applicant during the application process</p><p>The job title will be incorporated into the intervue link</p>', 3);
            break;
        
    }
    
}


if ($edit == true && !isset($_GET['id'])) {

    $quipp->js['onload'] .= 'alertBox("fail", "No job found");';
    
} else if ($edit == true && !$j->canEdit($_GET['id'])) {
    
    $quipp->js['onload'] .= 'alertBox("fail", "You do not have access to this job");';

    
} else if ($error == false && $jobID > 0) {
    if ($edit == false) {
       // Credits::assignCredits($user, -1);
        if ($newQnr === true){
            header('Location: /configure-question?step=2&qnrID='.$_POST["RQvalNUMBQuestionnaire"].'&jobID='.$jobID);
        }
        else {
            header('Location: /applications?success=Job+created=successfully');
        }
    } else {
    	// Get questionnaire by ID
    	
        header('Location: /configure-question?editStep=1&jobID='.$_GET['id'].'&qnrID='.$questionnaireID);
    }

} else {
    if ($error != false) {
        $quipp->js['onload'] .= 'alertBox("fail", "' . $error . '");';
    }

    
    $title           = '';
    $link            = '';
    $datePosted      = date('Y-m-d');
    $dateExpires     = date('Y-m-d', strtotime('+2 months'));
    $questionnaireID = 0;
    $status 		= 'inactive';

    if ($edit == true) {
        list($title, $link, $dateExpires, $datePosted, $questionnaireID, $status) = $j->getJob($_GET['id']);               
    }
    
    if (!empty($_POST)) {
        
        $title           = (isset($_POST['RQvalALPHTitle'])) ? $_POST['RQvalALPHTitle'] : $title;
        $link            = (isset($_POST['OPvalWEBSLink'])) ? $_POST['OPvalWEBSLink'] : $link;
        $datePosted      = (isset($_POST['RQvalDATEDate_Posted'])) ? $_POST['RQvalDATEDate_Posted'] : $datePosted;
        $dateExpires     = (isset($_POST['RQvalDATEDate_Expires'])) ? $_POST['RQvalDATEDate_Expires'] : $dateExpires;
        $questionnaireID = (isset($_POST['RQvalNUMBQuestionnaire'])) ? $_POST['RQvalNUMBQuestionnaire'] : $questionnaireID;    
        $status 	    = (isset($_POST['RQvalALPHActive'])) ? $_POST['RQvalALPHActive'] : $status;
    }

/* display the form if:
 * GET if you are creating a job
 * POST create job and error
 * GET edit with id and access
 * POST edit with id and access and error
 */
?>
<section id="jobManagerEdit">
	
	<!--- Prevent submission of form on enter press --->
    <body OnKeyPress="return disableKeyPress(event)">
    
	<div class="colASplit">
    
    <?php
    
    /*
if (empty($questionnaires)) {
        
        echo '<strong>You must <a href="/questionnaires">create a questionnaire</a> first</strong>';
    } else 
*/if ((int)$user->info['Job Credits'] == 0) {
        //echo '<p><strong>You current have 0 job credits. You can create a job, but you must add credits to your account before your job can be published. To do that now, please <a href="/buy-job-credits?req=createnew">purchase more job credits</a>.</strong></p>';
        
    } 
    ?>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <table class="simpleTable singleHeader">
            <thead>
                <tr>
                    <th colspan="5"><?php echo ($edit == true) ? 'Edit' : 'Name'; ?> Your Job</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="RQvalALPHTitle" id="title" placeholder="Example: Administrative Assistant" value="<?php echo $title; ?>"  required/></td>
                    <input type="hidden" name="RQvalNUMBQuestionnaire" id="questionnaire" value="<?php echo ($edit == true) ? $questionnaireID : 0; ?>"/>
                </tr>
             <!--   <tr>
                    <td><label for="link">Job Link</label></td>
                    <td><input type="url" name="OPvalWEBSLink" id="link" placeholder="http://monster.com/jobid" value="<?php echo $link; ?>"  required/></td>
                </tr>
               
              --> 
              
              <!--
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
-->

            </tbody>
        </table>
        
        <?php if(!isset($link)) { $link = "http://www.example.com"; } ?>
        
        <input type="hidden" name="OPvalWEBSLink" id="link" placeholder="http://monster.com/jobid" value="<?php echo $link; ?>" />
        <input type="hidden" name="RQvalDATEDate_Posted" value="<?php echo $datePosted; ?>"/>
        <input type="hidden" name="RQvalDATEDate_Expires" value="<?php echo $dateExpires; ?>"/>
        <input type="hidden" name="id" value="<?php echo (isset($_GET['id']) && $edit == true) ? (int)$_GET['id'] : 0; ?>" />
        <input type="hidden" name="RQvalALPHActive" value="<?php echo $status; ?>"/>
        <input type="submit" value="<?php echo ($edit == true) ? 'Save &amp Continue' : 'Create &amp Continue'; ?>" class="btn green" />
    </form>
    </div> <!-- colASplit -->
</section>
<?php } ?>