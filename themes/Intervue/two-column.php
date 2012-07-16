<?php

    array_push($meta['body_classes'], 'two-column');
    require 'header.php';

?>
<section class="main">
	<div class="content">
    	<div id="colA"><?php echo $page->get_col_content('twoColA'); ?></div>
    	<div id="colB"><?php echo $page->get_col_content('twoColB'); ?></div>
    </div>
</section>
<?php 
    require 'footer.php';