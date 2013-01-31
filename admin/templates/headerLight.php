<!doctype html>
<html lang="en" class="no-js">
<head>
  <meta charset="utf-8">
  <!--[if IE]><![endif]-->

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title><?php print $meta['title'] . $meta['title_append']; ?></title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="shortcut icon" type="image/png" href="/admin/favicon.png">
  <link rel="apple-touch-icon" href="/admin/apple-touch-icon.png">
  <link rel="stylesheet" href="/min/?f=css/style.css,css/admin.css,css/plugins/jquery-ui-1.8.6.css">
 
   <?php 
	//print out any scripts that are needed for the page calling in this header file, 
	//this is set in that particular file using array_push($quipp->js['header'],"/path/to/script.js", "/path/to/another/script.js");
	
	if(isset($quipp->css)) {
		if(is_array($quipp->css)) {
			foreach($quipp->css as $val) {
				if($val != '') {
					print '<link rel="stylesheet" href="' . $val . '">'; 
				}
			}
		}
	}
	?>
  <script src="/js/modernizr-1.6.min.js"></script>
  <?php 
	//print out any scripts that are needed for the page calling in this header file, 
	//this is set in that particular file using array_push($quipp->js['header'],"/path/to/script.js", "/path/to/another/script.js");
	
	if(isset($quipp->js['header'])) {
		if(is_array($quipp->js['header'])) {
			foreach($quipp->js['header'] as $val) {
				if($val != '') {
					print '<script type="text/javascript" src="' . $val . '"></script>'; 
				}
			}
		}
	}
  ?>
</head>
<body class="light <?php print Page::body_class($meta['body_classes']); ?>" id="<?php if (!empty($meta['body_id'])) print $meta['body_id']; ?>">
