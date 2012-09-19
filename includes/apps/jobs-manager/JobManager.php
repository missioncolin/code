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
            $this->db->escape($post['title']),
            $this->db->escape($post['link']),
            $this->db->escape($post['dateExpires']),
            $this->db->escape($post['datePosted']),
            (int)$post['questionnaire'],
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
            $this->db->escape($post['title']),
            $this->db->escape($post['link']),
            $this->db->escape($post['dateExpires']),
            $this->db->escape($post['datePosted']),
            (int)$post['questionnaire'],
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
        $qry = sprintf("SELECT title, link, dateExpires, datePosted, questionnaireID, sysStatus FROM tblJobs WHERE itemID='%d' AND userID='%d'", (int)$jobID, (int)$this->userID);
        $res = $this->db->query($qry);
       
        
        if (!$this->db->valid($res)) {
            return array();
        }
        return $this->db->fetch_array($res);
    }
}