<?php

class Questionnaire
{
    public $db;

    public function __construct($db, $questionnaireID = 0)
    {
        $this->db = $db;

        if ($questionnaireID > 0) {
            $this->questions = $this->getQuestions($questionnaireID);
        }
    }
    
    
    /**
     * Checks to see if the current user has access to edit an existing questionnaire
     * @param int questionnaire
     * @return bool
     */
    public function canEdit($questionnaireID) {
        return (bool)$this->db->num_rows($this->db->query(sprintf("SELECT itemID FROM tblQuestionnaires WHERE itemID='%d' AND hrUserID='%d' AND sysOpen = '1' AND sysActive = '1'", (int)$questionnaireID, (int)$_SESSION['userID'])));
    }


    /**
     * Get an array of the current users questionnaires
     * @param int userID
     * @return array
     */
    public function getQuestionnaires($userID = 0) {
        
        $userID = ($userID == 0) ? $_SESSION['userID'] : $userID;
                
        $qry = sprintf("SELECT * FROM tblQuestionnaires WHERE hrUserID = '%d' AND sysOpen = '1' AND sysActive = '1'", (int) $userID);
        $res = $this->db->query($qry);
        
        $questionnaires = array();
        if ($this->db->valid($res) && $this->db->num_rows($res) > 0) {
            while ($q = $this->db->fetch_assoc($res)) {
                $questionnaires[$q['itemID']] = $q;
                $questionnaires[$q['itemID']]['questions'] = $this->getQuestions($q['itemID']);
            }
        }
        
        return $questionnaires;
    }
    
        /**
     * Get an array of the current users questionnaires
     * @param int userID
     * @return array
     */
    public function getProfileQuestionnaires($userID = 0, $count = 3) {
        
        $userID = ($userID == 0) ? $_SESSION['userID'] : $userID;
                
        $qry = sprintf("SELECT * FROM tblQuestionnaires WHERE hrUserID = '%d' AND sysOpen = '1' AND sysActive = '1' LIMIT 0,%d", (int) $userID, $count);
        $res = $this->db->query($qry);
        
        $questionnaires = array();
        if ($this->db->valid($res) && $this->db->num_rows($res) > 0) {
            while ($q = $this->db->fetch_assoc($res)) {
                $questionnaires[$q['itemID']] = $q;
                $questionnaires[$q['itemID']]['questions'] = $this->getQuestions($q['itemID']);
            }
        }
        
        return $questionnaires;
    }
    
    
    /**
     * Get the details of a questionnaire
     * @param int questionnaireID
     * @return array
     */
    public function getQuestionnaire($questionnaireID) {
        
        $qry = sprintf("SELECT * FROM tblQuestionnaires WHERE itemID='%d'", (int)$questionnaireID);
        $res = $this->db->query($qry);
        
        $questionnaire = array();
        if ($this->db->valid($res) && $this->db->num_rows($res) > 0) {
            $questionnaire = $this->db->fetch_assoc($res);
            $questionnaire['questions'] = $this->getQuestions($questionnaireID);
        }
        
        return $questionnaire;
    }
    
    
    /**
     * Get an array of questions based on a questionnaire id
     * @param int questionnaire id
     * @return array
     */
    public function getQuestions($questionnaireID)
    {
        $qry = sprintf("SELECT * FROM tblQuestions WHERE questionnaireID = '%d' AND sysOpen = '1' AND sysActive='1'" , (int) $questionnaireID);
        $res = $this->db->query($qry);

        $questions = array();
        if ($this->db->valid($res)) {
            while ($qsn = $this->db->fetch_assoc($res)) {
                $questions[$qsn['itemID']] = $qsn;

                if (in_array($qsn['type'], array('1', '2'))) {
                    $questions[$qsn['itemID']]['options'] = $this->getOptions($qsn['itemID']);
                }
            }
        }

        return $questions;
    }
    
    /**
     * Get an array of the options (checkboxes and radios) for a question
     * @param int question id
     * @return array
     */
    public function getOptions($questionID)
    {
        $qry = sprintf("SELECT * FROM tblOptions WHERE questionID = '%d' AND sysOpen = '1'", (int) $questionID);
        $res = $this->db->query($qry);

        $options = array();
        if ($this->db->valid($res)) {
            while ($opt = $this->db->fetch_assoc($res)) {
                $options[$opt['itemID']] = $opt;
            }
        }

        return $options;
    }


    /**
     * Get the answer based on a question and userID
     * @param int questionID
     * @param int userID
     * @return string|array
     */
    public function getAnswer($questionID, $userID)
    {
        $qry = sprintf("SELECT * FROM tblAnswers WHERE questionID='%d' AND userID='%d' AND sysOpen='1' AND sysActive='1'",
            (int) $questionID,
            (int) $userID);
        $res = $this->db->query($qry);

        if ($this->db->valid($res) && $this->db->num_rows($res) > 0) {
            return $this->db->fetch_assoc($res);
        }

        $answers = array();
        $qry = sprintf("SELECT * FROM tblAnswerOptionsLinks WHERE questionID='%d' AND applicantID='%d'",
            (int) $questionID,
            (int) $userID);
        $res = $this->db->query($qry);

        if ($this->db->valid($res) && $this->db->num_rows($res) > 0) {
            while ($answer = $this->db->fetch_assoc($res)) {
                $answers[$answer['optionID']] = $answer;
            }
        }

        return $answers;
    }

    /**
     * Create a questionnaire
     * @param string title
     * @param int user id
     * @return int
     */
    public function createQuestionnaire($title, $userID)
    {
        if (!empty($title) && (int) $userID > 0) {
            $qry = sprintf("INSERT INTO tblQuestionnaires (hrUserID, label, sysDateInserted, sysDateLastMod, isUsed) VALUES ('%d', '%s', NOW(), NOW(), 0)",
                (int) $userID,
                $this->db->escape(strip_tags($title)));
            $res = $this->db->query($qry);
            
            if ($this->db->error() == 0 && $this->db->affected_rows($res) == 1) {
                return $this->db->insert_id();
            }

        }

        return 0;
    }
    
    
    /**
     * Get the questionnaire ID for use with can edit based on a question ID
     * @param int questionID
     * @return int
     */
    public function getQuestionnaireID($questionID) {
        
        $qry = sprintf("SELECT questionnaireID FROM tblQuestions WHERE itemID='%d'", (int)$questionID);
        $res = $this->db->query($qry);
        
        $questionnaireID = 0;
        if ($this->db->valid($res) && $this->db->num_rows($res) > 0) {
            list($questionnaireID) = $this->db->fetch_array($res);
        }
        
        return $questionnaireID;
    }
    
    
    /**
     * Delete a question
     * @param int questionID
     * @return bool
     */
    public function deleteQuestion($questionID) {
        
        
        // check to see if the questionnaire is in use
                
        $qry = sprintf("SELECT itemID FROM tblQuestionnaires WHERE hrUserID = '%d' AND itemID = '%d' AND isUsed = '0' AND sysOpen = '1' AND sysActive = '1'", 
            (int) $_SESSION['userID'],
            (int) $this->getQuestionnaireID($questionID));
        $res = $this->db->query($qry);
        
        if ($this->db->valid($res) && $this->db->num_rows($res) > 0) {
            
            $qry = sprintf("UPDATE tblQuestions SET sysOpen='0', sysActive='0' WHERE itemID='%d'",
                (int) $questionID);
            $this->db->query($qry);
            
            if ($this->db->affected_rows($res) == 1) {
                return true;
            }
        
        }
        return false;
    }
}
