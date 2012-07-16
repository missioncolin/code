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
	
	$site = array("1" => "", "2" => "CCOK");
	
	if (isset($_POST['username'], $_POST['password'])) {
		$auth->login($_POST['username'], $_POST['password']);	
	}

	require 'themes/Intervue/header'.$site[$quipp->siteID].'.php';
	
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
		<div id="loginBox" <?php print $showQuippBrand; ?>>
			<form action="?login<?php print $qs; ?>" id="loginBoxForm" method="post">
				
						<div id="loginBoxUsername">
							<label for="username">User</label>
							<input type="text" class="loginText" style="width:160px;" id="username" name="username" value="<?php print $username; ?>" />
						</div>
						<div id="loginBoxPassword">
							<label for="password">Password</label>
							<input type="password" class="loginText" style="width:160px;" id="password" name="password" value="" />
						</div>
						<div id="loginBoxButtons">
							
							<input type="button" value="Cancel" class="cancel" onclick="javascript:history.go(-1);" />
							<input type="submit"  value="Login &raquo;" />
						</div>
				<div class="clearBox">&nbsp;</div>
			</form>
		</div>
	<?php
	
	require 'themes/Intervue/footer'.$site[$quipp->siteID].'.php';
	
}

?>