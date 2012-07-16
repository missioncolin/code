<?php

class News
{

    var $userID;
    var $success = true;
    protected $_db;
    protected $_quipp;

    public function __construct($db, $quipp)
    {
        if (isset($_SESSION['myId'])) {
            $this->userID = (int) $_SESSION['myId'];
        } else if (isset($_SESSION['user_ID'])) {
                $this->userID = (int) $_SESSION['user_id'];
        }
        if (is_object($quipp) && $quipp INSTANCEOF Quipp){
            $this->_quipp = $quipp;
        }
        if (is_object($db) && $db INSTANCEOF DB_MySQL){
            $this->_db = $db;
        }
        else{
            throw new Exception("You are not connected to a database");
        }
    }


    public function author_specific_article_list($offset, $howManyArticlesInList, $author)
    {

        $limit = "";
        if ($howManyArticlesInList != "all") {
            $limit = "LIMIT " . $offset . "," . $howManyArticlesInList;
        }

        $qry = sprintf("SELECT itemID, title, displayDate, lead_in, slug, author FROM tblNews WHERE sysOpen='1' AND sysStatus = 'active'  AND author = '$author' ORDER BY displayDate DESC %s", $limit );
        $res = $this->_db->query($qry);
        //yell('print', $qry);
        $return = array();
        if ($this->_db->valid($res)) {
            while ($rs = $this->_db->fetch_array($res)) {
                array_push($return, $rs);
            }
            return $return;
        }else {
            return false;
        }

    }


    public function article_list($offset = 0, $howManyArticlesInList = 3)
    {
        
        $limit = "";
        if ($howManyArticlesInList != "all") {
            $limit = "LIMIT " . $offset . "," . $howManyArticlesInList;
        }

        $qry = sprintf("SELECT itemID, title, displayDate, lead_in, slug, author FROM tblNews WHERE sysOpen='1' AND sysStatus = 'active'  ORDER BY displayDate DESC %s", $limit );
        $res = $this->_db->query($qry);
        //yell('print', $qry);
        $return = array();
        if ($this->_db->valid($res)) {
            while ($rs = $this->_db->fetch_array($res)) {
                array_push($return, $rs);
            }
            return $return;
        }else {
            return false;
        }

    }


    public function articles_in_month($month = false, $year = false, $filters = false)
    {
        //$now = date("Y-m-d G:i:s", strtotime("now"));

        if ($year) {
            $yearStr = date("Y", strtotime($year));
        }else {
            $yearStr = date("Y", strtotime("now"));
        }
        if ($month) {
            $monthStr = $month;
        }else {
            $monthStr = date("F", strtotime("now"));
        }

        $dateStrMax = date("Y-m-d G:i:s", strtotime($monthStr . " 1st, " . $yearStr . " + 1 month"));
        $dateStrMin = date("Y-m-d G:i:s", strtotime($monthStr . " 1st, " . $yearStr ));

        $qry = sprintf("SELECT itemID, title, displayDate, lead_in, slug, author FROM tblNews WHERE sysOpen='1' AND sysStatus='active' AND displayDate > '%s' AND displayDate < '%s' %s ORDER BY displayDate DESC", $dateStrMin, $dateStrMax, $filters);
        //yell('print', $qry);
        $res = $this->_db->query($qry);
        $return = array();
        if ($this->_db->valid($res)) {
            while ($rs = $this->_db->fetch_array($res)) {
                //$rs['month'] = $monthStr . " " . $yearStr;
                array_push($return, $rs);
            }
            return $return;
        }else {
            return false;
        }
    }


    function search_articles($filters = false)
    {

        $qry = sprintf("SELECT itemID, title, displayDate, lead_in, slug, author FROM tblNews WHERE sysOpen='1' AND sysStatus = 'active'  %s", $filters);
        //yell('print', $qry);
        $res = $this->_db->query($qry);
        //yell('print', $qry);
        $return = array();
        if ($this->_db->valid($res)) {
            while ($rs =$this->_db->fetch_array($res)) {
                array_push($return, $rs);
            }
            return $return;
        }else {
            return false;
        }
    }


    function full_story($slug)
    {

        if ($slug == "latest") {
            $qry = sprintf("SELECT * FROM tblNews WHERE sysOpen='1' AND sysStatus = 'active' ORDER BY displayDate DESC LIMIT 0,1" );
        }else {
            $qry = sprintf("SELECT * FROM tblNews WHERE sysOpen='1' AND sysStatus = 'active' AND slug = '%s' LIMIT 0,1", $this->_db->escape($slug) );
        }

        $res = $this->_db->query($qry);
        $return = array();
        if ($this->_db->valid($res)) {
            return $this->_db->fetch_assoc($res);
        }else {
            return false;
        }

    }


    function count_articles()
    {

        $qry = sprintf("SELECT itemID FROM tblNews WHERE sysOpen='1' AND sysStatus = 'active' ");
        $res = $this->_db->query($qry);
        if ($this->_db->valid($res)) {

            return $this->_db->num_rows($res);
        }else {
            return false;
        }

    }


    function print_article_list($rs, $showDate = false, $truncateLeadIn = false, $showMonthDividers = false, $url='news', $class = 'news-article')
    {

        $month = "";

        if (is_array($rs)) {
            foreach ($rs as $article) {

                if ($showMonthDividers) {
                    if (date('F', strtotime($article['displayDate'])) != $month) {
                        echo "<h2 class=\"month-divider\">". date("F Y", strtotime($article['displayDate'])) . "</h2>";
                    }
                }
                if ($truncateLeadIn) {
                    $article['lead_in'] = str_shorten(strip_tags($article['lead_in']), 95);
                }
                
                ?>
                
                <div id="news-article-<?php print $article['itemID']; ?>" class="<?php print $class; ?>">
                	<?php if ($showDate == true) { ?><div class="date"><?php print date('M', strtotime($article['displayDate'])); print "<br />" . date('d', strtotime($article['displayDate'])); ?></div><?php } ?>
                    <h2><a href="/<?php print $url; ?>/<?php print $article['slug']; ?>"><?php print str_shorten($article['title'], 30); ?></a></h2>
                    <p class="leadin"><?php print strip_tags($article['lead_in'], '<a><em><b><i><strong><sup><sub>'); ?></p>
                </div>
                <?php
                
                
                $month = date('F', strtotime($article['displayDate']));
            }
        }else {
            echo "<p>No news at this time.</p>";

        }
    }

}


?>