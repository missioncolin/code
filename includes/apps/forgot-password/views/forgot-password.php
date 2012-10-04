<?php
    
    if(!class_exists('Quipp')) {
        // trollolololol
        require_once dirname(dirname(dirname(__DIR__))) . '/init.php';
	}
	
	global $quipp;
	
	require_once dirname(__DIR__) . '/ForgotPassword.php';
    $fp = new ForgotPassword($db);

	if (!empty($_POST)) {
       $response = $fp->sendReset($_POST['email']);
       
       if ($response === true) {
           header('Location: /');
       } else {
           $quipp->js['onload'] .= 'alertBox("fail", "' . $response . '");';
       }
	}
?>
<h2>Reset your password</h2>
<p>In order to reset your password, please supply us with your email address that you used to sign up</p>
<form action="" method="post">
    <div>
        <label for="email">Your email address</label> <input type="email" name="email" id="email" placeholder="johndoe@example.com" required />
    </div>
    <div>
        <input class="btn" type="submit" value="Submit" />
        
    </div>
</form>
