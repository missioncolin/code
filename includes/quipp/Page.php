<?php

class Page extends Quipp {
	
	public $name;
	public $can_modify = false;
	public $template = '';
	
	function __construct($name)
	{
		global $db, $auth, $user;
		
		if (isset($user->id) && $user->id != false) {
		
			$this->can_modify = $auth->has_permission("modifypages");
		}
		parent::__construct();
		
		
		if (!$this->can_modify && isset($_GET['draft']) && $_GET['draft'] == "preview") {
			$auth->boot_em_out("preview");
			die();
			
		} elseif ($this->can_modify && isset($_GET['draft']) && $_GET['draft'] == "preview") {
		
			//view a draft version of this selected page.
			$qry = sprintf("SELECT itemID, systemName, label, pageDescription, masterHeading, templateID, isHomepage, isProtected, privID
				FROM sysPage
				WHERE sysOpen = '1' 
				AND sysVersion = 'draft' 
				AND systemName = '%s' 
				AND systemName IN (SELECT appItemID FROM sysSitesInstanceDataLink WHERE sysOpen = '1' AND sysStatus = 'active' AND appID = 'page' AND instanceID = '%d')
				ORDER BY sysStatus DESC, sysDateCreated DESC;",
					$db->escape($name),
					$this->instanceID);
			
		} elseif (empty($name)) { 
		
			//add a flag to show the default page (homepage)
			//view a draft version of this selected page.
			$qry = sprintf("SELECT itemID, systemName, label, pageDescription, masterHeading, templateID, isHomepage, isProtected, privID
				FROM sysPage
				WHERE isHomepage = '1'
				AND sysOpen = '1' 
				AND sysStatus = 'active'
				AND sysVersion = 'live'
				AND sysOpen = '1'  
				AND systemName IN (SELECT appItemID FROM sysSitesInstanceDataLink WHERE sysOpen = '1' AND sysStatus = 'active' AND appID = 'page' AND instanceID = '%d')
				ORDER BY sysStatus DESC, sysDateCreated DESC;",
					$this->instanceID);
		
		} else {
			if (preg_match("#[[:alnum:]]#", $name)) {
				
				//user has provided a page name, let's valdate that it's clean before we let it into our query
				$qry = sprintf("SELECT itemID, systemName, label, pageDescription, masterHeading, templateID, isHomepage, isProtected, privID
					FROM sysPage
					WHERE systemName = '%s' 
					AND sysStatus = 'active'
					AND sysVersion = 'live'
					AND sysOpen = '1' 
					AND systemName IN (SELECT appItemID FROM sysSitesInstanceDataLink WHERE sysOpen = '1' AND sysStatus = 'active' AND appID = 'page' AND instanceID = '%d')
					ORDER BY sysStatus ASC, sysDateCreated DESC;",
						$db->escape($name),
						$this->instanceID);
		
			} else {
			
				$this->display_404();
			}
		}

		
		if (isset($qry)) {
			
			//get the page data
			$res = $db->query($qry);

			if ($db->valid($res)) {	
				$this->info = $db->fetch_assoc($res);
	
				// Check Protected
				if ($this->info['isProtected'] == '1' && $auth->has_permission($db->return_specific_item($this->info['privID'], "sysPrivileges", "systemName")) == false) {
					if (isset($_SESSION['userID'])) {
						$auth->boot_em_out(3);
					} else {
						$auth->boot_em_out(0);
					}
				}
			
				if ($this->info['isHomepage'] != '1') {
					$qry = sprintf("UPDATE sysSitesInstanceDataLink SET viewCount=viewCount+1 
						WHERE sysOpen = '1' 
						AND sysStatus = 'active' 
						AND appID = 'page' 
						AND instanceID = '%d' 
						AND appItemID = '%s';",
							$this->instanceID,
							$this->info['systemName']);
					$db->query($qry);
				}
			
			
				//get the template for this page
				if ($qry = $db->result_please($this->info['templateID'], "sysPageTemplate", "pathToTemplate", false, false, false)) {
					$tRS = $db->fetch_assoc($qry);
					$this->template = $_SERVER['DOCUMENT_ROOT'] . $tRS['pathToTemplate'];
				
				} else {
					print alertBox("There was no template assigned to this page (you kinda need that to display anything). Please check the configuration for this page.", 3);
				}
				
			} else {
			
				$this->display_404();				
			}
		}	
	
	}
	
	function display_404() 
	{
		global $db;
	
		header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
		/*
		print '<pre style="text-align:left">';
		print_r($db->queries);
		print '</pre>';
		print $db->error();
		*/
		require_once $_SERVER['DOCUMENT_ROOT'] . '/404.html';
		$db->close();
		die();
	
	}
	
	
	
	
	function get_col_content($column)
	{
		global $db, $props, $auth;
	
		$qry = sprintf("SELECT ptrc.itemID AS contentInstanceID, pc.divBoxStyle, pc.divHideTitle, pc.adminTitle, pc.htmlContent, pc.includeOverride, pc.isAnApp
			FROM sysPageContent AS pc
			LEFT OUTER JOIN sysPageTemplateRegionContent AS ptrc ON (ptrc.contentID = pc.itemID)
			LEFT OUTER JOIN sysPageTemplateRegion AS ptr ON (ptr.itemID = ptrc.regionID)
			LEFT OUTER JOIN sysPageTemplate AS pt ON (pt.itemID = ptr.templateID)
			LEFT OUTER JOIN sysPage p ON (p.templateID = pt.itemID AND p.itemID = ptrc.pageID)
			WHERE ptr.regionName = '%s' 
			AND p.itemID = '%d' 
			AND pc.sysOpen = '1'
			ORDER BY ptrc.myOrder;",
				$db->escape($column),
				$this->info['itemID']);	
		$cCRes = $db->query($qry);

		if ($db->valid($cCRes)) {
			while ($cRS = $db->fetch_assoc($cCRes)) {

				if (!isset($cRS['divBoxStyle'])) { $cRS['divBoxStyle'] = false; }
				if (!isset($cRS['htmlContent'])) { $cRS['htmlContent'] = false; }
				if (!isset($cRS['divHideTitle'])) { $cRS['divHideTitle'] = false; }
				if (!isset($cRS['adminTitle'])) { $cRS['adminTitle'] = false; }
				
				//check to see if this box has a permission set on it
				//first make a systemName out of the itemID and contentInstanceID
				$permissionSystemName = "view_pageID_" . $this->info['itemID'] . "_pageTemplateRegionContentID_" . $cRS['contentInstanceID'];
				
				$privID = $db->return_specific_item(false, "sysPrivileges", "itemID", false, "systemName = '".$permissionSystemName."'");
				//yell($permissionSystemName . " (privID) -> " . $privID);
				if($privID) {
					if($auth->has_permission($permissionSystemName)) {
						//yell("Has Permission (T)? " . $auth->has_permission($permissionSystemName));
						$printContent = true;
					} else {
						//yell("Has Permission (F)? " . $auth->has_permission($permissionSystemName));
						$printContent = false;
					}
				} else {
					$printContent = true;
				}
				
				//yell("User is carrying a groupIDMask: " . $_SESSION['groupIDMask']);
				
				//one last check, if there is a group override variable called groupIDMask
				if(isset($_SESSION['groupIDMask']) && is_numeric($_SESSION['groupIDMask'])) {
					if($auth->group_has_permission($permissionSystemName, $_SESSION['groupIDMask'])) {
						$printContent = true;
						//yell("User is carrying a groupIDMask, let's display the content.");
					}
				}
				
				//$auth->has_permission("modifypages");
				//if it does, check to see if the user 
				
				//yell($permissionSystemName);
	
				if (empty($cRS['includeOverride']) && $printContent) {
					
					//this is regular content
					if (strpos($cRS['divBoxStyle'], "plainBox")) { 
						$cRS['htmlContent'] = "<div class=\"innerBoxFade\">" . $cRS['htmlContent'] . "</div>";
					}
					if ($cRS['divHideTitle'] == 1) { 
						$cRS['adminTitle'] = false;
					}
	
					$return  = $this->print_box($cRS['divBoxStyle'], $cRS['adminTitle'], $cRS['htmlContent']);
					$return .= "<div class=\"clear\">&nbsp;</div>";
					echo $return;
					
				} elseif($printContent) {
				
					//if this is an app, check to see if there is property data for this placementID
					$props = $db->return_specific_item(false, "sysContentDataLink", "propertyData", false, "pageTemplateRegionContentID = '".$cRS['contentInstanceID']."'");
					if($props) {
						$props = json_decode($props, true);
					} 
					
					include $_SERVER['DOCUMENT_ROOT'] . $cRS['includeOverride'];
				}
	
			}
	
		}
	
	
		return '<!-- end content for ' . $column . ' -->';
	
	}
	
	
	/**
	 * GET DIV BOX
	 * generates and returns a content box on-demand, this should be used for all boxes,
	 * style with the class parameters
	 */
	function print_box($class, $header, $content, $linkURL = '', $linkDisplay = '', $width = '100%', $button = false, $expandable = false)
	{
		
		global $jsFooter;
		
		$myRandID = substr(rand(), 0, 3);


		$forReturn = "
			<div class=\"$class\" style=\"width:$width;\"> <!-- Master Div -->
				<div class=\"". $class . "MainHeader\">
					<h2>";
		if ($expandable > 2) {
			$forReturn .= "<a style=\"cursor:pointer;\" id=\"clickLink" . $myRandID . "\">";
		}
	
		$forReturn .= $header;
	
		if ($expandable > 2) { $forReturn .= "</a>"; }
	
		$forReturn .= "</h2>
					<div class=\"". $class . "HeaderCap\"><div class=\"". $class . "HeaderCapContent\"> <!-- Header Div -->";
	
		if (strtolower(substr($linkURL, 0, 10)) == "plaintext:") {
			$forReturn .= substr($linkURL, 10);
		}
		elseif (!empty($linkURL)) {
			if ($button) {
				if ($linkURL == "Submit") {
					$forReturn .= "<input type=\"submit\" name=\"submit\" value=\"$linkDisplay\" />";
				} else {
					$forReturn .= "<input type=\"button\" onClick=\"$linkURL\" value=\"$linkDisplay\" />";
				}
			} else {
	
				$forReturn .= "<a href=\"$linkURL\">$linkDisplay</a>";
			}
	
		} elseif (!empty($linkDisplay)) {
			$forReturn .= $linkDisplay;
		}
	
		if ($expandable != false) {
	
			$expandableArrow = $expandable;
	
			if ($expandableArrow > 2) { $expandableArrow -= 2; }
	
			$forReturn .= "<a id=\"arrow" . $myRandID . "\" style=\"cursor:pointer\"><img id=\"myTogImg".$myRandID."\" src=\"/images/arrow". $expandableArrow .".gif\" alt=\"expandContent\" /></a>";
			$jsFooter  .= sprintf("$('#arrow%s').click(function(){\$('#box%s').slideToggle('slow').parent().children('.%sFooter').slideToggle('slow');toggleArrow('myTogImg%s');});",
				$myRandID,
				$myRandID,
				$class,
				$myRandID);
	
		}
	
	
		$forReturn .= "</div></div> </div><!-- End Of Header Div --> ";
	
	
	
	
	
		if ($expandable == 1 || $expandable == 3 || !$expandable) {
			$myBlockPref = "block";
		} else {
			$myBlockPref = "none";
			$jsFooter  .= sprintf("$('#box%s').slideToggle('fastest').parent().children('.%sFooter').slideToggle('fastest');toggleArrow('myTogImg%s');",
				$myRandID,
				$class,
				$myRandID);
		}
	
		$forReturn .= "<div id=\"box". $myRandID ."\" style=\"display:$myBlockPref;\" class=\"". $class . "BodyMain\"><div class=\"". $class . "BodyMainCap\">$content" .
			"<div class=\"clearfix\">&nbsp;</div></div></div><!-- End Of Body Div -->
	
			<div class=\"". $class . "Footer\"><div class=\"". $class . "FooterCap\">&nbsp;</div></div>
			</div><!-- End Of Master Div -->";
	
	
	
		return $forReturn;

	}



	/**
	 * PHP CSS Browser Selector v0.0.1
	 * Bastian Allgeier (http://bastian-allgeier.de)
	 * http://bastian-allgeier.de/css_browser_selector
	 * License: http://creativecommons.org/licenses/by/2.5/
	 * Credits: This is a php port from Rafael Lima's original Javascript CSS Browser Selector: http://rafael.adm.br/css_browser_selector
	 */
	
	function body_class($b = array()) {
		$ua = strtolower($_SERVER['HTTP_USER_AGENT']);		

		$g = 'gecko';
		$w = 'webkit';
		$s = 'safari';
		
		// browser
		if(!preg_match('/opera|webtv/i', $ua) && preg_match('/msie\s(\d)/', $ua, $array)) {
			$b[] = 'ie ie' . $array[1];
		} else if(strstr($ua, 'firefox/2')) {
			$b[] = $g . ' ff2';		
		} else if(strstr($ua, 'firefox/3.5')) {
			$b[] = $g . ' ff3 ff3_5';
		} else if(strstr($ua, 'firefox/3')) {
			$b[] = $g . ' ff3';
		} else if(strstr($ua, 'gecko/')) {
			$b[] = $g;
		} else if(preg_match('/opera(\s|\/)(\d+)/', $ua, $array)) {
			$b[] = 'opera opera' . $array[2];
		} else if(strstr($ua, 'konqueror')) {
			$b[] = 'konqueror';
		} else if(strstr($ua, 'chrome')) {
			$b[] = $w . ' ' . $s . ' chrome';
		} else if(strstr($ua, 'iron')) {
			$b[] = $w . ' ' . $s . ' iron';
		} else if(strstr($ua, 'applewebkit/')) {
			$b[] = (preg_match('/version\/(\d+)/i', $ua, $array)) ? $w . ' ' . $s . ' ' . $s . $array[1] : $w . ' ' . $s;
		} else if(strstr($ua, 'mozilla/')) {
			$b[] = $g;
		}

		// platform				
		if(strstr($ua, 'j2me')) {
			$b[] = 'mobile';
		} else if(strstr($ua, 'iphone')) {
			$b[] = 'iphone';		
		} else if(strstr($ua, 'ipod')) {
			$b[] = 'ipod';		
		} else if(strstr($ua, 'mac')) {
			$b[] = 'mac';		
		} else if(strstr($ua, 'darwin')) {
			$b[] = 'mac';		
		} else if(strstr($ua, 'webtv')) {
			$b[] = 'webtv';		
		} else if(strstr($ua, 'win')) {
			$b[] = 'win';		
		} else if(strstr($ua, 'freebsd')) {
			$b[] = 'freebsd';		
		} else if(strstr($ua, 'x11') || strstr($ua, 'linux')) {
			$b[] = 'linux';		
		}
				
		return join(' ', $b);
		
}



}

?>