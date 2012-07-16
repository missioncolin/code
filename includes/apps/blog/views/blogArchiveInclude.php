<?php
if (isset($blog) && $blog INSTANCEOF Blog && isset($slug) && strstr($slug,"archive") !== false){

    $archive = str_replace("archive-","",$slug);
    //$posts = ((bool)strtotime($archive) === false)?$blog->getArchiveByCategory($archive):$blog->getArchiveByDate(date("U"));
    $posts = $blog->getArchiveByDate(date("U"));
    
    if ($posts !== false){
?>
        <div id="news-article-<?php echo $archive; ?>" class="news-article">
        <h2>Posts: <?php echo ucwords($archive); ?></h2>
<?php
            foreach ($posts as $post){
                echo '<h5><a href="/blog/'.trim($post["slug"]).'">'.trim($post["title"]).'</a></h5>';
                echo '<p class="author">Posted By: '.trim($post["author"]).' on '.date("F d, Y",trim($post["displayDate"])).'</p>';
                echo '<p class="lead-in">'.trim($post["lead_in"]).'</p>';
            }
     
?>   
        </div>
<?php  
    

    }
    
}