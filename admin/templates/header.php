<?php
$applications = array(
	/*"banners" => array(
		"label" => "Banner Manager",
		"permissions" => "modifyBanner",
	),	*/
	"job-credits" => array(
		"label" => "Job Credit Pricing",
		"permissions" => "modifyPricing",
	),
	"transactions" => array(
		"label" => "Transactions",
		"permissions" => "viewTransactions",
	),
	"notification-manager" => array(
		"label" => "Notification Manager",
		"permissions" => "modifyNotifications",
	)
);


$userEditors = array(
	"users" => array(
		"label" => "Site Users",
		"view"	=> "all"
	),
	"groups" => array(
		"label"	=> "Site Groups",
	)
	
);


$modifyPages = $auth->has_permission('modifypages');
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
  <meta charset="utf-8">
  <!--[if IE]><![endif]-->

  <title><?php print $meta['title'] . $meta['title_append']; ?></title>
  <meta name="robots" content="noindex,nofollow">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link type="text/plain" rel="author" href="/humans.txt" />
  <link rel="shortcut icon" type="image/png" href="/admin/favicon.png">
  <link rel="apple-touch-icon" href="/admin/apple-touch-icon.png">
  

  <link rel="stylesheet" href="/js/uniform_js/css/uniform.aristo2.css">
  <!-- <link rel="stylesheet" href="/min/?f=css/reset.css"> -->
  <link rel="stylesheet" href="/css/reset.css">
  <link rel="stylesheet" href="/css/admin.css">
  <link rel="stylesheet" href="/css/plugins/jquery-ui-1.8.6.css">

  <link rel="stylesheet" href="/css/plugins/jquery.fancybox-1.3.4.css">
  <link rel="stylesheet" href="/admin/js/growl/jquery.gritter.css">
  <link rel="stylesheet" href="/css/plugins/jquery.jscrollpane.css">
 <?php 
	//print out any scripts that are needed for the page calling in this header file, 
	//this is set in that particular file using array_push($quipp->js['header'],"/path/to/script.js", "/path/to/another/script.js");
	
	if(isset($quipp->css)) {
		if(is_array($quipp->css)) {
			foreach($quipp->css as $val) {
				if ($val != '') {
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
				if ($val != '') {
					print '<script type="text/javascript" src="' . $val . '"></script>'; 
				}
			}
		}
	}

	$username = (isset($user->info['First Name'])) ? $user->info['First Name'] . ' ' . $user->info['Last Name'] : $user->username;
	?>
		
</head>
<body class="<?php print Page::body_class($meta['body_classes']); ?>" id="<?php if (!empty($meta['body_id'])) print $meta['body_id']; ?>">
  <div id="container">
    <header>
		<span class="user">Welcome Back, <?php print $username; ?></span>
		<span class="currentSection"><?php if(!isset($meta['title'])) { print "Dashboard"; } else { print $meta['title']; } ?></span>
		<span class="profileLogout"><a href="/admin/users.php?view=edit&amp;id=<?php print base_convert($user->id, 10, 36); ?>">Profile</a> / <a href="/logout">Logout</a></span>
    </header>

    
    <div id="structureControl">
    <img src="/images/admin/quippBanner.png" alt="Quipp Engine" />
		<span id="structureControlTitle">
		
		<a href="/admin/"><img src="/themes/Intervue/img/logo.png" alt="Intervue" width="188px" height="142px"/></a></span>
		
		<div id="navTab">
			<table>
				<tr>
				
					<?php
						if ($modifyPages){
					?>
					<td<?php 
						if (strpos($_SERVER['PHP_SELF'], '/content.php') > 0 || strpos($_SERVER['PHP_SELF'], 'admin/index.php') > 0) { 
							print ' class="current"'; 
						} ?>><a href="#pages" class="edit-pages">Pages</a>
						</td>
					<td <?php if (strpos($_SERVER['PHP_SELF'], '/apps/') !== false) { print ' class="current"'; } ?>><a href="#applications" class="edit-apps">Apps</a></td>
					<?php if($auth->has_permission("approvepages")) { ?>
						<td><a href="#stream" class="edit-stream">Stream</a></td>
					<?php } ?>
					<td class="last <?php if (strpos($_SERVER['PHP_SELF'], 'users.php') > 0 || strpos($_SERVER['PHP_SELF'], 'groups.php') > 0) { print ' current'; } ?>"><a href="#users" class="edit-users">Users</a></td>
					<?php
						}
					else{
						echo('<td><a href="#" class="edit-apps">Apps</a></td>');
					}
					?>
				</tr>
			</table>
		</div>
		
		<div id="structureList"<?php if(strpos($_SERVER['PHP_SELF'], '/admin/index.php')) { print ' style="display:block;"'; } elseif (strpos($_SERVER['PHP_SELF'], 'content.php') === false || !$modifyPages) { print ' style="display:none;"'; } ?>>
			
		</div>
		
		<div id="stream" style="display:none;">
			<?php
				if (!class_exists('ApprovalUtility') || !isset($approvalUtility)){
				    require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/classes/ApprovalUtility.php';
				    $approvalUtility = new ApprovalUtility();
				}
			?>
			
				<p style="margin-left:10px;">This is a feed of all approval requests submitted by users for your review before being approved for go-live.</p>
				<p>&nbsp;</p>
			
			<?php
			$approvalUtility -> get_tickets();
			
			?>
			<style>
			div.approvalTicket {
				display:block;
				padding:5px;
				background-color:white;
				border:1px solid grey;
			}
			
			</style>
		</div>
		
		<div id="applications" style="display:none;">
			<ul class="sidebarLargeList">
			<?php
				foreach($applications as $appName => $appInfo){
				
				    if ($auth->has_permission($appInfo["permissions"])){
    					echo("<li><a ");
    					if (strpos($_SERVER['PHP_SELF'], $appName) !== false) { echo ' class="selected" '; }
    					
    					if (strpos($appName, 'http') === 0) {
    					   echo "href=\"" . $appName . "/\" target=\"_blank\">".$appInfo["label"]."</a></li>";
    					   
    					} else {
    					   echo "href=\"/admin/apps/".$appName."\">".$appInfo["label"]."</a></li>";
    					
    					}
					}
				}
			?>
   	    	</ul>
		</div>
		<div id="users" style="display:none;">
			<ul class="sidebarLargeList">
			<?php
				
				foreach($userEditors as $appName => $appInfo){
					echo("<li><a ");
					if (strpos($_SERVER['PHP_SELF'], $appName) !== false) { echo ' class="selected" '; }
					echo("href=\"/admin/".$appName.".php/\">".$appInfo["label"]."</a></li>");
				}
			?>
   	    	</ul>
		</div>
	</div>
	
	
	
    
	<div id="inhalt">