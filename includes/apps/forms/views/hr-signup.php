<?php
global $message;
if ($this INSTANCEOF Quipp){

    $submitted = false;
    require_once(dirname(__DIR__)."/Forms.php");
    $frms = new Forms($db);

    $meta = array(
        array("fieldLabel" => "Email", "validationCode" => "RQvalMAIL"),
        array("fieldLabel" => "Job Credits", "validationCode" => ""),
        array("fieldLabel" => "password", "validationCode" => "RQvalALPH"),
	    array("fieldLabel" => "confirm_password", "validationCode" => "RQvalALPH"),
	    array("fieldLabel" => "Company Name", "validationCode" => "RQvalALPH")
    );
        
    $post   = array();
    foreach($meta as $fields){
        $post[str_replace(" ","_",$fields["fieldLabel"])] = array("code" => $fields["validationCode"], "value" => "", "label" => $fields["fieldLabel"]);
    }
        	
    if (isset($_POST["sbmt-hr-signup"])){
    	
        $uploadErrors = array(
            0 => "There is no error, the file uploaded with success",
            1 => "The uploaded file exceeds the maximum file size", //php.ini
            2 => "The uploaded file exceeds the maximum file size", //web form
            3 => "The uploaded file was only partially uploaded",
            4 => "No file was uploaded",
            6 => "Missing a temporary folder"
        
        );
        
        $submitted = true;
        $valid = false;
        
        $validate = array();
        foreach($post as $field => $nfo){
           
            $validate[$nfo["code"].$field] = "";
            if (isset($_POST[$field])){
                $validate[$nfo["code"].$field] = $_POST[$field];
                $post[$field]["value"] = $_POST[$field];
            }
            else if ($field == "Job_Credits"){
                $post[$field]["value"] = "2";
            }
        }
        
        if (validate_form($validate)){
            
            if ($post['password']['value'] === $post['confirm_password']['value']) {
	            $valid = true;
	            unset($post[2]); //don't want to pass this to createUserAccount
            }
            else {
	            $message = "Your passwords do not match.";
            }

            
        }

        if ($valid == true){
            $message = "";

            if (0 === ($userID = $frms->createUserAccount($post, $_POST["password"], 'hr-managers'))){
                $valid = false;
            }
        }
        
    }
?>

<!-- Breadcrumb -->
<ul id="stepsNew">
    <li class="current"><span>1</span>Create Account</li>
    <li><span>2</span>Name Your Job</li>
    <li><span>3</span>Add Required Skills and Experience</li>
    <li><span>4</span>Add intervue Questions</li>
    <li><span>5</span>Activate Link</li>
</ul>

<section id="hrSignup">
    
<?php
    if ($submitted == true && $valid == true){
        //auto login
        $auth->login($post["Email"]["value"], $_POST["password"], true);     // set to true to redirect to create job    
    }
    else{
?>

    <div id="signupBox">
<?php
        if (!empty($message)) {
            echo alert_box("<strong>Sorry,</strong> there was an issue creating your account: <ul>".$message."</ul>", 2);
        }
?>
	    <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"];?>" id="loginBoxForm">
	        <h2>Create Account</h2>
	        <div class="inputs">
	        <label for="Email">Email</label>
	        <input type="text" id="Email" name="Email" class="full" value="<?php echo (isset($post["Email"]["value"])) ? $post["Email"]["value"] : "";?>" required="required"/>
	        </div>
	        <div class="inputs">
	        <label for="Company Name">Company Name</label>
	        <input type="text" id="Company Name" name="Company Name" class="full" value="<?php echo (isset($post["Company Name"]["value"])) ? $post["Company Name"]["value"] : "";?>" required="required"/>
	        </div>
	        <div class="inputs">
	        <label for="password">Password</label>
	        <input type="password" id="password" name="password" class="full" required="required"/>
	        </div>
	        <div class="inputs">
	        <label for="confirm_password">Confirm Password</label>
	        <input type="password" id="confirm_password" name="confirm_password" class="full" required="required"/>
	        </div>
	        <div>
		        Already a member? <a href="/login">Login here</a></br>
	            <div>
                    <input type="button" onclick="location.href='/';" class="btn blue" value="Cancel"/>
                    <input type="submit" value="Go" class="btn green" name="sbmt-hr-signup" />
                </div>      
	        </div>
	        <div class="clearfix"></div>
	    </form>
    </div>
<?php
    }
?>
</section>
<?php
}
