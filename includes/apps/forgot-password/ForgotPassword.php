<?php

class ForgotPassword {

    var $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function isTokenLive($token) {
        list($expiry, $token) = explode('|', $token);
        return (boolean)  ((time() - (int)$expiry) < 0);
    }

    /**
     * @param string
     * @param string (regHash|fpHash)
     * @throws Exception
     * @return User
     */
    public function verifyToken($token, $type) {
        if (!$this->isTokenLive($token)) {
            throw new Exception("Expired token");
        }


        $qry = sprintf("SELECT itemID FROM sysUsers WHERE `%s` = '%s'",
            $this->db->escape($type),
            $this->db->escape($token));
            
        $res = $this->db->query($qry);

        if ($this->db->num_rows($res) == 1) {
            list ($id) = $this->db->fetch_array($res);
            return new User($this->db, $id);
        }
        
        throw new Exception("Invalid token");
    }

    
    /**
     * @return string
     */
    public function generateToken() {
        return uniqid(strtotime('+72hours') . '|1', true);
    }


    /**
     * Send an email to this user to
     * @param string A valid email address
     * @todo Fetch email from database
     */
    public function sendReset($email) {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }


        // check to see if the email exists
        $qry = sprintf("SELECT userID FROM sysUGFValues WHERE sysStatus='active' AND sysOpen='1' AND fieldID='3' AND value='%s'",
            $this->db->escape($email));
        $res = $this->db->query($qry);
        if ($this->db->num_rows($res) > 0) {

            list($userID) = $this->db->fetch_array($res);

            // insert a unique hash into the DB to check on later
            $hash = $this->generateToken();
            $qry = sprintf("UPDATE sysUsers SET fpHash='%s' WHERE itemID='%d'",
                $hash,
                $userID);
            $this->db->query($qry);

            $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/reset-password?token=' . $hash;

            $mail = new PHPMailer();

            $mail->Subject = ucwords($_SERVER['HTTP_HOST']) . " password reset";
            $mail->AddAddress($email);
            $mail->SetFrom('no-reply@intervue.ca', 'Intervue');


            $mail->Body = "<p>Hi there,</p>

<p>There was recently a request to change the password on your account.</p>

<p>If you requested this password change, please set a new password by following the link below:</p>

<p>{$resetLink}</p>

<p>If you don't want to change your password, just ignore this message.</p>

<p>Thanks</p>";

            $mail->AltBody = strip_tags($mail->Body);
            
            return $mail->Send();
        } else {
            return 'No user found';
        }

        return false;
    }

}