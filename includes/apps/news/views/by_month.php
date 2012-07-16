<?php 	

	if(!isset($news)) {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/apps/news/News.php";
		$news = new News();
	}
	if(isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ){
		$page = $_REQUEST['page'];
	}else{
		$page = 1;
	}
	
	
	
	
//=================================================================================================//
	
	$goBackHowManyMonths = 3; 
	
	for ( $i = 0; $i < $goBackHowManyMonths; $i ++) {
		
		$month = date("F", strtotime("now -" . $i . " months"));
		$displayMonthStr = date("F Y", strtotime($month));
		$newsList = $news->articles_in_month($month);
	
	
		echo "<div id='newsByMonthViewListWrapper'>";
		echo "<h2 class='newsMonthDivider'>" . $displayMonthStr . "</h2>";
		
		$news->print_article_list($newsList);
		echo "</div>";
	
	}
	
	
	print "NEWS ARTICLES BY MONTH!!";
?>