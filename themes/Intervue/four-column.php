<?php

    array_push($meta['body_classes'], 'four-column');
    require 'header.php';

?>
<section id="colA">
    <div id="colAContent"><?php echo $page->get_col_content('fourColA'); ?></div>
</section>

<div id="container">
    <section class="main">
        <div id="colB"><?php echo $page->get_col_content('fourColB'); ?></div>
        <div id="colC"><?php echo $page->get_col_content('fourColC'); ?></div>
        <div id="colD"><?php echo $page->get_col_content('fourColD'); ?></div>
    </section>
</div>
<?php

    require 'footer.php';