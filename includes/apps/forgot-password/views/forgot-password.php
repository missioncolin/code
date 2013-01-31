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
<section id="forgot-password">

    <h2>Reset your password</h2>
    <form action="" method="post">
        <fieldset>
            <legend>In order to reset your password, please supply us with your email address that you used to sign up</legend>
            <label for="email">Your Email Address</label><input type="email" class="full bottom" name="email" id="email" placeholder="Your Email Address" required />
        </fieldset>
        <input class="btn" type="submit" value="Submit" />
    </form>

</section>