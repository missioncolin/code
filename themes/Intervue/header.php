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

    <link rel="stylesheet" href="/themes/Intervue/scratch.css">
    
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
                        <a href="/buy-job-credits"><?php echo $user->info['Job Credits'];  echo ( $user->info['Job Credits'] == 1 ? ' Credit': ' Credits'); ?></a> 
                      <!--  <a href="/profile">Profile</a>-->
                    </div>
                    <ul id="social">
                        <li><a class="icon linkedIn" href="http://www.linkedin.com/company/2723868?trk=tyah" target="_blank">LinkedIn</a></li>
                        <li><a class="icon facebook" href="http://www.facebook.com/Intervue?fref=ts" target="_blank">Facebook</a></li>
                        <li><a class="icon twitter" href="https://twitter.com/Intervuetweets" target="_blank">Twitter</a></li>
                    </ul>
                <?php } else { ?>
                    <ul id="social">
                        <li><a class="icon linkedIn" href="http://www.linkedin.com/company/2723868?trk=tyah" target="_blank">LinkedIn</a></li>
                        <li><a class="icon facebook" href="http://www.facebook.com/Intervue?fref=ts" target="_blank">Facebook</a></li>
                        <li><a class="icon twitter" href="https://twitter.com/Intervuetweets" target="_blank">Twitter</a></li>
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
                    print $nav->build_nav($nav->get_nav_items_under_bucket('managerHeader', 0));
                }
                ?>
            </nav>
            <?php  if ($meta['body_id'] == 'home') { include 'includes/apps/banners/views/banners.php'; } ?>
        <div class="clearfix"></div>
        </div>
    </header>
    
    <section id="container" <?php if ($meta['body_id'] == 'home') { print 'class="home"'; } ?>>
    
    	<?php $signUpPages = array("hr-signup", "profile"); ?>
    	 
    	<!-- New breadcrumb setup: If creating a new user, and moving to create a job, display this: --->
<!--         <ul id="stepsNew"<?php if (!isset($_GET['step'])) { ?> class="hide"<?php } ?>> -->

		 <!---- Handles breadcrumb for newly registered users ---->
       	 <ul id="stepsNew"<?php if ($_GET['p'] != 'hr-signup' && !isset($_SESSION['newReg'])) { ?> class="hide"<?php } ?>>
            <?php if (isset($_GET['step']) || in_array($_GET['p'], $signUpPages)) { ?>
            <li<?php if ($_GET['p'] == 'hr-signup') { ?> class="current"<?php } ?>><span>1</span>Create Account</li>
            <li<?php if (isset($_GET['step']) && $_GET['step'] == '1') { ?> class="current"<?php } ?>><span>2</span>Name Your Job</li>
            <li<?php if (isset($_GET['step']) && $_GET['step'] == '2') { ?> class="current"<?php } ?>><span>3</span>Add Required Skills and Experience</li>
            <li<?php if (isset($_GET['step']) && $_GET['step'] == '3') { ?> class="current"<?php } ?>><span>4</span>Add intervue Questions</li>
            <li<?php if (isset($_GET['step']) && $_GET['step'] == '4') { ?> class="current"<?php } ?>><span>5</span>Activate Link</li>
            <?php } ?>
        </ul>
        
		<!--   If just creating a new job, use this: --->
        <ul id="steps"<?php if (isset($_SESSION['newReg'])) { ?> class="hide"<?php } ?>>
            <?php if (isset($_GET['step'])) { ?>
            <li<?php if ($_GET['step'] == '1') { ?> class="current"<?php } ?>><span>1</span>Name Your Job</li>
            <li<?php if ($_GET['step'] == '2') { ?> class="current"<?php } ?>><span>2</span>Add Required Skills and Experience</li>
            <li<?php if ($_GET['step'] == '3') { ?> class="current"<?php } ?>><span>3</span>Add intervue Questions</li>
            <li<?php if ($_GET['step'] == '4') { ?> class="current"<?php } ?>><span>4</span>Activate Link</li>
            <?php } ?>
        </ul>

       
        
       <!--
 <?php if(in_array($_GET['p'], $signUpPages)) { ?>
             <ul id="steps">
                <li<?php if ($_GET['p'] == 'hr-signup') { ?> class="current"<?php } ?>><span>1</span>Create a HR Account</li>
                <li<?php if ($_GET['p'] == 'profile') { ?> class="current"<?php } ?>><span>2</span>Your Profile</li>
                <li<?php if ($_GET['p'] == 'nope') { ?> class="current"<?php } ?>><span>3</span>Create Your First Job</li>
            </ul>
        <?php } ?>
-->
