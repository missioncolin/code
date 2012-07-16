<?php

    array_push($meta['body_classes'], 'one-column');
    require 'header.php';

?>
<section class="main">
	<div class="content">
        <div id="colA"><?php echo $page->get_col_content('fullColA'); ?></div>
    </div>
</section>
<?php

    require 'footer.php';