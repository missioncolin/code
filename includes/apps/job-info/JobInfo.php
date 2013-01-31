<?php


class JobInfo {
    
    var $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    
    /**
     * Gets job info for a non-logged in user
     * @param int jobID
     * @return list
     */
    public function getJob($jobID) {
        $qry = sprintf("SELECT title, link, dateExpires, datePosted, questionnaireID, sysStatus, userID FROM tblJobs WHERE itemID='%d' AND sysOpen='1'", (int)$jobID);
        $res = $this->db->query($qry);
       
        
        if (!$this->db->valid($res)) {
            return array('No job found', '', date('Y-m-d'), date('Y-m-d'), 0, 'inactive', 0);
        }
        
        return $this->db->fetch_array($res);

    }

	
	    
} ?>