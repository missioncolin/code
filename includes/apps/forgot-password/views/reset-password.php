<?php
    
    if(!class_exists('Quipp')) {
        require_once dirname(dirname(dirname(__DIR__))) . '/init.php';
    }
    
    global $quipp;

	require_once dirname(__DIR__) . '/ForgotPassword.php';
    $fp = new ForgotPassword($db);
?>
<section id="forgot-password">

<?php

    $showForm = false;
    if (isset($_GET['token'])) {
        $showForm = true;
        try {
            $user = $fp->verifyToken($_GET['token'], 'fpHash');
            if (isset($_POST['password'], $_POST['conf_password'])) {
    
                if ($_POST['password'] != $_POST['conf_password']) {
                    $quipp->js['onload'] .= 'alertBox("fail", "Password mismatch");';

                } else {
    
    
                    try {
                        $result = $user->changePassword($_POST['password']);
                    } catch (Exception $e) {
                        $quipp->js['onload'] .= 'alertBox("fail", "' . $e->getMessage() . '");';

                        $showForm = false;
                    }
                    
                    if ($result) {
                        $user->removeHash();
                        $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/login';

                        $quipp->js['onload'] .= 'alertBox("success", "Your password was changed. Please <a href=\"' . $url . '\">proceed to the login</a>");';


                    } else {
                        $quipp->js['onload'] .= 'alertBox("fail", "Unable to change account password");';
    
                    }
                    
                    $showForm = false;
                }
    
            }
    
        } catch (Exception $e) {
            $quipp->js['onload'] .= 'alertBox("fail", "' . $e->getMessage() . '");';
            $showForm = false;
        }
    
    } else {
        echo 'No token provided';
    }
?>

<?php
if ($showForm == true) { 
?>
<h2>Reset your password</h2>

<form action="?token=<?php echo $_GET['token']; ?>" method="post">
    <fieldset>
        <legend>Enter a New Password</legend>
        <label for="email"><?php echo 'Your email' ?></label><input type="email" class="full" name="email" id="email" value="<?php echo $user->info['Email']; ?>" /><br>
        <label for="password"><?php echo 'New password' ?></label><input type="password" class="half bottom left" name="password" id="password" placeholder="<?php echo 'New password' ?>" required /><br>
        <label for="conf_password"><?php echo 'Confirm password' ?></label> <input type="password" class="half bottom" name="conf_password" placeholder="<?php echo 'Confirm password' ?>" id="conf_password" required />
    </fieldset>
    <input class="btn" type="submit" value="<?php echo 'Submit' ?>" />
</form>
</section>

<?php
}