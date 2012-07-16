<div class="blogLine"><h5>Recent Posts:</h5> </div>
<?php

if (isset($db) && $db INSTANCEOF DB_MySQL && $this INSTANCEOF Quipp){

    $blogUrl = array("1" => "blog", "2" => "ccok-blog");
    
	if (!isset($blog)){
	   require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/apps/Blog/Blog.php";
	   
        if (!isset($blogStatus) || $blogStatus != "private"){
            $blogStatus = "active";
        }
	   
	   $blog = new Blog($db, $this->siteID, $blogStatus);
	}
	
	$page = (isset($_GET["page"]) && (int)$_GET["page"] > 0) ? (int)$_GET["page"] : 1;    

    $recent = $blog->getPostList((($page*5)-5), 5);
    
    $pages = 0;
    $slug = '';
    if (isset($_GET['slug'])) {
        $slug = '/'.$_GET['slug'];
    }
    
    if (isset($recent) && $recent !== false){
?>
    <div class="archive-widget" id="blog-archive-recent">
     
    <ul>
<?php
        foreach ($recent as $post){
            
            $pages = $post["count"];
            
            $leadText = strip_tags($post["lead_in"]);
            $leadIn = (strlen($leadText) > 95)? substr($leadText, 0, strpos($leadText, " ", 75))."&hellip;" : $leadText;
        
            echo '<li>
            <a href="/'.$blogUrl[$this->siteID].'/'.trim($post['slug']).'">'.trim($post["title"]).'</a><br /><small>Posted On: '.date("M d Y",$post["displayDate"]).'</small><br />
            '.$leadIn.' <a href="/'.$blogUrl[$this->siteID].'/'.trim($post['slug']).'" class="readMore">Read More</a></li>';
        }
?>
    </ul>
<?php
        if ($pages > 1){
            echo pagination($pages, 5, $page, '/'.$blogUrl[$this->siteID].$slug.'?page=', false);
        }
?>
    </div>

<?php
    }
}