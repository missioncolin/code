<?php

//test
class Content
{


	// get content
	// update content
	// delete content
	// new content
	// reorder

	function build_template($pageID, $templateID, $isDraft = true)
	{
		global $db;
		
		//get the templateID for this page record
		$tQry = sprintf("SELECT templateID FROM sysPage WHERE itemID = '%d';",
			(int) $pageID);
		$tRes = $db->query($tQry);
		$temp = $db->fetch_assoc($tRes);
		$templateID = $temp['templateID'];
		
		//get the templates for this page
		$tQry = sprintf("SELECT * FROM sysPageTemplateRegion WHERE sysOpen = '1' AND templateID = '%d' ORDER BY myOrder ASC;",
			(int) $templateID);
		$tRes = $db->query($tQry);
		
		$regionsArray = array();
		
		while ($region = $db->fetch_assoc($tRes)) {
			?>
			<div id="RegionBox_<?php print $region['itemID']?>" class="region" style="width:<?php print $region['adminDisplayWidth']; ?>%;">

				<div class="regionHead">
					<span class="regionTitle"><?php print $region['regionDisplayName']; ?></span>
					<?php if ($isDraft) { ?><a class="newLink fancybox iframe" href="/admin/box.php?pageID=<?php print $pageID; ?>&amp;regionID=<?php print $region['itemID']; ?>">New Box</a><?php } ?>
					<div class="clearfix">&nbsp;</div>
				</div>

				<ul id="region<?php print $region['itemID']; ?>" class="regionbox">
					
			<?php

			$regionsArray[$region['itemID']] = "#region" . $region['itemID'];
			$this->get_boxes($pageID, $region['itemID'], $isDraft);
			?>
			</ul>
		</div>
		<?php  
		
		}

		return $regionsArray;

	}

	function get_boxes($pageID, $regionID, $isDraft = true)
	{
		global $db;
		
		
		print '<li id="list_0" class="templateDisplay">&nbsp;</li>';
		
		$rQry = sprintf("SELECT c.*, r.pageID, r.regionID, r.itemID as regionContentID
			FROM sysPageContent AS c
			LEFT OUTER JOIN sysPageTemplateRegionContent AS r ON (c.itemID = r.contentID)
			WHERE c.sysOpen = '1'
			AND r.sysOpen = '1'
			AND r.pageID = '%d'
			AND r.regionID = '%d'
			ORDER BY r.myOrder;",
			(int) $pageID,
			(int) $regionID);
		$rRes = $db->query($rQry);

		while ($gcRS = $db->fetch_assoc($rRes)) {

			$class = "appDisplay";
			if ($gcRS['isAnApp'] != '1') {
				$class = "contentDisplay";
			} elseif ($gcRS['isProtected'] == '1') {
				$class = "protectDisplay";
			}
			
			?>
			<li id="list_<?php print $gcRS['itemID']; ?>" class="<?php print $class; ?>">
			<?php if ($isDraft) { ?>
			<a class="contentDeleteButton" id="contentDeleteButton_<?php print $gcRS['itemID']; ?>" href="javascript:UpdateDelete(<?php print $gcRS['itemID']; ?>,<?php print $gcRS['regionID']; ?>);" title="Delete">Delete</a>
			<?php } else { ?>
			<div class="contentDeleteButton">&nbsp;</div>
			<?php } 
			
			
			if ($gcRS['appPropertiesAdminLink'] != '') {
			?>
			<a class="contentPropertiesButton fancybox iframe" id="contentPropertiesButton_<?php print $gcRS['itemID']; ?>" href="<?php print $gcRS['appPropertiesAdminLink']; ?>?regionContentID=<?php print $gcRS['regionContentID']; ?>&pageID=<?php echo $pageID;?>" title="Properties">Properties</a>
			
			<?php
			
			}
            ?>
            
            <a class="contentPermissionsButton fancybox iframe" id="contentPermisssionsButton_<?php print $gcRS['itemID']; ?>" href="boxPermissionsEditor.php?regionContentID=<?php print $gcRS['regionContentID']; ?>&pageID=<?php echo $pageID;?>" title="Public Viewing Permissions">Public Viewing Permissions</a>

			
			<div id="content_<?php print $gcRS['itemID']; ?>" class="contentDisplayArea">

			<?php if ($gcRS['isAnApp'] != 1 && $isDraft) {  //must be regular content ?>
				<a class="contentEditButton fancybox iframe" id="contentEditButton_<?php $gcRS['itemID']; ?>" href="/admin/box.php?contentID=<?php print $gcRS['itemID']; ?>"><?php print str_shorten(clean($gcRS['adminTitle'], true), 50); ?></a>
			<?php } elseif ($gcRS['isProtected'] != 1 && $isDraft) { //must be an app ?>
				<a class="appEditButton" id="appEditButton_<?php print $gcRS['itemID']; ?>" href="<?php print $gcRS['appAdminLink']; ?>" ><?php print str_shorten(clean($gcRS['adminTitle'], true), 50); ?></a>
			<?php } else { ?>

				<span class="protectEditButton"><?php print str_shorten($gcRS['adminTitle'], 50); ?></span>
			<?php } ?>
			</div>
		</li>

	<?php 
		}
	}
	
	
	
	function get_content($contentID)
	{
		global $db;
		$res = $db->query($qs = "SELECT * FROM sysPageContent WHERE itemID = '$contentID' AND sysOpen ='1' ");
		//yell($qs);

		if ($db->valid($res)) {

			$rs = $db->fetch_assoc($res);
			return $rs;

		}

	}


	function insert_content($method, $contentID, $content = array())
	{
		global $db;
		
		$hideTitle = (isset($_POST['hideTitle']) && $_POST['hideTitle'] == '1') ? '1' : '0';

		if ($method == 'new') {
			$qry = sprintf("INSERT INTO sysPageContent (divBoxStyle, adminTitle, htmlContent, divHideTitle) VALUES ('%s', '%s', '%s', '%d')",
				$db->escape($_POST['boxStyle'], true),
				$db->escape($_POST['boxTitle'], true),
				$db->escape($_POST['boxBodyContent']),
				(int) $hideTitle);
			$db->query($qry);
			$contentID = $db->insert_id();
			
			// get the last order and put it last
			$qry = sprintf("SELECT IFNULL((MAX(myOrder) + 1), 0) as myOrder FROM sysPageTemplateRegionContent WHERE pageID='%d' AND regionID='%d' AND sysOpen='1'",
				(int) $_POST['pageID'],
				(int) $_POST['regionID']);
			$res = $db->query($qry);

			if ($db->valid($res)) { 
				
				$tmp = $db->fetch_assoc($res);			
				$qry = sprintf("INSERT INTO sysPageTemplateRegionContent (contentID, pageID, regionID, myOrder) VALUES ('%d', '%d', '%d', '%d')",
					$contentID,
					(int) $_POST['pageID'],
					(int) $_POST['regionID'],
					(int) $tmp['myOrder']);
				$db->query($qry);
			}
		
		} else if ($method == 'update') {
			$qry = sprintf("UPDATE sysPageContent SET divBoxStyle='%s', adminTitle='%s', htmlContent='%s', divHideTitle='%d' WHERE itemID = '%d' AND sysOpen = '1'",
				$db->escape($_POST['boxStyle'], true),
				$db->escape($_POST['boxTitle'], true),
				$db->escape($_POST['boxBodyContent']),
				(int) $hideTitle,
				(int) $contentID);
			$db->query($qry);

		}
		

	}
	
	function insert_app_widget($contentID, $pageID)
	{
		global $db;
		
		//need to determine the regionID based on the template that is being used (we determine this by querying the template to determine the master column which
		//is where apps go by default)
		$qry = sprintf("SELECT r.itemID FROM sysPageTemplateRegion AS r LEFT OUTER JOIN sysPageTemplate AS t ON(t.itemID = r.templateID) LEFT OUTER JOIN sysPage AS p ON(p.templateID = t.itemID) WHERE r.isDefault = '1' AND p.itemID = '%d'",
			(int) $pageID);
		$res = $db->query($qry);

		if ($db->valid($res)) { 

			$reg = $db->fetch_assoc($res);
			// get the last order and put it last
			$qry = sprintf("SELECT IFNULL((MAX(myOrder) + 1), 0) as myOrder FROM sysPageTemplateRegionContent WHERE pageID='%d' AND regionID='%d' AND sysOpen='1'",
				(int) $pageID,
				(int) $reg['itemID']);
			$res = $db->query($qry);

			if ($db->valid($res)) { 
				
				$tmp = $db->fetch_assoc($res);			
				$qry = sprintf("INSERT INTO sysPageTemplateRegionContent (contentID, pageID, regionID, myOrder) VALUES ('%d', '%d', '%d', '%d')",
					$contentID,
					(int) $pageID,
					(int) $reg['itemID'],
					(int) $tmp['myOrder']);
				$db->query($qry);
				
				$pageTemplateRegionContentID = $db->insert_id();
				$propertiesData = array("test", "another test");
				
				$this->set_app_properties($pageTemplateRegionContentID, $propertiesData);
				
				
				return true;
			}
		}
	}

	
	//application (widget) properties can be set on individual widgets specific to their placement
	function set_app_properties($pageTemplateRegionContentID, $propertiesData) 
	{
		global $db;
		
		$pd = json_encode($propertiesData);
		
		$qry = sprintf("INSERT INTO sysContentDataLink (pageTemplateRegionContentID, propertyData, sysDateCreated, sysOpen) VALUES ('%d', '%s', NOW(), '1') ON DUPLICATE KEY UPDATE propertyData = '%s'",
					$pageTemplateRegionContentID,
					$db->escape($pd),
					$db->escape($pd)
					);
					yell($qry);
				return $db->query($qry);
				
	}

	function reorder_boxes($pageID, $regionID, $boxes)
	{
		global $db;
		
		$qry = sprintf("DELETE FROM sysPageTemplateRegionContent WHERE pageID = '%d' AND regionID = '%d';",
			$pageID,
			$regionID);
		$db->query($qry);
		
		foreach ($boxes as $order => $contentID) {
			
			if ($contentID != '0') {
				$qry = sprintf("INSERT INTO sysPageTemplateRegionContent (contentID, pageID, regionID, myOrder, sysOpen) VALUES ('%d', '%d', '%d', '%d', '1')",
					$contentID,
					$pageID,
					$regionID,
					$order);
				$db->query($qry);
			}		
		}
	}



}

?>