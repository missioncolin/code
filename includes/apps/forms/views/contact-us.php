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
                <span>Lorem Ipsum</span>
            </h2>
        </div>
        <ul>
            <li>
                <h4>Subtitle 1</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
            <li>
                <h4>Subtitle 2</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
            <li>
                <h4>Subtitle 3</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
            <li>
                <h4>Subtitle 4</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
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
