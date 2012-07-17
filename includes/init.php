<?php

session_start();

ini_set('display_errors', 'on');
error_reporting(E_ALL);

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
	"ga_password" => "32webuser32",
	"ga_profile_id" => "17069938" //mikealmond.com
);  //top content




$auth  = new Auth($db,$quipp);
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