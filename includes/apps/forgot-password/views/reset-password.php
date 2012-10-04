<?php
    
    if(!class_exists('Quipp')) {
        require_once dirname(dirname(dirname(__DIR__))) . '/init.php';
    }
    
    global $quipp;

	require_once dirname(__DIR__) . '/ForgotPassword.php';
    $fp = new ForgotPassword($db);
?>
<h2>Reset your password</h2><p></p>

<?php

    $showForm = false;
    if (isset($_GET['token'])) {
        $showForm = true;
        try {
            $user = $fp->verifyToken($_GET['token'], 'fpHash');
            if (isset($_POST['password'], $_POST['conf_password'])) {
    
                if ($_POST['password'] != $_POST['conf_password']) {
                    echo 'Password mismatch';
    
                } else {
    
    
                    try {
                        $result = $user->changePassword($_POST['password']);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        $showForm = false;
                    }
                    
                    if ($result) {
                        $user->removeHash();
                        $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/login';
                        echo 'Your password was changed. Please <a href="' . $url . '">proceed to the login</a>';

                    } else {
                        echo 'Unable to change account password';
    
                    }
                    
                    $showForm = false;
                }
    
            }
    
        } catch (Exception $e) {
            echo $e->getMessage();
            $showForm = false;
        }
    
    } else {
        echo 'No token provided';
    }

if ($showForm == true) { 
?>

<form action="?token=<?php echo $_GET['token']; ?>" method="post">
    <div>
        <label for="email"><?php echo 'Your email' ?></label> <input type="email" name="email" id="email" value="<?php echo $user->info['Email']; ?>" /><br>
        <label for="password"><?php echo 'New password' ?></label> <input type="password" name="password" id="password" required /><br>
        <label for="conf_password"><?php echo 'Confirm password' ?></label> <input type="password" name="conf_password" id="conf_password" required />

        <input class="btn" type="submit" value="<?php echo 'Submit' ?>" />
    </div>
</form>

<?php
}