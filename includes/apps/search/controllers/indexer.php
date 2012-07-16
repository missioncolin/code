<?php
	//this is the search results interface, currently it's one function call but will prob be broken up into a data and display thing	
	require '../../../../includes/init.php';
	
	if(!isset($search)) {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/apps/search/Search.php";
		$search = new Search();
	}
	
	if(!isset($_REQUEST['q'])) {
		$_REQUEST['q'] = null;
	}
	
	$search->build_index();
	
	//news index
	$getNews = "SELECT * FROM tblNews WHERE sysStatus = 'active' AND sysOpen = '1'"; 
	$result = $db->query($getNews);
	
	if($db->valid($result)) {
		$clearOutOldIndexItems = "DELETE FROM sysSearchIndex WHERE indexSource = 'news';";
		$db->query($clearOutOldIndexItems);
		while($indexRS = $db->fetch_array($result)) {
			 if(!empty($indexRS['body'])) {
					//print "<li><strong>$pageSystemName</strong> : $pageContent </li>";
					$insertNewIndexItem = "
						INSERT INTO sysSearchIndex (indexTime, indexSource, pathToContent, contentTitle, contentDumpToScan) 
						VALUES
						(NOW(), 'news','/newsreel/" .$indexRS['slug'] . "','" . addslashes($indexRS['title']) . "','" . addslashes(strip_tags($indexRS['body'])) . "');
					";
					
					$db->query($insertNewIndexItem);
			}
		}
	}

	//end of news index
						
						
	
	
	
?>