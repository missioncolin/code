<?php 	

	if(!isset($news) && $this INSTANCEOF Quipp) {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/apps/news/News.php";
		$news = new News($db,$this);
	}
?>

<div id="newsWidget">
    <?php
	$newsList = $news->article_list(0, 3);
	$news->print_article_list($newsList, true, true, false, 'newsreel', 'news-widget');
    ?>
</div>