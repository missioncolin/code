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

    $body .= "\n\n-------\nSent from ".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		
	$from = ($_POST['RQvalMAILEmail_Address'] != "") ? $_POST['RQvalMAILEmail_Address'] : "no-reply@intervue.com";
	$email = $db->return_specific_item(false, 'sysStorageTable', 'value', 'info@intervue.com', "application='contact-us'");
	
	mail($email, 'Intervue contact from: ' . trim($_POST['RQvalALPHName']), $body, 'From: '. $from);
	
	$sent = 1;
	
}

?>
<section id="contactUs">
    
    <div id="card" class="box">
        <div class="heading">
            <h2>
                Contact Us<br />
                <span>We can help</span>
            </h2>
        </div>
        <ul>
            <li>
                <h4>Want a product demo?</h4>
                <p>We will show you the amazing features and benefits of Intervue, set up your first job for you and get you started in minutes.</p>
            </li>
            <li>
                <h4>Have a question?</h4>
                <p>We are happy to help, one of our customer service agents will contact you within 24 hours and help you with anything you need.</p>
            </li>
            <li>
                <h4>Have a suggestion?</h4>
                <p>We want to hear what you have to say about intervue and if you have ideas on how to improve it, or if there are features that you need to make the service work better for you just let us know.</p>
            </li>
            <li>
                <h4>Just want to talk?</h4>
                <p>We at intervue are happy to listen</p>
            </li>
        </ul>
    </div>

	<?php
	    $post = array(
	       "RQvalALPHName"          => "",
	       "RQvalPHONPhone_Number"  => "",
	       "RQvalMAILEmail_Address" => "",
	       "RQvalALPHMessage"       => "",
	    );
	
	?>
	<div id="form">
<?php
        if($sent == 1){
			print '<div class="success">Thank You!</strong> Your message was sent.</div>';
		}
		else if (isset($message) && !empty($message)) {
			print '<div class="error"><ul>'.$message.'</ul></div>';
			foreach ($_POST as $key => $value){
			     $post[$key] = $value;
			}
		}
?>
	<form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"];?>">
	   <fieldset>
                <legend>Please complete all fields</legend>

                <label for="Name">First Name</label>
                <input type="text" id="Name" name="RQvalALPHName" class="full" placeholder="Name" value="<?php echo $post["RQvalALPHName"];?>" required="required"/>
                <label for="Phone_Number">Phone Number</label>
                <input type="text" id="Phone_Number" name="RQvalPHONPhone_Number" class="full" placeholder="Phone Number" value="<?php echo $post["RQvalPHONPhone_Number"];?>" required="required"/>
                <label for="Email">Email Address</label>
                <input type="text" id="Email" name="RQvalMAILEmail_Address" class="full" placeholder="Email Address" value="<?php echo $post["RQvalMAILEmail_Address"];?>" required="required"/>
                <label for="Message">Message</label>
                <textarea name="RQvalALPHMessage" id="Message" cols="35" rows="10" placeholder="Message"><?php echo $post["RQvalALPHMessage"];?></textarea>
            </fieldset>
		<input type="submit" value="Submit" class="btn" name="sub-contact-us" />
	</form>
	</div>
</section>
