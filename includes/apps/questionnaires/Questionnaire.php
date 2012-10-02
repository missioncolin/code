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

    public function createQuestionnaire($title, $userID)
    {
        $qID = 0;
        if (!empty($title) && (int) $userID > 0) {
            $actionQS = sprintf("INSERT INTO tblQuestionnaires (hrUserID, label, sysDateInserted, sysDateLastMod, isUsed) VALUES ('%d', '%s', NOW(), NOW(), 0)",
                (int) $userID,
                clean($title, true, true));
            $this->db->query($actionQS);
            if ($this->db->error() == 0 && $this->db->affected_rows() == 1) {
                $qID = $this->db->insert_id();
            }

        }

        return $qID;
    }
}
