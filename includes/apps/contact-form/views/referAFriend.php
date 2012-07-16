<?php 

global $message;
$sent = 0;
$body = '';

if (!empty($_POST) && isset($_POST["sub-refer"]) && validate_form($_POST)) {

    $templates = array(
        "1" => "referEmail.php",
        "2" => "referCCOKEmail.php"
    );
    
    $admin = $db->return_specific_item(false, 'sysStorageTable', 'value', '--', "application='refer-a-friend' AND `siteID` = ".$quipp->siteID);
    
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
    $headers .= 'From: '.$_POST['RQvalALPHName'].' <'.$_POST['RQvalMAILEmail_Address'].'>' . "\r\n";
    $headers .= 'Bcc: '.$admin."\r\n";
	       
    $body .= '<p>Greetings,</p><p>Your friend '.$_POST['RQvalALPHName'].' was visiting The Center for Cosmetic Orthodontics and KinderSmiles website and thought you would be interested in 
    their products and services.</p>';
    $body .= '<p>Visit <a href="http://'.$_SERVER["SERVER_NAME"].'">'.$_SERVER["SERVER_NAME"].'</a> to learn more about it.</p>';   
       
	$body .= '<p>Referred By: '.$_POST['RQvalALPHName'].'<br>';
	$body .= 'Email: '.$_POST['RQvalMAILEmail_Address'].'</p>';
    $body .= '<p>-------<br>Sent from '.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"].'</p>';
		
	$from = ($_POST['RQvalMAILEmail_Address'] != "") ? $_POST['RQvalMAILEmail_Address'] : "no-reply@kindersmiles.com";
	
	
	$email = trim($_POST["RQvalMAILFriend_Email"]);
	
	try{
	   $content = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/apps/contact-form/views/".$templates[$quipp->siteID]);
	   $content = str_ireplace('%BODY%',$body,$content);
	   $content = str_ireplace('%SERVERNAME%',$_SERVER["SERVER_NAME"],$content);
	   $content = str_ireplace('%TITLE%','A Friendly Referral to',$content);
	
	   mail($email, 'A Friendly Referral to '.$_SERVER["SERVER_NAME"], $content, $headers);
	
	   $sent = 1;
    }
    catch(Exception $e){
        $sent = 0;
        $message = $e->getMessage();
    }
	
}

?>
<div class="blank" style="width:460px !important;">

	<?php
	    $post = array(
	       "RQvalALPHName"          => "",
	       "RQvalMAILEmail_Address" => "",
	       "RQvalMAILFriend_Email" => "",
	    );
	    
		if($sent == 1){
			print alert_box("<strong>Thank You!</strong> Your message was sent. Please complete the form again to refer another friend.", 1);
		}else if (isset($message) && $message != '') {
			print alert_box($message, 2);
			foreach ($_POST as $key => $value){
			     $post[$key] = $value;
			}
		}
	
	?>
	<form action="<?php print $_SERVER['REQUEST_URI']; ?>" method="post">
		<table>
			<tr>
				<td style="line-height:3; width:180px"><label for="RQvalALPHName" class="req">Your Name</label></td>
				<td><input type="text" name="RQvalALPHName" id="RQvalALPHName"  value = "<?php echo $post["RQvalALPHName"];?>" /></td>
			</tr>

			<tr>
				<td style="line-height:3; width:180px"><label for="RQvalMAILEmail_Address" class="req">Your Email Address</label></td>
				<td><input type="email" name="RQvalMAILEmail_Address" id="RQvalMAILEmail_Address" value = "<?php echo $post["RQvalMAILEmail_Address"];?>"/></td>
			</tr>
			
			<tr>
				<td style="line-height:3; width:180px"><label for="RQvalMAILFriend_Email" class="req">Your Friend's Email Address</label></td>
				<td><input type="email" name="RQvalMAILFriend_Email" id="RQvalMAILFriend_Email" value = "<?php echo $post["RQvalMAILFriend_Email"];?>"/></td>
			</tr>
			<tr>
				<td colspan="2"><div class="submitWrap"><input type="submit" value="Submit" name="sub-refer" style="float:right;" class="btnStyle red" /></div></td>
			</tr>
		</table>
	</form>
</div>
