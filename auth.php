<?php

if ($_GET['mode'] == 'logout') {
	header("Cache-control: private");
	if (!isset($_SESSION)){session_start();}
	session_destroy();
	unset($_SESSION);
	header("Location: /");
	exit('Logged Out...');
	
} else {
	
	require 'includes/init.php';
	
    if (isset($_POST['username'], $_POST['password'])) {
        $auth->login($_POST['username'], $_POST['password']);
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
    
    <link rel="stylesheet" href="/min/?f=themes/Intervue/style.css"> 
    <script src="/js/modernizr.custom.js"></script>
</head>
<body data-controller="<?php echo str_replace('-', '_', $meta['body_id']); ?>" class="<?php print Page::body_class($meta['body_classes']); ?> light" id="login">

<div id="container" class="login">
    
    
    <?php

    if (isset($_GET['t'])) {
        print $auth->fail_type($_GET['t']);
    }

    $username = (isset($_POST['username'])) ? $_POST['username'] : '';

    $directoryTag = "";
    if ($auth->type == "ad") {
        $directoryTag = "<span style=\"color:#CCCCCC; font-style:italic; font-size:10px;\"> (Active Directory - " . $auth->ad->domain_controllers[0] . ")</span>";
    }

    $showQuippBrand = " class=\"quippBranding\"";
    ?>  
        <img src="/themes/Intervue/img/logo.png" alt="" />
        <div id="loginBox" <?php print $showQuippBrand; ?>>
 
            <form action="<?php print $_SERVER['REQUEST_URI']; ?>?login<?php print $qs; ?>" id="loginBoxForm" method="post">
            	<h2>Sign-In</h2>
                
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
                            <input type="submit"  value="Sign-in" class="btn" />
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
<?php	
}

?>