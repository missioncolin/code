<?php 

global $message;
$sent = 0;
$body = '';

if (!empty($_POST) && isset($_POST["sub-contact-us"]) && validate_form($_POST)) {
	
	if (isset($_POST['RQvalALPHMessage']) && !empty($_POST['RQvalALPHMessage'])) { 
		$body .= $_POST['RQvalALPHMessage'] . "\n\n";
	}
	$body .= $_POST['RQvalALPHName']."\n";
	$body .= "Email: ".$_POST['RQvalMAILEmail_Address']."\n";
	$body .= "Phone: ".$_POST['RQvalPHONPhone_Number'];
	if (isset($_POST['OPvalNUMBExtension']) && $_POST['OPvalNUMBExtension'] != ''){
	   $body .= ' ext. ' . make_numeric($_POST['OPvalNUMBExtension']);
    }
    $body .= "\n\n-------\nSent from ".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		
	$from = ($_POST['RQvalMAILEmail_Address'] != "") ? $_POST['RQvalMAILEmail_Address'] : "no-reply@kindersmiles.com";
	$email = $db->return_specific_item(false, 'sysStorageTable', 'value', '--', "application='contact-us'");
	
	mail($email, 'CCOK/KinderSmiles contact from: ' . trim($_POST['RQvalALPHName']), $body, 'From: '. $from);
	
	$sent = 1;
	
}

?>
<div class="blank" style="width:460px !important;">

	<?php
	    $post = array(
	       "RQvalALPHName"          => "",
	       "RQvalPHONPhone_Number"  => "",
	       "OPvalNUMBExtension"     => "",
	       "RQvalMAILEmail_Address" => "",
	       "RQvalALPHMessage"       => "",
	    );
		if($sent == 1){
			print alert_box("<strong>Thank You!</strong> Your message was sent.", 1);
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
				<td style="width:120px"><label for="RQvalALPHName" class="req">Name</label></td>
				<td><input type="text" name="RQvalALPHName" id="RQvalALPHName"  value = "<?php echo $post["RQvalALPHName"];?>" /></td>
			</tr>

			<tr>
				<td style="width:120px"><label for="RQvalPHONPhone_Number" class="req">Phone Number</label></td>
				<td><input type="text" name="RQvalPHONPhone_Number" id="RQvalPHONPhone_Number" value = "<?php echo $post["RQvalPHONPhone_Number"];?>"/> ext. 
				    <input type="text" name="OPvalNUMBExtension" id="OPvalNUMBExtension" style="width:40px;" value = "<?php echo $post["OPvalNUMBExtension"];?>"/></td>
			</tr>
			<tr>
				<td style="width:120px"><label for="RQvalMAILEmail_Address" class="req">Email</label></td>
				<td><input type="email" name="RQvalMAILEmail_Address" id="RQvalMAILEmail_Address" value = "<?php echo $post["RQvalMAILEmail_Address"];?>"/></td>
			</tr>
			<tr>
				<td style="width:120px"><label for="RQvalALPHMessage" class="req">Message</label></td>
				<td><textarea name="RQvalALPHMessage" id="RQvalALPHMessage" cols="35" rows="10"><?php echo $post["RQvalALPHMessage"];?></textarea></td>
			</tr>
			<tr>
				<td colspan="2"><div class="submitWrap"><input type="submit" value="Submit" name="sub-contact-us" style="float:right;" class="btnStyle red" /></div></td>
			</tr>
		</table>
	</form>
</div>
