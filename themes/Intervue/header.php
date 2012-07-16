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
?><!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title><?php echo $meta['title'] . $meta['title_append']; ?></title>
    <meta name="description" content="<?php echo $meta['description']; ?>">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="/min/?f=themes/<?php echo basename(__DIR__); ?>/default.css"> 
    <script src="/js/modernizr-2.5.3.min.js"></script>

    <link rel="stylesheet" href="/themes/Intervue/style.css">

    <script src="/themes/Intervue/js/vendor/modernizr-2.5.3.min.js"></script>
    <script type="text/javascript" src="http://use.typekit.com/gux8ztq.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
</head>
<body>
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
                <ul>
                    <li><a href="#">How It Works</a>
                    <li><a href="#">FAQ</a>
                    <li><a href="#">Contact Us</a>
                </ul>
            </nav>
            <div id="banner">
                <ul>
                    <li>
                        <span>Walk through the job<br />registering process</span>
                    </li>
                    <li class="middle">
                        <a class="arrows prev" href="#">Previous</a>
                        <h2>Why Use<br /><strong>Intervue?</strong></h2>    
                        <a href="#">Watch the video! <span class="icon video">Video</span></a>
                        <a class="arrows next" href="#">Next</a>
                    </li>
                    <li>
                        <span>Walk through the<br />application process</span>
                    </li>
                </ul>
            </div>
        <div class="clearfix"></div>
        </div>
    </header>
    
    <section id="container" class="home">
</head>
<body data-controller="<?php echo str_replace('-', '_', $meta['body_id']); ?>" class="<?php print Page::body_class($meta['body_classes']); ?>" id="<?php echo $meta['body_id']; ?>">
