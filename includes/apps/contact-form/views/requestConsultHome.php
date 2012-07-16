<?php
//this form is displayed on the home page and gets sent to the request-a-consult page
$services = array(
       "Braces",
       "InvisAlign",
       "Teeth Whitening"	    
    );
$actions = array("1" => "request-a-consultation", "2" => "ccok-request-a-consultation");
?>

<div id="consultationForm">

    <div class="headingWrap"><h3>Request a Consultation</h3></div>

	<form action="/<?php echo $actions[$this->siteID];?>" method="post">
		<div>
		    <input type="text" name="RQvalALPHName" id="RQvalALPHName"  placeholder="Name" />
			<label for="RQvalALPHName" class="req">Jane Smith</label>
		</div>
		
        <div>
            <input type="text" name="RQvalPHONPhone_Number" id="RQvalPHONPhone_Number" placeholder="Phone Number"/>
			<label for="RQvalPHONPhone_Number" class="req">201-555-1212</label>
		</div>
		
		<div>
		    <input type="email" name="RQvalMAILEmail_Address" id="RQvalMAILEmail_Address" placeholder="Email Address"/>
            <label for="RQvalMAILEmail_Address" class="req">name@url.com</label>
        </div>
        
        <div>
        		<select name="RQvalALPHServices[]" id="RQvalALPHServices">
        		<option>Service</option>
<?php
                for ($s = 0; $s < count($services); $s++){
                    echo '<option value = "'.$services[$s].'">'.$services[$s].'</option>';
                }
?>
                </select>
                <label for="RQvalALPHServices" class="req">Choose One</label>
		</div>
		
		<div>
            <textarea name="RQvalALPHMessage" id="RQvalALPHMessage" cols="30" rows="3" placeholder="Message"></textarea>
		</div>
		
        <div class="submitWrap"><input type="submit" value="Send Message" name="sub-req-consult" class="btnStyle red" /></div>
	</form>
	
</div>