<?php


class JobManager {
    
    var $db;
    var $userID;
    
    public function __construct($db, $userID) {
        $this->db = $db;
        $this->userID = $userID;
    }
    
    
    /**
     * Get an array of the users questionaires
     * @return array
     */
    public function getQuestionaires() {
       	$qry = sprintf("SELECT itemID, label FROM tblQuestionnaires WHERE hrUserID = '%d' AND sysOpen = '1' AND sysActive = '1'", (int)$this->userID);
       	$res = $this->db->query($qry);
       	
       	$questionaires = array();
       	if ($this->db->valid($res)) {
           	while ($q = $this->db->fetch_assoc($res)) {
               	$questionaires[$q['itemID']] = $q['label'];
           	}
       	}
       	return $questionaires;
    }
    
    /**
     * Checks to see if the current user has access to edit an existing job
     * @param int jobID
     * @return bool
     */
    public function canEdit($jobID) {
        return (bool)$this->db->num_rows($this->db->query(sprintf("SELECT itemID FROM tblJobs WHERE itemID='%d' AND userID='%d'", (int)$jobID, (int)$this->userID)));
    }
    
    /**
     * Checks to see if the user has applied for the job
     * @param int jobID
     * @return bool
     */
    public function hasApplied($jobID) {
        return (bool)$this->db->num_rows($this->db->query(sprintf("SELECT itemID FROM tblApplications WHERE jobID='%d' AND userID='%d'", (int)$jobID, (int)$this->userID)));
    }
    
    /**
     * Add a job into the database
     * @return bool
     */
    public function addJob($post) {       
        
        
        //$active = (isset($post['active']) && $post['active'] == 'on') ? 'active' : 'inactive';
        $active = 'inactive';
        $qry = sprintf("INSERT INTO tblJobs (`userID`, `title`, `link`, `dateExpires`, `datePosted`, `questionnaireID`, `sysStatus`) VALUES ('%d', '%s', '%s', '%s', '%s', '%d', '%s')",
            (int)$this->userID,
            $this->db->escape($post['RQvalALPHTitle']),
            $this->db->escape($post['RQvalWEBSLink']),
            //$this->db->escape($post['RQvalDATEDate_Expires']),
            date("Y-m-d", strtotime('+2 months')),
            $this->db->escape($post['RQvalDATEDate_Posted']),
            (int)$post['RQvalNUMBQuestionnaire'],
            $active);
        $this->db->query($qry);
        
        if ($this->db->error()) {
            return $this->db->error();
        }
        return mysql_insert_id();
    }
    
    
    /**
     * Edit an existing job in the database
     * @return bool
     */
    public function editJob($post) {
        $active = (isset($post['active']) && $post['active'] == 'on') ? 'active' : 'inactive';
        $qry = sprintf("UPDATE tblJobs SET `title`='%s', `link`='%s', `dateExpires`='%s', `datePosted`='%s', `questionnaireID`='%d', `sysStatus`='%s' WHERE itemID='%d' AND userID='%d'",
            $this->db->escape($post['RQvalALPHTitle']),
            $this->db->escape($post['OPvalWEBSLink']),
            $this->db->escape($post['RQvalDATEDate_Expires']),
            $this->db->escape($post['RQvalDATEDate_Posted']),
            (int)$post['RQvalNUMBQuestionnaire'],
            $active,
            (int)$post['id'],
            $this->userID);
        $this->db->query($qry);
        
        if ($this->db->error()) {
            return $this->db->error();
        }
        return true;
    }
    
    /**
     * Get the job details
     * @param int jobID
     * @return array
     */
    public function getJob($jobID) {
        $qry = sprintf("SELECT title, link, dateExpires, datePosted, questionnaireID, sysStatus, userID FROM tblJobs WHERE itemID='%d' AND sysOpen='1'", (int)$jobID);
        $res = $this->db->query($qry);
       
        
        if (!$this->db->valid($res)) {
            return array('No job found', '', date('Y-m-d'), date('Y-m-d'), 0, 'inactive', 0);
        }
        return $this->db->fetch_array($res);
    }
    
    
    /**
     * Get all the jobs a user has created
     * @return array
     */
    public function getJobs($offset, $page, $display) {
        
        $jobs = array();
        
        $qry = sprintf("SELECT itemID, title, link, dateExpires, datePosted, questionnaireID, hasBeenViewed, sysStatus FROM tblJobs WHERE userID='%d' AND sysOpen='1' ORDER BY datePosted DESC LIMIT %d, %d",
            (int)$this->userID,
            $offset,
            $display);
        $res = $this->db->query($qry);
       
        
        if ($this->db->valid($res)) {
            while ($j = $this->db->fetch_assoc($res)) {
                $jobs[$j['itemID']] = $j;            
            }
        }
        return $jobs;
        
    }
    
    
    /**
     * Return the total number of jobs
     * @return int
     */
    public function totalJobs() {
        
        $qry = sprintf("SELECT itemID FROM tblJobs WHERE userID='%d' AND sysOpen='1'",
            (int)$this->userID);
        $res = $this->db->query($qry);
       
        
        if ($this->db->valid($res)) {
            return $this->db->num_rows($res);
        }
        return 0;
        
    }
    
    
    /**
     * Toggles the status of a job
     * @param int jobID
     * @return bool
     */
    public function toggle($jobID) {
        
        $qry = sprintf("UPDATE tblJobs SET `sysStatus` = IF(`sysStatus` = 'active', 'inactive', 'active') WHERE itemID='%d' AND userID='%d'",
            (int)$jobID,
            (int)$this->userID);
        $res = $this->db->query($qry);

        if ($this->db->valid($res)) {
            return true;
        }
        return false;

    }    
    
    
    
    /**
     * Deletes a job
     * @param int jobID
     * @return bool
     */
    public function delete($jobID) {
        
        $qry = sprintf("UPDATE tblJobs SET `sysStatus`='inactive', `sysOpen` = '0' WHERE itemID='%d' AND userID='%d'",
            (int)$jobID,
            (int)$this->userID);
        $res = $this->db->query($qry);
       
        
        if ($this->db->valid($res)) {
            return true;
        }
        return false;
        
    }
    
    public function getApplicants($jobID, $offset = 0, $display = 10000) {
        
        $applicants = array();
        
        $qry = sprintf("SELECT *
            FROM tblApplications
        	WHERE  jobID = '%d' 
            LIMIT %d, %d", 
        	   (int)$jobID,
        	   $offset,
        	   $display);
        $res = $this->db->query($qry);
        
        if ($this->db->valid($res)) {
            while ($a = $this->db->fetch_assoc($res)) {
                $applicants[$a['userID']] = $a;            
            }
        }
        return $applicants;
    }

    
    /**
     * Return the details of an application
     * @param int applicationID
     * @return array
     */
    public function getApplication($applicationID) {
        
        
        $qry = sprintf("SELECT *
            FROM tblApplications
        	WHERE itemID = '%d'", 
        	   (int)$applicationID);
        $res = $this->db->query($qry);
        
        if ($this->db->valid($res)) {
            $tmp = $this->db->fetch_assoc($res);
            $tmp['rating'] = $this->getApplicantRating($applicationID);
            return $tmp;
        }
        return array();
    }
        
    /**
     * Return the total number of jobs
     * @return int
     */
    public function totalApplicants($jobID) {
        
        $qry = sprintf("SELECT itemID
            FROM tblApplications
        	WHERE  jobID = '%d'", 
        	   (int)$jobID);
        $res = $this->db->query($qry);
       
        
        if ($this->db->valid($res)) {
            return $this->db->num_rows($res);
        }
        return 0;
        
    }
    
    
    /**
     * Grade the applicant
     * @param int applicationID
     * @param string grade recommend|average|nq
     * @return bool
     */
    public function gradeApplicant($applicationID, $grade)
    {
        if (!in_array($grade, array('recommend', 'average', 'nq'))) {
            return false;
        }
        
        $qry = sprintf("UPDATE tblApplications SET grade='%s' WHERE itemID='%d'",
            $this->db->escape($grade),
            (int)$applicationID);
        $res = $this->db->query($qry);
        
        if ($this->db->affected_rows($res) > 0) {
            $colours = array(
                'recommend' => 'green',
                'average'   => 'yellow',
                'nq'        => 'red'
            );
            
            return $colours[$grade];
        } else {
            return false;
        }   
    }
        
    public function getApplicantRating($applicationID){
        //total from values column in tblanswers
        $points = 0;
        
        // sum the total years of experience
        $qry = sprintf("SELECT SUM(a.value) as value
            FROM tblAnswers AS a
            WHERE a.applicationID='%d'
            AND questionID IN (SELECT itemID FROM tblQuestions WHERE type='3' AND sysOpen='1' AND sysActive='1')
            AND a.sysOpen='1'
            AND a.sysActive='1'
            GROUP BY a.applicationID",
                (int)$applicationID);
        $res = $this->db->query($qry);
        
        if ($this->db->valid($res)) {
            $tmp = $this->db->fetch_assoc($res);
            $points += (int)$tmp['value'];
        }
        
        //total from options - radio
        $radioQry = sprintf("SELECT SUM(o.value) AS 'value'
            FROM tblAnswers AS a
            INNER JOIN tblOptions AS o ON a.optionID = o.itemID
            WHERE a.applicationID = '%d'
            GROUP BY a.applicationID", 
                (int)$applicationID);        
        $radioRes = $this->db->query($radioQry);
        
        if ($this->db->valid($radioRes)) {
            $tmp = $this->db->fetch_assoc($radioRes);
            $points += (int)$tmp['value'];
        }
        
        
        //total from options - multi-select
        $multiQry = sprintf("SELECT SUM(o.value) AS 'value'
            FROM tblOptions AS o 
            INNER JOIN tblAnswerOptionsLinks l ON o.itemID = l.optionID
            WHERE l.applicationID = '%d'
            GROUP BY l.applicantionID",
                (int)$applicationID);
        
        $multiRes = $this->db->query($multiQry);
        if ($this->db->valid($multiRes)) {
            $tmp = $this->db->fetch_assoc($multiRes);
            $points += (int)$tmp['value'];
        }
        
        return $points;   
    }
    /**
    * Method to re-publish a job. This is done via ajax request
    * @access public
    * @param integer $jobID
    * @param object $user
    * @see Credits::assignCredits
    * @return string
    */
    public function reactivate($jobID, $user){
        $success = "fail";
        $currentCredits = $user->info['Job Credits'];
        if ($currentCredits > 0){
            if (is_numeric($jobID) && (int)$jobID > 0){
                $newCredits = Credits::assignCredits($user, -1);
                if ($newCredits < $currentCredits){
                
                    $qry = sprintf("UPDATE `tblJobs` 
                    SET `dateExpires` = '%s', `sysStatus` = 'active', `sysOpen` = '1' 
                    WHERE `itemID` = %d AND `userID` = %d AND `dateExpires` < %d",
                        date("Y-m-d", strtotime('+2 months')),
                        (int)$jobID,
                        (int)$this->userID,
                        date("U") //want to make sure that this was not already re-published
                    );
                   
                    $res = $this->db->query($qry);
                    if ($this->db->affected_rows($res) == 1){
                        $success = 'success';
                    }
                    else{
                        $newCredits = Credits::assignCredits($user, 1);
                        $success = "An error occurred and your job could not be re-activated. Your available credits were not updated  ".$qry;
                    }
                }
                else{
                    $success = "Credits could not be updated";
                }
            }
            else{
                $success = "Invalid Job Selected";
            }
        }
        else{
            $success = "You do not have enough credits to re-activate this job";
        }
        return $success;
    }
    

    public function activate($jobID, $user){
        $success = "fail";
        $currentCredits = $user->info['Job Credits'];
        if ($currentCredits > 0){
            if (is_numeric($jobID) && (int)$jobID > 0){
                $newCredits = Credits::assignCredits($user, -1);
                
                if ($newCredits < $currentCredits){
                
                    $qry = sprintf("UPDATE `tblJobs` 
                    SET `dateExpires` = '%s', `sysStatus` = 'active', `sysOpen` = '1' 
                    WHERE `itemID` = %d AND `userID` = %d AND `dateExpires` < %d",
                        date("Y-m-d", strtotime('+2 months')),
                        (int)$jobID,
                        (int)$this->userID,
                        date("U") //want to make sure that this was not already re-published
                    );
                    $res = $this->db->query($qry);
                    if ($this->db->affected_rows($res) == 1){
                        $success = 'success';
                    }
                    else{
                        $newCredits = Credits::assignCredits($user, 1);
                        $success = "An error occurred and your job could not be activated. Your available credits were not updated";
                    }
                }
                else{
                    $success = "Credits could not be updated";
                }
            }
            else{
                $success = "Invalid Job Selected";
            }
        }
        else{
            $success = "You do not have enough credits to activate this job";
        }
        return $success;
    }

    
    public function getYearsOfExperienceQuestions($jobID){
	    //type = 3
	    $qsArr = array();
	    $selectExpQry = sprintf("SELECT question.itemID AS 'questionID', question.label AS 'label' 
	    		FROM tblQuestions question INNER JOIN tblQuestionnaires questionnaire ON question.questionnaireID = questionnaire.itemID
	    		INNER JOIN tblJobs jobs ON jobs.questionnaireID = question.questionnaireID
	    		WHERE question.type='3' AND jobs.itemID = '%d'", $jobID);
	   
	   $selectExpRS = $this->db->query($selectExpQry);
	   
	   if(is_resource($selectExpRS)){
	   	if($this->db->num_rows($selectExpRS) > 0){
			while($selectExp = $this->db->fetch_assoc($selectExpRS)){
				$qsArr[$selectExp['questionID']] = $selectExp['label'];
			} //end while
		} //end if num row > 0
	   } //end if resource
	   return $qsArr;
    }
            
    
    public function setJobViewed($jobID){
	    $setJobViewedQry = "UPDATE tblJobs set hasBeenViewed = 1 WHERE itemID = '".$jobID."'";
	    $setJobViewedRS = $this->db->query($setJobViewedQry);
	            
	    if ($this->db->valid($setJobViewedRS)) {
		    return true;
	    }
	    return false;
		
    }
    
    /**
    *  Returns applicant's answer to the slider question
    *  returns false if no answer found
    *  @return integer 
    **/
    private function getAnswer($applicantID, $jobID, $questionID) {
	    
	    $ansQry = sprintf("SELECT value FROM tblAnswers WHERE userID='%d' AND jobID='%d' AND questionID='%d'", (int)$applicantID, (int)$jobID, (int)$questionID);
	    $ansRS = mysql_query($ansQry);

	    if ($ansRS) {

		    $returnedAnswer = mysql_fetch_array($ansRS); 	    		    
		    
		    if ($returnedAnswer != false) {  // could return 0 which could be taken as false...make sure this works!
			    return (int)$returnedAnswer[0]; // return the answer that was retrieved 
		    }
		    else {
			    return false;
		    }
	    }
	    else {
		    return false;
	    }
    }
    
    /**
    *  Returns array of users to display based on 
    *  values in the array of questions requiring
    *  slider input
    *  format of return array: Array([0]=>[userID])
    *  @return int array
    **/
    
    public function getApplicantVisibility($desiredVal, $jobID, $questionID, $allApplicants) {
	    
	    // Return array with visible userIDs
	    $visibleApplicants = array();
	    $appVisibility = array();
	    
	    // For each applicant check whether applicant's answer is
		// greater than or equal to the selected value on the slider for this question
	    foreach ($allApplicants as $applicantID=>$infoArray) {

			$answer = $this->getAnswer($applicantID, $jobID, $questionID);

			// If an answer exists, check whether within range
			if ($answer >= 0) {		
			
				if ($answer >= $desiredVal) {
					
					// Will display this applicant
					$appVisibility[] = $applicantID;
					
				}
	
			}
	        
	    }
	    
	    return $appVisibility;	    
	
	}
	    
} ?>
