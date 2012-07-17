<?php
    if (!isset($meta)) {
        $meta = array();
    }

    $meta += array(
        'title'        => ''
      , 'title_append' => ''
      , 'description'  => ''
      , 'body_id'      => ''
      , 'body_classes' => array()
    );
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title><?php echo $meta['title'] . $meta['title_append']; ?></title>
    <meta name="description" content="<?php echo $meta['description']; ?>">
    <meta name="viewport" content="width=device-width">
    
    <link rel="stylesheet" href="/themes/Intervue/style.css">

    <script src="/themes/Intervue/js/vendor/modernizr-2.5.3.min.js"></script>
    <script type="text/javascript" src="http://use.typekit.com/gux8ztq.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
</head>
<body data-controller="<?php echo str_replace('-', '_', $meta['body_id']); ?>" class="<?php print Page::body_class($meta['body_classes']); ?>" id="<?php echo $meta['body_id']; ?>">
    <!--[if lt IE 7]><p class="chromeframe">Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->

    <header>
        <div>
            <a class="logo" href="/"><img src="/themes/Intervue/img/logo.png" alt="Intervue" /></a>
            <div id="tools">
                <ul id="social">
                    <li><a class="icon linkedIn" href="#">LinkedIn</a></li>
                    <li><a class="icon facebook" href="#">Facebook</a></li>
                    <li><a class="icon twitter" href="#">Twitter</a></li>
                </ul>
                <a class="btn" href="#">Login</a>
            </div>
            <nav>
                <?php print $nav->build_nav($nav->get_nav_items_under_bucket('primary'), true, true); ?>
            </nav>
            <?php  if ($meta['body_id'] == 'home') { include 'includes/apps/banners/views/banners.php'; } ?>
        <div class="clearfix"></div>
        </div>
    </header>
    
    <section id="container" class="home">
