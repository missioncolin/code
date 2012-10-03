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

    <title><?php echo $meta['title'] . $meta['title_append']; ?></title>
    <meta name="description" content="<?php echo $meta['description']; ?>">
    <meta name="viewport" content="width=device-width">
    
    <link rel="stylesheet" href="/themes/Intervue/style.css">
    <link rel="stylesheet" href="/css/plugins/jquery-ui-1.8.6.css">


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
            
                <?php if (isset($user->groups['hr-managers'])) { ?>
                    <div id="loggedInButtons">
                        <a href="/buy-job-credits"><?php echo $user->info['Job Credits']; ?> Credits</a> //
                        <a href="/profile">Profile</a>
                    </div>
                <?php } else { ?>
                    <ul id="social">
                        <li><a class="icon linkedIn" href="#">LinkedIn</a></li>
                        <li><a class="icon facebook" href="#">Facebook</a></li>
                        <li><a class="icon twitter" href="#">Twitter</a></li>
                    </ul>
                <?php } ?>
                
                <?php if (isset($user->groups['hr-managers']) || isset($user->groups['applicants'])) { ?>
                    <a class="btn" href="/logout">Logout</a>
                <?php } else { ?>
                    <a class="btn" href="/login">Login</a>
                <?php } ?>
            </div>
            <nav>
                <?php 
                if (isset($user->groups['hr-managers'])) {
                	print $nav->build_nav($nav->get_nav_items_under_bucket('managerHeader', 0));
                } else {
                    print $nav->build_nav($nav->get_nav_items_under_bucket('primary'), true, true);
                }
                ?>
            </nav>
            <?php  if ($meta['body_id'] == 'home') { include 'includes/apps/banners/views/banners.php'; } ?>
        <div class="clearfix"></div>
        </div>
    </header>
    
    <section id="container" <?php if ($meta['body_id'] == 'home') { print 'class="home"'; } ?>>
    
        <ul id="steps">

        </ul>
