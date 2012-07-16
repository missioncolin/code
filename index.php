<?php

require 'includes/init.php';

$page  = new Page($_GET['p']);
if ($page->template != '') {
	

	$meta['title'] 		  = $page->info['label'];
	$meta['title_append'] = ' &bull; ' . $quipp->siteLanguageRS[$_SESSION['instanceID']]['siteTitle'];
	$meta['body_id'] 	  = $page->info['systemName'];
	$meta['description']  = ($page->info['pageDescription'] != '') ? $page->info['pageDescription'] : $quipp->siteLanguageRS[$_SESSION['instanceID']]['description'];
	
	// build the breadcrumbs
	$breadcrumb = $nav->breadcrumb($page->info['itemID'], 'link', true, ' &gt; ');

	// pull in the template file
	require_once $page->template;
} else {

	$page->display_404();
}

$db->close();

?>