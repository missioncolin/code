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
     * Add a job into the database
     * @return bool
     */
    public function addJob($post) {
        
        $active = (isset($post['active']) && $post['active'] == 'on') ? 'active' : 'inactive';
        $qry = sprintf("INSERT INTO tblJobs (`userID`, `title`, `link`, `dateExpires`, `datePosted`, `questionnaireID`, `sysStatus`) VALUES ('%d', '%s', '%s', '%s', '%s', '%d', '%s')",
            (int)$this->userID,
            $this->db->escape($post['RQvalALPHTitle']),
            $this->db->escape($post['RQvalWEBSLink']),
            $this->db->escape($post['RQvalDATEDate_Expires']),
            $this->db->escape($post['RQvalDATEDate_Posted']),
            (int)$post['RQvalNUMBQuestionnaire'],
            $active);
        $this->db->query($qry);
        
        if ($this->db->error()) {
            return $this->db->error();
        }
        return true;
    }
    
    
    /**
     * Edit an existing job in the database
     * @return bool
     */
    public function editJob($post) {
        $active = (isset($post['active']) && $post['active'] == 'on') ? 'active' : 'inactive';
        $qry = sprintf("UPDATE tblJobs SET `title`='%s', `link`='%s', `dateExpires`='%s', `datePosted`='%s', `questionnaireID`='%d', `sysStatus`='%s' WHERE itemID='%d' AND userID='%d'",
            $this->db->escape($post['RQvalALPHTitle']),
            $this->db->escape($post['RQvalWEBSLink']),
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
        $qry = sprintf("SELECT title, link, dateExpires, datePosted, questionnaireID, sysStatus FROM tblJobs WHERE itemID='%d' AND sysOpen='1'", (int)$jobID);
        $res = $this->db->query($qry);
       
        
        if (!$this->db->valid($res)) {
            return array('No job found', '', date('Y-m-d'), date('Y-m-d'), 0, 'inactive');
        }
        return $this->db->fetch_array($res);
    }
    
    
    /**
     * Get all the jobs a user has created
     * @return array
     */
    public function getJobs($offset, $page, $display) {
        
        $jobs = array();
        
        $qry = sprintf("SELECT itemID, title, link, dateExpires, datePosted, questionnaireID, sysStatus FROM tblJobs WHERE userID='%d' AND sysOpen='1' ORDER BY datePosted DESC LIMIT %d, %d",
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
    
    public function get_points_sum($jobID, $userID){
	  //total from values column in tblanswers
	  $points = 0;
	  $answersQry = sprintf("SELECT SUM(value) AS 'points'
        			FROM tblAnswers 
        			WHERE  jobID = '%d' AND userID= '%d' AND sysActive = '1' and sysOpen = '1'
        			GROUP BY userID, jobID", $jobID, $userID);
        $answersRS= mysql_query($answersQry);
        if ($answersRS){
        	$row = mysql_fetch_array($answersRS);
        	$points += $row['points'];
        }


        //total from options - radio
 	$radioQry = sprintf("SELECT SUM( options.value ) AS  'value'
 		FROM tblAnswers answers
		INNER JOIN tblOptions options ON answers.optionID = options.itemID
		WHERE answers.jobID = '%d'
		AND answers.userID = %d
		GROUP BY answers.userID
		", $jobID, $userID);

 	$radioRS = mysql_query($radioQry);
 	if($radioRS){
        	$valueRow = mysql_fetch_array($radioRS);
        	$points += $valueRow['value'];
 	}
 
	
 	 //total from options - multi-select
 	$multiQry = sprintf("SELECT sum(options.value) AS 'value' FROM tblOptions options 
 	INNER JOIN tblAnswerOptionsLinks links ON options.itemID = links.optionID
 	WHERE links.jobID = '%d' AND links.applicantID = '%d'
 	GROUP BY applicantID, jobID", $jobID, $userID);
 	
 	$multiRS = mysql_query($multiQry);
 	if ($multiRS){
        	$valueRow = mysql_fetch_array($multiRS);
        	$points += $valueRow['value'];	
 	}
	        
	return $points;
	    
    }
    
}