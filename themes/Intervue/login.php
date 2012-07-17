<?php

    $qcore = Quipp();

    if (isset($_POST['username'], $_POST['password'])) {
        $qcore->auth()->login($_POST['username'], $_POST['password']);
    }
    
    ?>
    
    <?php
    if (!isset($meta)) {
        $meta = array('title' => '', 'title_append' => '', 'description' => '', 'body_id' => '', 'body_classes' => array());
    }
?><!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <title><?php print $meta['title']; print $meta['title_append']; ?></title>
    <meta name="description" content="<?php print $meta['description']; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link type="text/plain" rel="author" href="/humans.txt" />
    
    <link rel="stylesheet" href="/min/?f=themes/Intervue/default.css"> 
    <script src="/js/modernizr.custom.js"></script>
</head>
<body data-controller="<?php echo str_replace('-', '_', $meta['body_id']); ?>" class="<?php print Page::body_class($meta['body_classes']); ?> dark" id="<?php if (!empty($meta['body_id'])) print $meta['body_id']; ?>">

<div id="container" class="login">
    
    
    <?php

    if (isset($_GET['t'])) {
        print $qcore->auth()->fail_type($_GET['t']);
    }

    $username = (isset($_POST['username'])) ? $_POST['username'] : '';

    $directoryTag = "";
    if ($qcore->auth()->type == "ad") {
        $directoryTag = "<span style=\"color:#CCCCCC; font-style:italic; font-size:10px;\"> (Active Directory - " . $qcore->auth()->ad->domain_controllers[0] . ")</span>";
    }

    $showQuippBrand = " class=\"quippBranding\"";
    ?>  
        <img src="/themes/Intervue/img/logo.png" alt="" />
        <div id="loginBox" <?php print $showQuippBrand; ?>>
 
            <form action="<?php print $_SERVER['REQUEST_URI']; ?>?login<?php print $qs; ?>" id="loginBoxForm" method="post">
            	<h2>Sign-In</h2>
                <input type="hidden" name="nonce" value="<?php echo $qcore->config('security.nonce'); ?>" />
                
                    <div class="inputs">
                        <label for="username">Username</label>
                        <input type="text" class="loginText" id="username" name="username" autofocus="autofocus" value="<?php print $username; ?>" />
                    </div>
                    <div class="inputs">
                        <label for="password">Password</label>
                        <input type="password" class="loginText" id="password" name="password" value="" />
                    </div>
                    
                    <div>
                        <div>
                            <input type="checkbox" id="keepSignedIn">
                            <label for="keepSignedIn">Keep me signed in</label>
                        </div>
                        
                        <div>
                            <input type="submit"  value="Sign-in" class="btnStyle" />
                            <a href="/forgot-password">Password Recovery</a>
                        </div>
                    </div>
            </form>
        </div>


    <div class="clearfix"></div>
    </div> <!--! end of #container -->

  <script>
    var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview'],['_trackPageLoadTime']];
    (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
    g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
    s.parentNode.insertBefore(g,s)}(document,'script'));
  </script>
  <!--[if lt IE 7 ]>
    <script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
    <script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
  <![endif]-->  
</body>
</html>
