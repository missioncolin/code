<?php
require_once("../includes/init.php");
require_once("../includes/apps/news/newsFeed.php");
$title = ucwords(str_replace(".com","",str_replace("www.","",$_SERVER["SERVER_NAME"])));
$description = "News from ".$title;
header('Content-Type: text/xml');
$nf = new newsFeeds($db,$quipp->siteID);
$nf->create_rss_items("tblNews","news","rss/newsFeed.php",$title,$description);