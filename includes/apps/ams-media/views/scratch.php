<?php
require '../../../../includes/init.php';
?>
<html>
<head> </head>

<body>

<?php
//the itemID of the video table
$userID = 0;
$jobID = 0;

if(!isset($_GET['videoID'])) {

$qry = sprintf("INSERT INTO tblVideos (userID, jobID, sysDateInserted, sysDateLastMod, reviewStatus) VALUES ('%d', '%d', NOW(), NOW(), 0)", 
        				(int)$userID,
        				(int)$jobID);
       //print $qry;
       $db->query($qry);
       $videoID = $db->insert_id();
} else {
    $videoID = (int) $_GET['videoID'];
}
       //print "VIDEO ID IS: " . $videoID;


//pull the video URL from the database
 $qry = sprintf("SELECT * FROM tblVideos 
        				WHERE itemID = '%d'", 
        				(int)$videoID);
       //print $qry;
       $vidResult = $db->query($qry);
       
      
       $vRS = $db->fetch_array($vidResult);

if(!empty($vRS['filename'])) { 
?>

<h1>Playback</h1>
<p>A video has been found stored in this location.</p>
<div id="mediaplayer">This site requires flashplayer. Please upgrade your plugins and install the latest flashplayer.</div>
	
	<script type="text/javascript" src="jwplayer.js"></script>
	<script type="text/javascript">
		jwplayer("mediaplayer").setup({
			flashplayer: "player.swf",
			streamer: "rtmp://media.intervue.ca/intervue/",
			file: "<?php echo $vRS['filename'] ?>",
			image: "preview.jpg"
		});
	</script>

<?php } else { ?>

<h1>Capture</h1>

<embed src="/includes/apps/ams-media/flx/captureModule.swf"
    quality="high"
    bgcolor="#000000"
    width="550"
    height="400"
    name="captureModule" FlashVars="itemID=<?php echo $videoID; ?>&securityKey=<?php echo md5("iLikeSalt" . $videoID); ?>"
    align="middle"
    allowScriptAccess="sameDomain"
    allowFullScreen="false"
    type="application/x-shockwave-flash"
    pluginspage="http://www.adobe.com/go/getflash"
/>
    <a href="?videoID=<?php echo $videoID;?>">When Done, View Your Captured Video</a>

<?php } ?>

</body>