<div id="newsSideNav">
<?php
global $quipp;

$includeParent = true;
$nav = new Nav();

$n = $nav->get_nav('news', $includeParent);
$slug = $_GET['p'];

if (isset($n) && is_array($n)) {
	if ($includeParent == true) {
		$parent = array_shift($n);
		
		$selected = ($slug == $parent['slug']) ? ' class="current"' : '';
		$url   	  = (!empty($parent['url'])) ? $parent['url'] : '/' . $parent['slug'];
		$parent['target'] = (!empty($parent['target'])) ? 'target="' . $parent['target'] . '" ' : '';

		
		print '<h3><a href="' . $url . '"' . $parent['target'] . '>' . $parent['label'] . '</a></h3>';
	}
  
  
	$qry = sprintf("SELECT YEAR(sysDateCreated) AS yearCreated, MONTHNAME(sysDateCreated) AS monthCreated 
		FROM tblNews 
		WHERE DATEDIFF(CURDATE(), sysDateCreated) <= 365 
		GROUP BY CONCAT(yearCreated, '-', monthCreated) 
		ORDER BY CONCAT(yearCreated, '-', monthCreated) ASC;");
	$res = $db->query($qry);
	
	$newsArchive = array();
	if ($db->valid($res)) {
		while ($w = $db->fetch_array($res)) {
    
			array_push($newsArchive, array(
				'url'    => '/news?y=' . $w['yearCreated'] . '&amp;m=' . $w['monthCreated'],
				'slug'   => '',
				'label'  => $w['monthCreated'] . ' ' . $w['yearCreated'],
				'target' => '',
			));
		
		}
	}
	$n1 = array(array(
		'url' => '#archive',
		'slug' => '',
		'label' => 'Archive',
		'target' => '',
		'class' => 'flyout',
		'children' => $newsArchive
	));
	
	
	$n = array_merge($n, $n1);

	print $nav->build_nav($n, $slug);

}

$quipp->js['onload'] .= "\$('#newsSideNav li.flyout').hover(function() {\$('#newsSideNav ul ul').show(); }, function() {\$('#newsSideNav ul ul').hide();});";

?>
</div>