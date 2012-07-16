<?php
	//this is the search results interface, currently it's one function call but will prob be broken up into a data and display thing	
	if(!isset($search)) {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/apps/search/Search.php";
		$search = new Search();
	}
	
	if(!isset($_REQUEST['q'])) {
		$_REQUEST['q'] = null;
	}
	
	print $search->run_content_search($_REQUEST['q']);
	
?>