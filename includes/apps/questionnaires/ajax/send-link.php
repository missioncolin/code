<?php

require dirname(dirname(dirname(__DIR__))) . '/init.php';

$link 			= $_SERVER["SERVER_NAME"]."/apply/".$_POST['job']."?user=".$_SESSION['userID'];
$companyName 	= strip_tags(substr($_POST['companyName'], 0, 100));
$jobTitle 		= strip_tags(substr($_POST['jobTitle'], 0, 100));
$email 			= substr($_POST['email'], 0, 100);
$firstName 		= strip_tags(substr($_POST['firstName'], 0, 100));
$lastName 		= strip_tags(substr($_POST['lastName'], 0, 100));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // invalid emailaddress
    die();
}

   	$mail = new PHPMailer();

    $mail->Subject = ucwords($_SERVER['HTTP_HOST']) . " application link";
            $mail->AddAddress($email);
            $mail->SetFrom('no-reply@intervue.ca', 'Intervue');
            $mail->Body = "<p>Dear ".$firstName." ".$lastName.",</p>

<p>Thank you for applying to ".$companyName." for the position of ".$jobTitle.". Your application has been saved. When you are ready to continue the application process, please click the link below.</p>

<p><a href=\"".$link."\">".$link."</a></p>

<p>Thank you for applying to ".$companyName."</p>";

            $mail->AltBody = strip_tags($mail->Body);
            
            return $mail->Send();

