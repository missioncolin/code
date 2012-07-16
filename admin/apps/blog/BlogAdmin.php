<?php

require_once("../auth/twitteroauth/twitteroauth.php");

class BlogAdmin{

    protected $_db;
    
    public function __construct($db){
        if (is_object($db) && $db INSTANCEOF DB_MySQL){
            $this->_db = $db;
        }
        else{
            throw new Exception("You are not connected to a database");
        }
    }
    
    protected function getDateTweeted($itemID){
        $qry = sprintf("SELECT `dateTweeted` FROM `tblNews` WHERE `itemID` = '%d'",
            (int)$itemID
        );
        
        $res = $this->_db->query($qry);
        if ($this->_db->valid($res)){
            $row = $this->_db->fetch_assoc($res);
            return trim($row["dateTweeted"]);
        }
        return true;
    }
    
    protected function setDateTweeted($itemID){
        $qry = sprintf("UPDATE `tblNews` SET `dateTweeted` = %d WHERE `itemID` = '%d'",
            date("U"),
            (int)$itemID
        );       
        $res = $this->_db->query($qry);

    }
    
    protected function getOAuthTokens($siteID){
        $qry = sprintf("SELECT * FROM `tblTwitterOAuth` WHERE `siteID` = %d",
            (int)$siteID
        );
        $res = $this->_db->query($qry);
        if ($this->_db->valid($res)){
            $twAuth = $this->_db->fetch_assoc($res);
            return array(trim($twAuth["consumerKey"]), trim($twAuth["consumerSecret"]), trim($twAuth["accessToken"]), trim($twAuth["accessTokenSecret"]));
        }
        return array(false,false,false,false);
    }
    
    public function autoTweet($itemID,$domains){
    
        if (is_numeric($itemID) && (int)$itemID > 0){
            if ((bool)$this->getDateTweeted($itemID) === false){
                $qry = sprintf("SELECT `title`, `siteID`, `autoTweet`, `slug` FROM `tblNews` WHERE `itemID` = %d",
                    (int)$itemID
                );
                $res = $this->_db->query($qry);
                if ($this->_db->valid($res)){
                    $row = $this->_db->fetch_assoc($res);
                    if (isset($domains[trim($row["siteID"])])){
                        
                        $share = '"'.trim($row["title"]).'" now on '.$domains[trim($row["siteID"])].": http://".$domains[trim($row["siteID"])]."/blog/".trim($row["slug"]);
                        
                        list($consumerKey, $consumerSecret, $oAuthToken, $oAuthSecret) = $this->getOAuthTokens(trim($row["siteID"]));
                        
                        if ($consumerKey !== false){
                            $tweet = new TwitterOAuth($consumerKey, $consumerSecret, $oAuthToken, $oAuthSecret);
                            if (is_object($tweet)){
                                try{
                                    $sent = $tweet->post('statuses/update',array('status' => $share));
                                    $this->setDateTweeted($itemID);
                                    return true;
                                }
                                catch (Exception $e){
                                    echo $e->getMessage();
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
}