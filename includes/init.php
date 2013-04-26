<?php

session_start();

ini_set('display_errors', 'on');
error_reporting(E_ALL);

//force https on the buy-job-credits page at the php level to ensure server configuration changes don't accidentally 
//reveal a non-secured page

if(strstr($_SERVER['REQUEST_URI'], "/buy-job-credits") && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")) {
	header("Location:https://" . $_SERVER['SERVER_NAME'] . "/buy-job-credits");
} elseif(!strstr($_SERVER['REQUEST_URI'], "/buy-job-credits") && $_SERVER['SERVER_PORT'] != "80") {
	header("Location:http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
}

setlocale(LC_MONETARY, 'en_CA');
header('X-Developer: Resolution Interactive Media Inc.');


$_SESSION['settings']['docroot'] = $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);

// Class auto load function
function __autoload($class) { 
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/quipp/' . $class . '.php')) {
    	require_once 'quipp/' . $class . '.php';
    }
}
require 'includes.php'; 
require 'quipp/common.php'; 
require 'apps/Auth/customAuth.php'; //just for Intervue
require dirname(__DIR__) . '/vendor/Stripe/lib/Stripe.php';
Stripe::setApiKey('sk_AohcDYj7BQy2HBUB0eKj3C7l28oWo');


$config = require __DIR__ . '/config.php.dist';
$dbc    = $config['db'];
$class  = $dbc['class'];

$db = new $class($dbc['host'], $dbc['user'], $dbc['pass'], $dbc['name']);
$db->query('SET NAMES utf8;');

$quipp = new Quipp();
$quipp->js = array(
	'header' => array(),
	'footer' => array(),
	'onload' => ''
);
$quipp->css = array();

$quipp->google = array(
	"ga_email" => "resimanalytics@gmail.com",
	"ga_password" => "2349swordFISH28",
	"ga_profile_id" => "71788152" //intervue.ca
);  //top content




//$auth  = new Auth($db,$quipp);
$auth  = new customAuth($db,$quipp);
$nav   = new Nav();

if (isset($_SESSION['userID'])) {
	$user  = new User($db,$_SESSION['userID']);
} else {
	$user  = new User($db);
}

$auth->check_auth();


$feedback = new Feedback();


/********************* ^^ Database Configuration Values ^^ *********************/

/********************* vv Authentication/Directory Configuration Values vv *********************/
/*$DRAGGIN['auth']['authtype'] = "local";   //options: activedirectory or local
$DRAGGIN['auth']['directory']['account_suffix'] = "";
$DRAGGIN['auth']['directory']['base_dn'] = "";
$DRAGGIN['auth']['directory']['domain_controllers'] = array("");
$DRAGGIN['auth']['directory']['ad_username'] = "";
$DRAGGIN['auth']['directory']['ad_password'] = "";
*/

$meta = array(
	'title' 	   => 'Intervue',
	'title_append' => ' &bull; Home',
	'description'  => 'Intervue',
	'keywords'	   => '',
	'lang'		   => 'en',
	'robots'	   => 'noindex,nofollow',
	'author'	   => '',
	'body_id'	   => '',
	'body_classes' => array(),
	'analytics'    => '',
	'top_bar'   => '',
	'is_home'   => false	   
); 
$jsFooter = '';




if (!isset($_GET['p'])) { 
	$_GET['p'] = false;
}


$MIME_TYPES = array(
	'image/jpeg'   => 'jpg',
	'image/pjpeg'  => 'jpg',
	'image/gif'    => 'gif',
	'image/tiff'   => 'tif',
	'image/x-tiff' => 'tif',
	'image/png'    => 'png',
	'image/x-png'  => 'png',
	'application/x-shockwave-flash' => 'swf'
);


$qs = '';
			
array_walk($_GET, 'clean_query_string');
$qs = substr($qs, 0, -1);

if ($qs != '') {
	$qs = '&' . $qs;
}





?>
