<?php

require 'includes/init.php';

$page  = new Page($_GET['p']);
if ($page->template != '') {

    if (isset($_SESSION['isMasquerading'])) {
        $quipp->js['onload'] .= "$('body').prepend('<div style=\"text-align:center;background:#FFF800;margin:0;padding:8px;\"><h1>You are currently masquerading as another user. You must <a href=\"/logout\">logout</a> to reclaim your account</h1></div>');";
    }

	$meta['title'] 		  = $page->info['label'];
	$meta['title_append'] = ' &bull; ' . $quipp->siteLanguageRS[$_SESSION['instanceID']]['siteTitle'];
	$meta['body_id'] 	  = $page->info['systemName'];
	$meta['description']  = ($page->info['pageDescription'] != '') ? $page->info['pageDescription'] : $quipp->siteLanguageRS[$_SESSION['instanceID']]['description'];
	
	// build the breadcrumbs
	$breadcrumb = $nav->breadcrumb($page->info['itemID'], 'link', true, ' &gt; ');

	// pull in the template file
	ob_start();
	
	require_once $page->template;
	
	ob_end_flush();
} else {

	$page->display_404();
}

$db->close();

?>