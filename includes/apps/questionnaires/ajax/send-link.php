<?php

require dirname(dirname(dirname(__DIR__))) . '/init.php';

$encryptionKey = "elephants321";
$userID = $_SESSION['userID'];

//One of the MCRYPT_ciphername constants, or the name of the algorithm as string.
//One of the MCRYPT_MODE_modename constants, or one of the following strings: "ecb", "cbc", "cfb", "ofb", "nofb" or "stream".
$encryptedID = trim(base64url_encode(mcrypt_encrypt(
    MCRYPT_RIJNDAEL_256,
    $encryptionKey,
    $userID,
    MCRYPT_MODE_ECB,
    mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB),
    MCRYPT_RAND
))));
//$userID2 = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $encryptionKey , base64url_decode(rtrim($encryptedID)) , MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));


$link        = $_SERVER["SERVER_NAME"]."/apply/".$_POST['job']."?user=".$encryptedID;
$companyName = strip_tags(substr($_POST['companyName'], 0, 100));
$jobTitle    = strip_tags(substr($_POST['jobTitle'], 0, 100));
$email       = substr($_POST['email'], 0, 100);
$firstName   = strip_tags(substr($_POST['firstName'], 0, 100));
$lastName    = strip_tags(substr($_POST['lastName'], 0, 100));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // invalid emailaddress
    die('Invalid email');
}

$mail = new PHPMailer();

$mail->Subject = "Intervue application link";
$mail->AddAddress($email);
$mail->SetFrom('no-reply@intervue.ca', 'Intervue');
$mail->Body = "<p>Dear ".$firstName." ".$lastName.",</p>

<p>Thank you for applying to ".$companyName." for the position of ".$jobTitle.". Your application has been saved. When you are ready to continue the application process, please click the link below.</p>

<p><a href=\"http://".$link."\">".$link."</a></p>

<p>Thank you for applying to ".$companyName."</p>";

$mail->AltBody = strip_tags($mail->Body);

echo ($mail->Send() ? 'OK' : 'Email not sent');

session_destroy();

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
