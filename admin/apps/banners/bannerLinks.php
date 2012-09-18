<?php

include '../../../includes/init.php';
//require '../../classes/Editor.php';
//$db = new DB();
	
		// grab the referer's pageID
		$targetPageID = 0;
		preg_match('/navID\=([0-9]+)/', $_SERVER['HTTP_REFERER'], $tmp);
		if($tmp){
			$targetPageID = $tmp[1];
		}else if (isset($_POST['targetPageID'])){
			$targetPageID = $_POST['targetPageID'];
		}

$meta['title'] = 'Banner-Page Link Tool';
$meta['title_append'] = ' &bull; Quipp CMS';

$targetPage = array();
$targetPage['systemName'] = $db->return_specific_item($targetPageID, "sysNav", "pageSystemName");
$targetPage['label'] = $db->return_specific_item($targetPageID, "sysNav", "label");



/*
if (!isset($_GET['id'])) { $_GET['id'] = null; }

if (!isset($_POST['dbaction'])) {
	$_POST['dbaction'] = null;

	if (isset($_GET['action'])) {
		$_POST['dbaction'] = $_GET['action'];
	}
}
*/

if (!empty($_POST) && validate_form($_POST)) {
	//yell($_POST);
	$targetPageID = $_REQUEST['targetPageID'];
	
	//erase all previous links banners and this page
	$db->query(sprintf("DELETE FROM sysPageDataLink WHERE pageSystemName = '%s' AND appID = 'banners'", $targetPage['systemName']));
	
	//add a linking record for each banner that was checked off 
	if($_POST['bannerArray']){
		foreach($_POST['bannerArray'] as $key => $val) {
			$qry = sprintf("INSERT IGNORE INTO  sysPageDataLink(pageSystemName, appID, appItemID, sysDateCreated, sysStatus, sysOpen) VALUES ('%s', 'banners', '%d', NOW(), 'active', '1')",
				$targetPage['systemName'],
				(int) $val);
			$db->query($qry);
			//yell("print", $qry);
		}
	}
	
	
	header('Location: ' . "/admin/content.php?navID=" . $targetPageID);
	//exit('Redirecting...');
}

include "../../templates/header.php";

?>


<h1>Banner Linking Tool</h1>
<p>This allows the ability to place selected banners on specific pages of your website.<br />You can create new banners with the <a style="color:#666; text-decoration:underline;" href="/admin/apps/banners/">banner manager</a>.</p>

<div class="boxStyle">
	<div class="boxStyleContent">
		<div class="boxStyleHeading">
			<h2>Select banners to use on: <?php echo $targetPage['label'] . " (" . $targetPage['systemName'] . ")"; ?></h2>
			<div class="boxStyleHeadingRight">

			</div>
		</div>
		<div class="clearfix">&nbsp;</div>
		<div id="template">
			<form id="form1" name="form1" method="post" action="<?php print $_SERVER['PHP_SELF']; ?>">
				<table class="productsEditorTable" border="0px" cellspacing="0px" cellpadding="7px">
				<?php
				
					$ePQS = sprintf("SELECT * FROM sysPageDataLink WHERE pageSystemName = '%s' AND appID = 'banners';", $targetPage['systemName'] );
					$ePQ = $db->query($ePQS);
					if ($db->valid($ePQ)) {
						$existingBannerArray = array();
						while ($ePRS = $db->fetch_array($ePQ)) {
							array_push($existingBannerArray, $ePRS['appItemID']);
						}
					}
					$pQS = sprintf("SELECT DISTINCT b.* FROM `tblBanners` AS b 
					       INNER JOIN `tblBannerSiteLinks` AS bs ON b.`itemID` = bs.`bannerID` 
					       INNER JOIN `sysSitesInstances` AS si ON bs.`siteID` = si.`siteID` 
					       INNER JOIN `sysPage` AS p ON si.`itemID` = p.`instanceID` 
					       WHERE b.`sysOpen` = '1' AND b.`sysStatus` = 'active' AND p.`systemName` = '%s' ORDER BY title ASC",
					       $targetPage['systemName']
					       );
					$pQ = $db->query($pQS);
					$isItChecked = "";
					if($db->valid($pQ)){
						while ($pRS = $db->fetch_array($pQ)) {
							if (!empty($existingBannerArray)) {
								foreach($existingBannerArray as $ePA) {
									if($ePA == $pRS['itemID']) {
										$isItChecked = " checked=\"checked\"";
										break;
									} else {
										$isItChecked = "";
									}
								}
							}
							print "<tr>";
							print "<td><input type=\"checkbox\" name=\"bannerArray[".$pRS['itemID']."]\" id=\"banner".$pRS['itemID']."\" value=\"".$pRS['itemID']."\"".$isItChecked."></td>";
							print "<td style='vertical-align:top;'>".$pRS['title']."</td>";
							print "<td style='padding:0 0 0 10px'><img src=\"/bin/banners/".$pRS['photo']."\" alt=\"\" width=\"120px\" height=\"70px\" /></td>";
							print "</tr>"; 
						} 
					}
					
				?>
				</table>
				<div class="clearfix" style="margin-top: 10px; height:10px; border-top: 1px dotted #B1B1B1;">&nbsp;</div>
				<input type="hidden" name="targetPageID" id="targetPageID" value="<?php print $targetPageID; ?>" />
				
				<input type="button" name="cancelForm" id="cancelForm" class="btnStyle grey" onclick="javascript:window.location.href='/<?php print "../../admin/content.php?navID=" . $targetPageID; ?>';" value="Cancel" />
				<input type="submit" name="submitForm" id="submitForm" value="Save" class="btnStyle green" />
			</form>
		</div>
		
		</div>

		<div class="clearfix">&nbsp;</div>

	</div>

</div>

<?php


//end of display logic


include "../../templates/footer.php";




?>