<?php
if (isset($db) && $db INSTANCEOF DB_MySQL && $this INSTANCEOF Quipp){
    $qry = sprintf("SELECT `handle` FROM `tblTwitter` WHERE `siteID` = %d AND sysStatus = 'active' LIMIT 1",
        (int)$this->siteID
    );
    
    $res = $db->query($qry);
    $info = $db->fetch_assoc($res);
}
?>
<div id="twitterCallout" class="callout">
	<h3>Follow us on Twitter<br />
	<a href="http://www.twitter.com/<?php echo ltrim($info["handle"],"@");?>">@<?php echo ltrim($info["handle"],"@");?></a></h3>
</div>