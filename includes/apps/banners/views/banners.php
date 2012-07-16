<?php
if ($this INSTANCEOF Quipp && isset($this->siteID)){

    $sysName =  (isset($_GET["p"]) && trim($_GET["p"]) != "")?$db->escape($_GET["p"],true):"home"; 
    $bQuery = sprintf("SELECT b.* FROM `tblBanners` AS b 
    INNER JOIN `sysPageDataLink` AS dl ON b.`itemID` = dl.`appItemID` INNER JOIN `tblBannerSiteLinks` bl ON b.`itemID` = bl.`bannerID` 
    WHERE dl.`appID` = 'banners' AND dl.`sysStatus` = 'active' AND dl.`sysOpen` = '1' AND dl.`pageSystemName` = '%s' AND b.`sysOpen` = '1' AND b.`sysStatus` = 'active' AND bl.`siteID` = %d",
        $sysName,
        (int)$this->siteID
    );
    $bRes = $db->query($bQuery);
    if ($db->valid($bRes)){
        $numRecords = $db->num_rows($bRes);
?>

<section id="banners">
	
	<img class="bannerOverlay" src="/images/layout/banner1Overlay.png" alt="Banner 1 Overlay" />
	<div class="bannerContentWrap">
		<div class="bannerContent">
			<h2></h2>
			<p></p>
			<div class="bannerNav">
				<ul>
				    <?php
				        if ($numRecords > 1){
				            for ($i = 1; $i <= $numRecords; $i++){
				                $class = ($i == 1)?' class="current"':'';
				                echo '<li><a'.$class.' href="#">'.$i.'</a></li>';
					       }
					   }
					?>
				</ul>
				<?php if ($numRecords > 1){
				    echo '<a class="next" href="#">Next</a><a class="prev" href="#">Previous</a>';
				}
				else{
				    echo '&nbsp;<br />';
				}
				?>
			</div>
			<a class="bannerBtn" href="#"></a>
		</div>
	</div>
	<div class="bannerImg">
	   <?php
	       $p = 1;
	       while ($data = $db->fetch_assoc($bRes)){
	           $overlay = (trim($data["photo"]) == 'banner1.jpg')?true:false;
	           $display = ($p == 1)?'':'style="display:"none"';
	           echo '<img class="bannerImg '.$data['itemID'].'" data-title="'.stripslashes($data['title']).'" data-bodytext="'.stripslashes($data['body_text']).'" data-bannerlink="'.stripslashes($data['link']).'" data-overlay="'.$overlay.'" data-buttonlabel="'.stripslashes($data['buttonLabel']).'" src="bin/banners/'.$data['photo'].'" alt="" '.$display.'/>';
	           $p++;
	       }
	   ?>
	</div>

</section>
<?php
        global $quipp;
        $quipp->js['footer'][] = "/includes/apps/banners/js/banners.js";
    }
}
