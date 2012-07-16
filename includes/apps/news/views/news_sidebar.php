<?php 	

	if(!isset($news)) {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/apps/news/News.php";
		$news = new News();
	}
	
	if(isset($_REQUEST['slug'])){
		$whichArticle = $_REQUEST['slug'];
	}else{
		$whichArticle = 'latest';
	}
	
		
	//================ FULL STORY VIEW ====================//	
	/*
echo "<div id='newsViewArticle'>";
	foreach($news->full_story($whichArticle) as $article){
	
		$author = $db->return_specific_item($article['author'], "tblWineries", "wineryName");
		echo "<span class='newsViewTitle'>" . $article['title'] . "</span>";
		echo "<span class='newsViewAuthor'>" . $author . "</span>";
		echo "<span class='newsViewDate'>" . date('l, F j, Y', strtotime($article['sysDateCreated'])) . "</span>";
		echo "<span class='newsViewBody'>" . $article['body'] . "</span>";
	}
	echo "</div>";
*/
	//================  ====================//
	
	
	
	
	
	
	
	//================ SIDEBAR LIST VIEW ====================//
	
	
	
	if(isset($_REQUEST['show']) && $_REQUEST['show'] == 'all'){
		$itemsPerPage = 'all';
		$dividePagesBy = $news->count_articles();
	}else{
		$itemsPerPage = 3;
		$dividePagesBy = $itemsPerPage;
	}
	
	if(isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ){
		$page = $_REQUEST['page'];
		$offset = ($page - 1) * $itemsPerPage;
	}else{
		$page = 1;
		$offset = 0;
	}
	
	$newsList = $news->article_list($offset, $itemsPerPage);
	
	echo "<div id='newsViewListWrapper'>";
	
	echo "<div id='newsWidget'><h2>News</h2>";
	if($newsList){
		$news->print_article_list($newsList, false, true);
	}
	echo "</div>";
	
	//pagination
	
		/*
echo "<div id='newsViewPagination'>";
			echo pagination(ceil($news->count_articles() / $dividePagesBy), $page, "/news/" . $whichArticle . "&page=", 1 );
			//echo pagination(5, 1, '/?page=', 1 );
		echo "</div>";
*/
	
	//================  ====================//
	
	echo "</div>";
?>