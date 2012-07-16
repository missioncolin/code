<div id="subNav">
<?php


$includeParent = false;
$nav = new Nav();

global $sideNavOverride;
if (isset($sideNavOverride) && $sideNavOverride != '') {
	
	$n = $nav->get_nav($sideNavOverride, $includeParent);
	$slug = $sideNavOverride;

} else if (isset($page->info['systemName'])) {
	
	$n = $nav->get_nav($page->info['systemName'], $includeParent);
	$slug = $page->info['systemName'];

} else if (isset($_GET['p'])) {
	
	$n = $nav->get_nav($_GET['p'], $includeParent);
	$slug = $_GET['p'];
} 


if (isset($n) && is_array($n)) {
	if ($includeParent == true) {
		$parent = array_shift($n);
		
		$selected = ($slug == $parent['slug']) ? ' class="current"' : '';
		$url   	  = (!empty($parent['url'])) ? $parent['url'] : '/' . $parent['slug'];
		$parent['target'] = (!empty($parent['target'])) ? 'target="' . $parent['target'] . '" ' : '';

		
		print '<h3><a href="' . $url . '"' . $parent['target'] . '>' . $parent['label'] . '</a></h3>';
	}

	print $nav->build_nav($n, $slug);

}


?>
</div>