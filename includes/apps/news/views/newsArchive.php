<?php

    if (!isset($news)) {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/apps/news/News.php";
        $news = new News();
    }
    
    $itemsPerPage = 4;
    $page         = 1;
    $offset       = 0;
    
    $slug = 'latest';
    if (isset($_GET['slug'])) {
        $slug = $_GET['slug'];
    } 
    
    
    if (isset($_GET['page']) && (int) $_GET['page'] > 0 ) {
        $page   = (int) $_GET['page'];
        $offset = ($page - 1) * $itemsPerPage;
    }
       
       
        
    if (isset($_GET['show']) && $_GET['show'] == 'all') {
        $newsList = $news->article_list($offset, 'all');
    } else {
        $newsList = $news->article_list($offset, $itemsPerPage);

    }
    
    
    if (is_array($newsList)) {
        $news->print_article_list($newsList, true, false, false, 'newsreel', 'news-archive');
        
		echo pagination(ceil($news->count_articles() / $itemsPerPage), $page, "/newsreel/" . $slug . "&page=", 1 );

    } else {
    ?>
        <p>There are no articles currently present.</p>
    <?php
    }
?>
