<?php
if (isset($db) && $db INSTANCEOF DB_MySQL && $this INSTANCEOF Quipp){
    $qry = sprintf("SELECT `handle` FROM `tblTwitter` WHERE `siteID` = %d AND sysStatus = 'active' LIMIT 1",
        (int)$this->siteID
    );
    
    $res = $db->query($qry);
    $info = $db->fetch_assoc($res);
    
    if (isset($info)){
        $tweets = @simplexml_load_file('http://twitter.com/statuses/user_timeline/'.ltrim($info["handle"],"@") .'.xml?count=1&exclude_replies=true');
    }
}
?>
<div id="twitterWidget">
	<h2>Recent Tweet</h2>
	<p>
<?php
    if (isset($tweets) && is_object($tweets) && isset($tweets->status)){
        foreach ($tweets->status as $tweet){
            echo (string)$tweet->text;
            break;
        }
    }
    else{
        echo 'No Recent Tweets';
    }
?>
	</p>
</div>