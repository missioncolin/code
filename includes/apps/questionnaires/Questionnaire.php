<?php

class Questionnaire {

    var $db;

    public function __construct($db, $questionnaireID = 0) {

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
    public function getQuestions($questionnaireID) {

        $qry = sprintf("SELECT * FROM tblQuestions WHERE questionnaireID = '%d' AND sysOpen = '1' AND sysActive='1'" , (int)$questionnaireID);
        $res = $this->db->query($qry);

        $questions = array();
        if ($this->db->valid($res)) {
            while($qsn = $this->db->fetch_assoc($res)) {
                $questions[$qsn['itemID']] = $qsn;

                if (in_array($qsn['type'], array('1', '2'))) {
                    $questions[$qsn['itemID']]['options'] = $this->getOptions($qsn['itemID']);
                }
            }
        }

        return $questions;
    }


    public function getOptions($questionID) {

        $qry = sprintf("SELECT * FROM tblOptions WHERE questionID = '%d' AND sysOpen = '1'", (int)$questionID);
        $res = $this->db->query($qry);
        
        $options = array();
        if ($this->db->valid($res)) {
            while($opt = $this->db->fetch_assoc($res)) {
                $options[$opt['itemID']] = $opt;
            }
        }
        
        return $options;

    }

}