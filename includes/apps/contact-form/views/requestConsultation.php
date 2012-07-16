<?php 

global $message;
$sent = 0;
$body = '';

if (!empty($_POST) && isset($_POST["sub-req-consult"])) {

    $submitted = $_POST;
    unset($submitted["sub-req-consult"]);
    unset($submitted["RQvalALPHServices"]);
    
    $submitted["RQvalALPHServices"] = (isset($_POST["RQvalALPHServices"]) && is_array($_POST["RQvalALPHServices"]))?implode(",",$_POST["RQvalALPHServices"]):"";
    
    if (validate_form($submitted)){
	       
	    $body .= "A Request for Consultation was submitted for the following services:\n\n";
	    $body .= implode("\n",$_POST['RQvalALPHServices'])."\n\n";   
	       
    	if (isset($_POST['RQvalALPHMessage']) && !empty($_POST['RQvalALPHMessage'])) { 
    		$body .= "Additional Message:\n".$_POST['RQvalALPHMessage'] . "\n\n";
    	}
    	$body .= "Submitted By:\n".$_POST['RQvalALPHName']."\n";
    	$body .= "Email: ".$_POST['RQvalMAILEmail_Address']."\n";
    	$body .= "Phone: ".$_POST['RQvalPHONPhone_Number'];
    	if (isset($_POST['OPvalNUMBExtension']) && $_POST['OPvalNUMBExtension'] != ''){
    	   $body .= ' ext. ' . make_numeric($_POST['OPvalNUMBExtension']);
        }
        $body .= "\n\n-------\nSent from ".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    		
    	$from = ($_POST['RQvalMAILEmail_Address'] != "") ? $_POST['RQvalMAILEmail_Address'] : "no-reply@kindersmiles.com";
    	$email = $db->return_specific_item(false, 'sysStorageTable', 'value', '--', "application='request-a-consult'");
    	
    	mail($email, 'CCOK/KinderSmiles Consultation Request from: ' . trim($_POST['RQvalALPHName']), $body, 'From: '. $from);
    	
    	$sent = 1;
	}
	
}

?>
<div class="blank" style="width:460px !important;">

	<?php
	    $post = array(
	       "RQvalALPHName"          => "",
	       "RQvalPHONPhone_Number"  => "",
	       "OPvalNUMBExtension"     => "",
	       "RQvalMAILEmail_Address" => "",
	       "RQvalALPHServices"      => array(),
	       "RQvalALPHMessage"       => "",
	    );
	    
	    $services = array(
	       "Braces",
	       "InvisAlign",
	       "Teeth Whitening"	    
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
				<td style="width:180px;"><label for="RQvalALPHName" class="req">Name</label></td>
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
				<td style="width:120px"><label for="RQvalALPHServices" class="req">Services of Interest</label><br /><small>Please select at least one (1) option</small></td>
				<td>
<?php
                for ($s = 0; $s < count($services); $s++){
                    $checked = (in_array($services[$s],$post["RQvalALPHServices"]))?'checked="checked"':'';
                    echo '<input type="checkbox" name="RQvalALPHServices[]" id="RQvalALPHServices_'.$services[$s].'" value = "'.$services[$s].'" '.$checked. '/>'.$services[$s].'<br />';
                }
?>
				</td>
			</tr>
			<tr>
				<td style="width:120px"><label for="RQvalALPHMessage" class="req">Message</label></td>
				<td><textarea name="RQvalALPHMessage" id="RQvalALPHMessage" cols="35" rows="10"><?php echo $post["RQvalALPHMessage"];?></textarea></td>
			</tr>
			<tr>
				<td colspan="2"><div class="submitWrap"><input type="submit" value="Submit" name="sub-req-consult" style="float:right;" class="btnStyle red" /></div></td>
			</tr>
		</table>
    </form>
</div>
