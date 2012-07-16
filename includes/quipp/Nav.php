<?php

class Nav
{

	public $recordsToMod = array();

	function __construct($bucket = false)
	{


	}


	//this should never be called externally
	function update_nav_item($id, $parentID, $bucketID, $myOrder)
	{
		global $quipp, $db;

		if(!is_numeric($id)) {
			$id = substr($id, 1); //take the 'type' identifier off the front end eg. L25 becomes 25
		}

		if(!$parentID && !$myOrder) { //must be a bucket update only
			$qry = sprintf("UPDATE sysNav SET bucketID = '%d' WHERE itemID = '%d';",
				$db->escape($bucketID),
				$db->escape($id)
			);
			//yell($qry);
			if($db->query($qry)) {
				return true;
			} else {
				return false;
			}
		} else {

			$qry = sprintf("UPDATE sysNav SET parentID = '%d', bucketID = '%d', myOrder = '%d' WHERE itemID = '%d';",
				$db->escape($parentID),
				$db->escape($bucketID),
				$db->escape($myOrder),
				$db->escape($id)
			);
			//yell($qry);
			if($db->query($qry)) {
				return true;
			} else {
				return false;
			}


		}


	}

	function update_nav_property($id, $fieldName, $value)
	{
		global $quipp, $db;

		//setting this to true for now, will be used for future expansion similar to update_page_property in /admin/classes/PageUtility.php
		$runDefaultQuery = true;
		
		if($runDefaultQuery) {

			$qry = sprintf("UPDATE sysNav SET %s = '%s' WHERE itemID = '%d';",
				$db->escape($fieldName),
				$db->escape($value),
				$db->escape($id)
			);


			if($db->query($qry)) {
				return true;
			} else {
				return false;
			}
		}


	}


	//expects l23, b293 (or l29), 3
	function move_nav_item_recursive($id, $parentID, $myOrder, $siblings = false)
	{

		global $quipp, $db;

		if($siblings) {
			
			//this will populate a variable called $so 'siblingOrder' with the data from the query string style data from $siblings
			//$sO is looped below at the end of this function to update the order of each of the siblings
			parse_str($siblings, $sO);
			//yell($sO);
		}

		//yell("move_nav_item_recursive(".$id.", ".$parentID.", ".$myOrder.")");

		if(!is_numeric($id)) {
			$id = substr($id, 1); //take the 'type' identifier off the front end eg. L25 becomes 25
		}

		if(!is_numeric($parentID)) {

			if(substr($parentID, 0, 1) == "l") { //parent type is link
				$parentID = substr($parentID, 1); //take the 'type' identifier off the front end eg. L25 becomes 25
				$bucketID = $db->return_specific_item($parentID, "sysNav", "bucketID");
			} else { //parent type must be a bucket
				$bucketID = substr($parentID, 1);
				$parentID = 0; //if the parent is a bucket the parentID is always 0
			}

			$currentBucketID = $db->return_specific_item($id, "sysNav", "bucketID");


			//check whether we're doing a bucket change, if so we'll need to run a recursive bucket update run
			if($currentBucketID != $bucketID) {


				//get the details from the bucket that holds this nav item currently, we need this to walk the bucket of children if they exist
				$qry = sprintf("SELECT * FROM sysNavBuckets WHERE itemID = '%d';", (int) $currentBucketID);
				$bucRes = $db->query($qry);

				if($db->valid($bucRes)) {
					$buc = $db->fetch_assoc($bucRes);

					$parentBucketName = $buc["layoutReferenceName"];
					$instanceID = $buc["instanceID"];


					//flush the array
					$this->recordsToMod = array();
					$navItemChildren = $this->get_nav_items_under_bucket($parentBucketName, 1000, $instanceID, $id, "everything");

					//yell($navItemChildren);

					if(is_array($navItemChildren)) {
						array_walk_recursive($navItemChildren, array(&$this, 'filter_id_from_child_array'));
					}

					//update children
					if(is_array($navItemChildren)) {
						foreach ($this->recordsToMod as $key=>$value) {
							//yell("Trying to update position on " . $value . " to bucketID: " .  $bucketID);
							//array_search($value,$sO)
							$this->update_nav_item($value, false, $bucketID, false);
						}
					}
				}



			}

			//update the item idividually
			//this will get updated twice as a failsafe in case the item is an only child
			$this->update_nav_item($id, $parentID, $bucketID, $myOrder);
			
			//because we have moved an item, we must update the orders of all of the siblings
			foreach($sO['siblings'] as $order => $keyArray) {
				
				if(!is_numeric($keyArray['id'])) {
					$id = substr($keyArray['id'], 1); //take the 'type' identifier off the front end eg. L25 becomes 25
					//yell("setting " . $id . " to order of " . $order);
					$this->update_nav_item($id, $parentID, $bucketID, $order);
				}
			}
			

		}

	}

	
	function create_empty_nav_item($underThisParentID = 1, $parentType="bucket")
	{
		global $quipp, $db, $nav;
		

		//yell("CREATE EMPTY PAGE");
		switch ($parentType) {
		case "bucket":
			//yell("CREATE EMPTY PAGE BUCKET!");
			//get the instance ID based on the bucket
			$qry = sprintf("SELECT instanceID FROM sysNavBuckets WHERE itemID = '%d'",
				$db->escape($underThisParentID));
			$res = $db->query($qry);
			$rs = $db->fetch_assoc($res);
			$pageInstanceID = $rs['instanceID'];
			$navItemParentID = 0;

			if(!is_numeric($pageInstanceID)) {
				return false;
			}

			$bucketID = $underThisParentID;

			break;
		case "nav":

			//get the instance ID based on the bucket via the nav item
			$qry = sprintf("SELECT instanceID FROM sysNavBuckets WHERE itemID = (SELECT bucketID FROM sysNav WHERE itemID = '%d')",
				$db->escape($underThisParentID));
			//yell($qry);
			$res = $db->query($qry);
			$rs = $db->fetch_assoc($res);
			$pageInstanceID = $rs['instanceID'];

			if(!is_numeric($pageInstanceID)) {
				return false;
			}

			//get the bucket ID via the nav item
			$qry = sprintf("SELECT bucketID FROM sysNav WHERE itemID = '%d';",
				$db->escape($underThisParentID));
			$res = $db->query($qry);
			$rs = $db->fetch_assoc($res);
			$bucketID = $rs['bucketID'];
			$navItemParentID = $underThisParentID;
			if(!is_numeric($bucketID)) {
				return false;
			}

			break;
		}

		//yell("CREATE EMPTY PAGE A: " . $bucketID);

		if($navID = $nav->create_nav_item($bucketID, $navItemParentID, 0, "", "http://", "_self")) {
			return $navID;	
		} else {
			return false;
		}
	}

	
	
	
	/**create_nav_item()
	 * creates a nav item that is by default, inactive. It will return the nav id of the item it creates.
	 */

	function create_nav_item($bucketID = "1", $parentID = "0", $myOrder = "0", $pageSystemName = "", $url = "NULL", $target = "", $label = "Untitled")
	{
		global $quipp, $db;

		if($url != "NULL") { $url = "'" . $db->escape($url) . "'"; }

		$qry = sprintf("INSERT INTO sysNav (bucketID, parentID, myOrder, pageSystemName, url, target, label, sysStatus, sysOpen) VALUES ('%d', '%d', '%d', '%s', %s, '%s', '%s', 'inactive', '1');",
			$db->escape($bucketID),
			$db->escape($parentID),
			$db->escape($myOrder),
			$db->escape($pageSystemName),
			$url,
			$db->escape($target),
			$db->escape($label)
		);
		//yell($qry);
		
		if($db->query($qry)) {
			return $db->insert_id();
		} else {
			return false;
		}
	}


	function rename_nav_item($id, $newName)
	{
		global $quipp, $db;

		if(!is_numeric($id)) {
			$id = substr($id, 1); //take the 'type' identifier off the front end eg. L25 becomes 25
		}
		$qry = sprintf("UPDATE sysNav SET label = '%s' WHERE itemID = '%d';",
			$db->escape($newName),
			$db->escape($id)
		);
		//yell($qry);
		if($db->query($qry)) {
			return true;
		} else {
			return false;
		}

	}


	function activate_nav_item($id)
	{
		global $quipp, $db, $pageUtility;

		if(!isset($pageUtility)) {
			require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/classes/PageUtility.php";
			$pageUtility = new PageUtility($db);
		}

		if(!is_numeric($id)) {
			$id = substr($id, 1); //take the 'type' identifier off the front end eg. L25 becomes 25
		}

		$qry = sprintf("UPDATE sysNav SET sysStatus = 'active' WHERE itemID = '%d';",
			$db->escape($id)
		);

		//yell($qry);
		if($db->query($qry)) {

			//activate the page too
			//we're updating only the one where checkOutID IS NULL (which will be the live one)
			$qry = sprintf("UPDATE sysPage SET sysStatus = 'active' WHERE systemName = '%s';",
				$db->escape($db->return_specific_item($id, "sysNav", "pageSystemName"))
			);

			if($db->query($qry)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}

	}


	function deactivate_nav_item($id)
	{
		global $quipp, $db;

		if(!is_numeric($id)) {
			$id = substr($id, 1); //take the 'type' identifier off the front end eg. L25 becomes 25
		}

		$qry = sprintf("UPDATE sysNav SET sysStatus = 'inactive' WHERE itemID = '%d';",
			$db->escape($id)
		);
		if($db->query($qry)) {

			//activate the page too
			//we're updating only the one where checkOutID IS NULL (which will be the live one)
			$qry = sprintf("UPDATE sysPage SET sysStatus = 'inactive' WHERE systemName = '%s';",
				$db->escape($db->return_specific_item($id, "sysNav", "pageSystemName"))
			);

			if($db->query($qry)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}


	}




	function filter_id_from_child_array($item, $key)
	{
		if($key == 'id') {
			$this->recordsToMod[] = $item;
			//yell("Would be adding: " . $key . "->" . $item);
		}
	}


	function delete_nav_item_recursive($id)
	{

		global $quipp, $db;

		if(!is_numeric($id)) {
			$id = substr($id, 1); //take the 'type' identifier off the front end eg. L25 becomes 25
		}


		//get the details from this nav item's record
		$qry = sprintf("SELECT * FROM sysNav WHERE itemID = '%d';", (int) $id);
		$navRes = $db->query($qry);

		if($db->valid($navRes)) {

			$nav = $db->fetch_assoc($navRes);

			//get the details from the bucket that holds this nav item, we need this to walk the bucket of children if they exist
			$qry = sprintf("SELECT * FROM sysNavBuckets WHERE itemID = '%d';", (int) $nav['bucketID']);
			$bucRes = $db->query($qry);

			if($db->valid($bucRes)) {
				$buc = $db->fetch_assoc($bucRes);

				//we need to determine 'where' this item is first so that we can re-use some of the functions to walk the nav and do a recursive delete

				$parentBucketName = $buc["layoutReferenceName"];
				$instanceID = $buc["instanceID"];

				//flush the array
				$this->recordsToMod = array();
				$navItemChildren = $this->get_nav_items_under_bucket($parentBucketName, 1000, $instanceID, $id, "everything");

				//yell($navItemChildren);

				if(is_array($navItemChildren)) {
					array_walk_recursive($navItemChildren, array(&$this, 'filter_id_from_child_array'));
				}

				//delete children
				if(is_array($navItemChildren)) {
					foreach ($this->recordsToMod as $key=>$value) {
						$this->delete_nav_item($value);
					}
				}

				//then delete me
				if($this->delete_nav_item($id)) {
					return true;
				}

			} else {
				return false;

			}

		} else {
			return false;
		}
	}


	//this is *not* recursive, it just deletes the single nav item, and it's associated page if one exists
	function delete_nav_item($id)
	{
		global $quipp, $db;

		if(!is_numeric($id)) {
			$id = substr($id, 1); //take the 'type' identifier off the front end eg. L25 becomes 25
		}

		$qry = sprintf("UPDATE sysNav SET sysOpen = '0' WHERE itemID = '%d';",
			$db->escape($id)
		);
		if($db->query($qry)) {
			if($db->return_specific_item($id, "sysNav", "pageSystemName") != "--") {
				//delete the page too
				$qry = sprintf("UPDATE sysPage SET sysOpen = '0' WHERE systemName = '%s';",
					$db->escape($db->return_specific_item($id, "sysNav", "pageSystemName"))
				);

				if($db->query($qry)) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		} else {
			return false;
		}


	}


	/**get_everything()
	 * returns the entire structure (including site, instances, and buckets, and nav items as a nested array)
	   This method is used primarially by the Admin nav editor, but could also be used as a source for Site Maps
	   This method uses get_nav_items_under_bucket() for nav items and anything deeper than the bucket level
	 */
	function get_everything()
	{
		global $quipp, $db;
		$qry = sprintf("SELECT s.itemID, s.title FROM sysSites AS s LEFT JOIN sysUSites AS us ON s.itemID = us.siteID WHERE s.sysOpen = '1' AND us.userID='%d' GROUP BY s.itemID ORDER BY s.myOrder", $_SESSION['userID']);
		$res = $db->query($qry);

		if($db->valid($res)) {
			$s=0;
			$thisSItem = array();
			while ($site = $db->fetch_assoc($res)) {

				//add this site to a new array (this 'S[ite]' item)
				$thisSItem[$s]['attr'] = array('id' => 's' . $site['itemID'], 'rel' => 'site');
				$thisSItem[$s]['data'] = array('title' => "" . $site['title']);

				//check to see what instances are under this site
				$qry = sprintf("SELECT i.*, l.title FROM sysSitesInstances AS i LEFT OUTER JOIN sysSitesLanguages AS l ON(i.languageID = l.itemID) WHERE i.sysStatus='active' AND i.siteID = '%d' AND i.sysOpen = '1';", (int) $site['itemID']);


				$insres = $db->query($qry);

				if($db->valid($insres)) {
					//init an instance count
					$i=0;
					$thisIItem = array();
					while ($ins = $db->fetch_assoc($insres)) {

						//add this instance to a new array (this 'I[nstance]' item)
						$thisIItem[$i]['attr'] = array('id' => 'i' . $ins['itemID'], 'rel' => 'instance');
						$thisIItem[$i]['data'] = array('title' => "" . $ins['title'] . " (Instance)");

						//get the buckets for this site
						$qry = sprintf("SELECT label, layoutReferenceName, itemID FROM sysNavBuckets WHERE instanceID = '%d' AND sysOpen = '1' ORDER BY myOrder;", (int) $ins['itemID']);
						//yell($qry);
						$bucketres = $db->query($qry);

						if($db->valid($bucketres)) {
							//init an bucket count
							$b=0;
							$thisBItem = array();
							while ($buc = $db->fetch_assoc($bucketres)) {

								//add this instance to a new array (this 'B[ucket]' item)
								$thisBItem[$b]['attr'] = array('id' => 'b' . $buc['itemID'], 'rel' => 'bucket');
								$thisBItem[$b]['data'] = array('title' => "" . $buc['label']);

								//heavy lifting - adding all of the children (1000 levels deep to the
								//yell($buc['layoutReferenceName'] . "-" . 1000 . "-" . $ins['itemID']);
								$thisBItem[$b]['children'] = $this->get_nav_items_under_bucket($buc['layoutReferenceName'], 1000, $ins['itemID'], 0, "everything");

								//increment the bucket counter
								$b++;
							}

							$thisIItem[$i]['children'] = $thisBItem;
						}

						//increment the instance counter
						$i++;
					}

					//add the instances (array) to their parent site in it's children place holder
					$thisSItem[$s]['children'] = $thisIItem;
				}

				//increment the site counter
				$s++;
			}

		}

		return $thisSItem;
	}


	/**get_nav_items_under_bucket()
	 * returns all of the items (as a nested array) with a provided bucket,instance (assumes 0 parentID which is first level)
	 */

	function get_nav_items_under_bucket($bucketName, $recursionLevel = 1000, $instanceID = false, $parentID = 0, $detailLevel = "frontend", $flat = false, $level = 'top')
	{
		//detailLevel defaults to frontend which delivers just pages and links, 'everything' returns inactive pages and extra details in the attr fields

		global $quipp, $db;

		if(!$instanceID) {
			$instanceID = $quipp->instanceID;
		}

		$tmp = '';
		if($detailLevel == 'frontend') {
			$tmp = " AND n.sysStatus = 'active'";
		}
		$qry = sprintf("SELECT n.itemID, n.sysStatus, n.parentID, n.pageSystemName, n.url, n.target, n.label
			FROM sysNav AS n
			LEFT OUTER JOIN sysNavBuckets AS b ON(n.bucketID = b.itemID)
			WHERE b.instanceID = '%d'
			AND b.layoutReferenceName = '%s'
			AND n.parentID = '%d'
			AND n.sysOpen = '1'%s
			ORDER BY n.myOrder;",
			(int) $instanceID,
			(string) $bucketName,
			(int) $parentID,
			$tmp);
		$navres = $db->query($qry);


		if($db->valid($navres)) {
			//init an bucket count
			$n=0;
			$thisNItem = array();
			while ($nav = $db->fetch_assoc($navres)) {

				//assume page
				$type = "page";

				//if a URL is present, this is an outbound link
				if(!empty($nav['url'])) { //TO DO: create new outbound-inactive
					$type = "outbound";
				}

				//if the nav item record is inactive, cat and send back a flag as inactive
				if($nav['sysStatus'] != 'active') {
					$type .= "inactive";  // outbound[inactive] or page[inactive]
				} elseif(empty($nav['url'])) { //the rest of these checks are for active 'pages' only
					//this in an active item, let's probe deeper to see if it's special somehow (inactives don't get flagged as protected/home)

					//right now, we only care about these items if we're pulling the full detail level
					//do a check against the page to determine home page, protected pages
					//this is so we can send through different 'rel' types for icon and functionality hooks in the back-end nav editor

					if($type == "page") { //if this is a page
						//determine if this is the homepage or if it's protected

						if($detailLevel == 'frontend') {
							$tmp = " AND sysStatus = 'active' AND sysVersion='live'";
						}
						$qry = sprintf("SELECT systemName, isHomepage, isDevLocked, isProtected
							FROM sysPage
							WHERE systemName = '%s'
							AND sysOpen = '1'%s;",
							(string) $nav['pageSystemName'],
							$tmp);

						$pageres = $db->query($qry);

						if($db->valid($pageres)) {
							$page = $db->fetch_assoc($pageres);


							if($detailLevel == "everything") {

								if($page['isHomepage']) {
									$type = "homepage";

								}
								//cat a protected flag on the end of this type if it is protected (only pages can be 'protected')
								if($page['isProtected']) {
									$type .= "protected";
								}
							}
						} else {
							continue;
						}
					}
				}

				if($detailLevel == "frontend") {

					$thisNItem[$n] = array(
						'url'   => $nav['url'],
						'slug'   => $nav['pageSystemName'],
						'label'  => $nav['label'],
						'target' => $nav['target'],
						'level'  => $level
					);
				} else {

					//add this instance to a new array (this 'B[ucket]' item)
					$thisNItem[$n]['attr'] = array('id' => 'l' . $nav['itemID'], 'rel' => $type);
					$thisNItem[$n]['data'] = array('title' => "" . $nav['label']);

				}

				if($recursionLevel > 0) {
					$recursionLevel--;
					$childrenResult = $this->get_nav_items_under_bucket($bucketName, $recursionLevel, $instanceID, $nav['itemID'], $detailLevel, $flat, 'sub');
					if($childrenResult) {
						if ($flat == true) {
							$thisNItem[$n]['children'] = count($childrenResult);
							foreach ($childrenResult as $child) {
								$n++;
								$thisNItem[$n] = $child;
							}
							
						} else {
							$thisNItem[$n]['children'] = $childrenResult;
						}
					}
				}
				//increment the bucket counter
				$n++;
			}


			return $thisNItem;
		} else {
			return false;
		}

	}



	function build_nav($navigation, $page = '', $list = true, $dropdown = false)
	{
		global $db, $quipp;

		if(!is_array($navigation)) {
			return false;
		}
		$toReturn = '';
		if($list === true) {
			if ($dropdown == true) {
				$toReturn .= '<ul class="sf-menu">';
			} else {
				$toReturn .= '<ul>';
			}
		}
		foreach ($navigation as $nav) {
			
			if (isset($nav['class'])) {
				$selected = ($page == $nav['slug']) ? ' class="current ' . $nav['class'] . '"' : ' class="' . $nav['class'] . '"';
			} else {
				$selected = ($page == $nav['slug']) ? ' class="current"' : '';
			}
			
			
			$url   = (!empty($nav['url'])) ? $nav['url'] : '/' . $nav['slug'];

			$nav['target'] = (!empty($nav['target'])) ? 'target="' . $nav['target'] . '" ' : '';

			if($list == true) {
				$toReturn .= '<li' . $selected . '>';
				$selected  = '';
			}
			$toReturn .= '<a ' . $nav['target'] . 'href="' . $url . '"' . $selected. '>' . $nav['label'] . '</a>';

			if(isset($nav['children'])) {
				$toReturn .= $this->build_nav($nav['children'], $page);
			}
			if($list == true) {
				$toReturn .= '</li>';
			} else {
				$toReturn .= ' | ';
			}
		}
		if($list == true) {
			$toReturn .= '</ul>';
		}
		else{
			$toReturn = rtrim($toReturn," | ");
		}
		return $toReturn;

	}
	
	function build_nav_buckets($navigation, $page = '', $columns = false, $rows = false, $list = true) 
	{
		if ($columns == false && $rows == false) {
			return false;
		}
		
		if (is_numeric($columns)) {
			
			
			$groups = partition($navigation, $columns);
						
		} else if (is_numeric($rows)) {
			
			$total     = ceil(count($navigation) / $rows);
	
			for ($i=1; $i<=$total; $i++) {
				for ($j=1; $j<=$rows; $j++) {
					if (!empty($navigation)) {
						$groups[$i][] = array_pop($navigation);
				  	}
				}
			}
	
			if ((count($groups[$i-1]) / $rows) < 0.4 && $adjust == true) {
				$j = 1;
				foreach ($groups[$i-1] as $name) {
					if ($j == ($i - 1)) { 
						$j = 1;
					}
					$groups[$j][] = array_pop($groups[$i-1]);
					$j++;	
				}
				unset($groups[$i-1]);
			}
					
		}
		
		
		$toReturn = '<div style="clear:both;">';
		
		if (is_array($groups)) {
		foreach($groups as $column) {
			$toReturn .= '<ul style="float:left;">';
			foreach ($column as $nav) {
				$selected = ($page == $nav['slug']) ? ' class="current"' : '';
				$url   = (!empty($nav['url'])) ? $nav['url'] : '/' . $nav['slug'];
	
				$nav['target'] = (!empty($nav['target'])) ? 'target="' . $nav['target'] . '" ' : '';
	
				if($list == true) {
					$toReturn .= '<li' . $selected . '>';
					$selected  = '';
				}
				if ($nav['level'] == 'top') {
					$selected = ' class="bold"';
				}
				$toReturn .= '<a ' . $nav['target'] . 'href="' . $url . '"' . $selected. '>' . $nav['label'] . '</a>';
				
	
				
				if($list == true) {
					$toReturn .= '</li>';
				}

			}
			$toReturn .= '</ul>';
		}
		}
		
		return $toReturn . '</div>';
	
	}


	



	/**
	 * Build a breadcrumb based on the current slug
	 * @param string
	 * @return array
	 */

	function breadcrumb($pageID, $return = 'link', $original = true, $separator = ' &gt; ')
	{
		global $db, $quipp;
		
		$breadcrumb = array();
		// grab the current label
		$qry = sprintf("SELECT p.itemID, n.parentID, n.label, n.pageSystemName as slug, p.isHomepage
			FROM sysNav AS n
			LEFT JOIN sysPage AS p ON (n.pageSystemName=p.systemName)
			WHERE p.sysStatus='active'
			AND p.sysVersion='live'
			AND p.sysOpen='1'
			AND n.sysOpen='1'
			AND n.sysStatus='active'
			AND p.itemID='%d'",
				(int) $pageID);
		$res = $db->query($qry);

		if ($db->valid($res)) {
			list($pageID, $parentID, $label, $slug, $isHomepage) = $db->fetch_array($res);
			array_push($breadcrumb, array('url' => '/' . $slug, 'label' => $label, 'home'=>$isHomepage));
			
			
			// check for parents
			if ($parentID > 0) {
			
				// get the page ID associated with the nav parent
				$qry = sprintf("SELECT p.itemID
					FROM sysNav AS n
					LEFT JOIN sysPage AS p ON (n.pageSystemName=p.systemName)
					WHERE p.sysStatus='active'
					AND p.sysVersion='live'
					AND p.sysOpen='1'
					AND n.sysOpen='1'
					AND n.sysStatus='active'
					AND n.itemID='%d'",
						$parentID);
				$res = $db->query($qry);
						
				
				if ($db->valid($res)) {
					list($parentPageID) = $db->fetch_array($res);
				
					$tmp = $this->breadcrumb($parentPageID, 'array', false);
				
					foreach($tmp as $temp) {
						array_push($breadcrumb, $temp);
					}
				} else {
					// check for outbound link parents
					
					$qry = sprintf("SELECT url, label 
						FROM sysNav AS n 
						WHERE n.itemID='%d'
						AND n.sysStatus='active'
						AND n.sysOpen='1'",
							$parentID);
					$res = $db->query($qry);
					
					if ($db->valid($res)) {
						while ($tmp = $db->fetch_assoc($res)) {							
							array_push($breadcrumb, array('url'=>$tmp['url'], 'label'=>$tmp['label'], 'home'=>'0'));						
						}					
					}				
				}
				
				
				
				
			}
		
		}
		
		
		// grab the homepage
		if ($original === true) {
			if (array_search_recursive($breadcrumb, '1', 'home') === false) {
				
				$qry = sprintf("SELECT label
					FROM sysPage
					WHERE isHomepage = '1'
					AND sysOpen = '1' 
					AND sysStatus = 'active'
					AND sysVersion = 'live'
					AND sysOpen = '1'  
					AND systemName IN (SELECT appItemID FROM sysSitesInstanceDataLink WHERE sysOpen = '1' AND sysStatus = 'active' AND appID = 'page' AND instanceID = '%d')
					ORDER BY sysStatus DESC, sysDateCreated DESC;",
						$quipp->instanceID);
				$res = $db->query($qry);
				
				if ($db->valid($res)) {
					list($label) = $db->fetch_array($res);
					array_push($breadcrumb, array('url' => '/', 'label' => $label, 'home'=>'1'));
				}
			}
		
		}
		
		// return array of 
		if ($return == 'array') {
			return $breadcrumb;
		} else {
			
			$breadcrumb = array_reverse($breadcrumb);
			$t = count($breadcrumb) -1;
			$toReturn = '';
			for ($i = 0; $i <= $t; $i++) {
				
				$current = ($i == $t) ? ' class="current"' : '';
				$toReturn .= '<a href="' . $breadcrumb[$i]['url'] . '"' . $current . '>' . $breadcrumb[$i]['label'] . '</a>';
				if ($current == '') {
					$toReturn .= $separator;
				}
			
			}
			return $toReturn;
			
		}
		
	}





	function get_nav($slug, $self = false)
	{

		global $db;

		$qry = sprintf("SELECT parentID FROM sysNav 
			WHERE sysOpen='1' 
			AND sysStatus='active'
			AND pageSystemName='%s'",
				$db->escape($slug));
		$res = $db->query($qry);
		

		if($db->valid($res)) {
			$nav = $db->fetch_assoc($res);
			if($nav['parentID'] == '0') {
				return $this->get_nav_children($slug, $self);
			} else {
				
				// check to see if this nav has any children
				$qry = sprintf("SELECT itemID
					FROM sysNav
					WHERE parentID=(SELECT itemID FROM sysNav n WHERE n.sysStatus='active' AND n.sysOpen='1' AND n.pageSystemName='%s')
					AND sysOpen='1'
					AND sysStatus='active'",
						$db->escape($slug));

				$res = $db->query($qry);
				
				if ($db->valid($res)) {
					return $this->get_nav_children($slug, $self);
				} else {
					return $this->get_nav_siblings($slug, $self);
				}
				
			}
		}
		return false;
	}

	/**
	 * get's the page's children based on a pageID or a sytemName
	 */

	function get_nav_children($slug, $self = false)
	{

		global $db;

		$nav = array();
		if ($self == true) {
		
			$qry = sprintf("SELECT itemID, parentID, url, target, label, pageSystemName
			FROM sysNav
				WHERE sysOpen='1'
				AND pageSystemName='%s' 
				ORDER BY myOrder",
				$db->escape($slug),
				$db->escape($slug));
				
	
			$res = $db->query($qry);
	
			if($db->valid($res)) {
			
				while ($n = $db->fetch_assoc($res)) {
					$n = array(
						'url'   => $n['url'],
						'slug'   => $n['pageSystemName'],
						'label'  => $n['label'],
						'target' => $n['target']
					);
					array_push($nav, $n);
				}
			}
		}
		
		
		$qry = sprintf("SELECT itemID, parentID, url, target, label, pageSystemName
			FROM sysNav
			WHERE sysOpen='1'
			AND parentID=(SELECT n.itemID FROM sysNav n WHERE n.pageSystemName='%s')				
			ORDER BY myOrder",
				$db->escape($slug));
				
	
		$res = $db->query($qry);

		if($db->valid($res)) {
			
			while ($n = $db->fetch_assoc($res)) {
				$n = array(
					'url'   => $n['url'],
					'slug'   => $n['pageSystemName'],
					'label'  => $n['label'],
					'target' => $n['target']
				);
				array_push($nav, $n);
			}
			return $nav;
		}
		
		if (!empty($nav)) {
			return $nav;
		}
		return false;

	}
	
	/**
	 * get's the page's siblings based on a pageID or a sytemName
	 */

	function get_nav_siblings($slug, $parent = false)
	{

		global $db;
		
		$nav = array();
		if ($parent == true) {
		
			$qry = sprintf("SELECT itemID, parentID, url, target, label, pageSystemName
			FROM sysNav
				WHERE sysOpen='1'
				AND itemID=(SELECT n.parentID FROM sysNav n WHERE n.sysStatus='active' AND n.sysOpen='1' AND pageSystemName='%s')
				ORDER BY myOrder",
				$db->escape($slug));
				
	
			$res = $db->query($qry);
	
			if($db->valid($res)) {
			
				while ($n = $db->fetch_assoc($res)) {
					$n = array(
						'url'   => $n['url'],
						'slug'   => $n['pageSystemName'],
						'label'  => $n['label'],
						'target' => $n['target']
					);
					array_push($nav, $n);
				}
			}
		}
		
		
		$qry = sprintf("SELECT url, target, label, pageSystemName 
			FROM sysNav 
			WHERE sysOpen='1' 
			AND parentID=(SELECT n.parentID FROM sysNav n WHERE n.pageSystemName='%s') 
			AND bucketID=(SELECT n.bucketID FROM sysNav n WHERE n.pageSystemName='%s') 
			ORDER BY myOrder",
				$db->escape($slug),
				$db->escape($slug));
		$res = $db->query($qry);

		if($db->valid($res)) {
			
			while ($n = $db->fetch_assoc($res)) {
				$n = array(
					'url'   => $n['url'],
					'slug'   => $n['pageSystemName'],
					'label'  => $n['label'],
					'target' => $n['target']
				);
				array_push($nav, $n);
			}
			
		}
		if (!empty($nav)) {
			return $nav;
		}
		return false;
	}
	public function build_main_nav($navigation, $page = '', $dropdown = false){
		$toReturn = "<ul>";
		$numNavs = count($navigation);
		$spacers = 6 - (count($navigation));
		if ($spacers < 0){
			$numNavs = 6;
			$spacers = 0;
		}
		$liWidth = 110;
		$liWidthL = $liWidth - 13;
		$liWidthR = $liWidth;
		if ($spacers > 0){
			for ($i = 1; $i <= $spacers; $i++){
				if ($i % 2 > 0){
					$liWidthL += ($spacers < 3)?(floor($liWidth/2)):$liWidth;
				}
				else if ($i % 2 == 0){
					$liWidthR += ($spacers < 3)?(floor($liWidth/2)):$liWidth;
				}
			}
		}
		$w = 0;
		for ($i = 0; $i < $numNavs; $i++) {
			$nav = $navigation[$i];
			$style = 'width:';
			$style .= ($w >= 304)?$liWidthR.'px;text-align:center':$liWidthL.'px';
			
			if ($w == 304){
				$toReturn .= '<li style="width:260px">&nbsp;</li>'; //logo spacer
			}
			
			if (isset($nav['class'])) {
				$selected = ($page == $nav['slug']) ? ' class="current ' . $nav['class'] . '"' : ' class="' . $nav['class'] . '"';
			} else {
				$selected = ($page == $nav['slug']) ? ' class="current"' : '';
			}
			
			
			$url   = (!empty($nav['url'])) ? $nav['url'] : '/' . $nav['slug'];

			$nav['target'] = (!empty($nav['target'])) ? 'target="' . $nav['target'] . '" ' : '';

			$toReturn .= '<li' . $selected . ' style='.$style.'>';
			$selected  = '';

			$toReturn .= '<a ' . $nav['target'] . 'href="' . $url . '"' . $selected. '>' . $nav['label'] . '</a>';

			if(isset($nav['children']) && $dropdown == true) {
				$toReturn .= $this->build_nav($nav['children'], $page);
			}
			$toReturn .= '</li>';
			
			$w += ($w >= 330)?$liWidthR:$liWidthL;
		}
		return $toReturn."</ul>";
	}

	//THE PRECEEDING METHODS/FUNCTIONS need to be updated to reflect the new table strucure

}


?>