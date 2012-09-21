<?php
global $message;
if ($this INSTANCEOF Quipp){

    $submitted = false;
    require_once(dirname(__DIR__)."/Forms.php");
    $frms = new Forms($db);
    
    $meta   = $frms->getMetaFieldsByGroup('applicants');
    $post   = array();
    foreach($meta as $fields){
        $post[str_replace(" ","_",$fields["fieldLabel"])] = array("code" => $fields["validationCode"], "value" => "", "label" => $fields["fieldLabel"]);
    }
    if (isset($_POST["sbmt-ap-signup"])){
    
        
        $submitted = true;
        $valid = false;
        
        $validate = array();
        foreach($post as $field => $nfo){
            
            $validate[$nfo["code"].$field] = "";
            if (isset($_POST[$field])){
                $validate[$nfo["code"].$field] = $_POST[$field];
                $post[$field]["value"] = $_POST[$field];
            }
        }
        
        
        if (validate_form($validate)){
            $valid = true;
        }
        if (empty($_POST["password"]) || empty($_POST["confirmPassword"]) || ($db->escape($_POST["password"], true) !== $db->escape($_POST["confirmPassword"], true))){            
            $message = (empty($message)) ?"<li>Password is required and must match the Password Confirmation</li>" : $message . "<li>Password is required and must match the Password Confirmation</li>";
            $valid = false;
        }
        if (isset($_FILES["Video_Profile"]) && ($_FILES["Video_Profile"]["error"] != 0 && $_FILES["Video_Profile"]["error"] != 4)){
            
           $valid = false;
           $message = (empty($message)) ?"<li>".$uploadErrors[$_FILES["Video_Profile"]["error"]]."</li>" : $message . "<li>".$uploadErrors[$_FILES["Video_Profile"]["error"]]."</li>";
        }
        if ($valid == true){
            $message = "";
            //create user account
            //get ID and create 1) folder - check for images and upload
            if (0 !== ($userID = $frms->createUserAccount($post, $_POST["password"]))){
                $root = dirname(dirname(dirname(dirname(__DIR__))))."/uploads/profiles";
                mkdir($root."/".$userID);
              /*  
              ========== INSERT VIDEO CAPTURE HERE. Video will be stored in uploads/profiles/{userID} ==========
              
                if (isset($_FILES["Video_Profile"]) && ($_FILES["Video_Profile"]["error"] !== 4)){
                    $post["Video_Profile"]["value"] = upload_file("Video_Profile", $root."/".$userID."/", $frms->vMimeTypes, false, true);
                    if (strstr($post["Video_Profile"]["value"],'<strong>') === false){
                        $frms->set_meta($post["Video_Profile"]["label"], $post["Video_Profile"]["value"]);
                    }
                }*/

            }
            else{
                $valid = false;
            }
        }
        
    }
?>
<section id="hrSignup">
    
    <div id="card" class="box">
        <div class="heading">
            <h2>
                Applicant Signup<br />
                <span>Sample Interview Questions</span>
            </h2>
        </div>
        <ul>
            <li>
                <h4>Question 1</h4>
                <p>--Video Capture &amp; Playback--</p>
            </li>
            <li>
                <h4>Question 2</h4>
                <p>--Video Capture &amp; Playback--</p>
            </li>
            <li>
                <h4>Question 3</h4>
                <p>--Video Capture &amp; Playback--</p>
            </li>
            <li>
                <h4>Question 4</h4>
                <p>--Video Capture &amp; Playback--</p>
            </li>
            <li>
                <h4>Question 5</h4>
                <p>--Video Capture &amp; Playback--</p>
            </li>
        </ul>
    </div>
<?php
    if ($submitted == true && $valid == true){
?>
    <div id="form">
    <h3>Thank you! Your account has been created. Please continue to login using your <strong>Email Address</strong> and the password you provided</h3>
    <a class="btn" href="/apply" >Continue</a>
    </div>
<?php        
    }
    else{

?>
    <div id="form">

<?php
        if (!empty($message)){
            echo '<div class="error">';
            echo "The following must be completed in order to create your account: <ul>".$message."</ul>";
            echo '</div>';
        }
?>
        <h3>Applicant Information</h3>
        <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"];?>">
            <div>
                <label for="videoProfile">Upload your <strong>Video</strong></label>
                <input type="file" id="companyLogo" name="Video_Profile" />
            </div>
            <div>
                The following file types/extensions are accepted: 
                <ul>
                    <li>video/mp4 (.mp4)</li>
                </ul>
            </div>
            <fieldset>
                <legend>Account Details</legend>

                <label for="First_Name">First Name</label>
                <input type="text" id="First_Name" name="First_Name" class="full" placeholder="First Name" value="<?php echo $post["First_Name"]["value"];?>" required="required"/>
                <label for="Last_Name">Last Name</label>
                <input type="text" id="Last_Name" name="Last_Name" class="full" placeholder="Last Name" value="<?php echo $post["Last_Name"]["value"];?>" required="required"/>
                <label for="Email">Email Address</label>
                <input type="text" id="Email" name="Email" class="full" placeholder="Email Address" value="<?php echo $post["Email"]["value"];?>" required="required"/>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="half left bottom" placeholder="Password"  required="required"/>
                <label for="confirmPassword">Re-Type Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="half bottom" placeholder="Re-Type Password" required="required"/>
            </fieldset>

            <fieldset>
                <legend>Website &amp; Social Links</legend>
                <label for="Website_or_Blog_URL">Website</label>
                <input type="text" id="Website_or_Blog_URL" name="Website_or_Blog_URL" class="half left" placeholder="Website" value="<?php echo $post["Website_or_Blog_URL"]["value"];?>"/>
                <label for="Facebook_Username">Facebook</label>
                <input type="text" id="Facebook_Username" name="Facebook_Username" class="half" placeholder="Facebook" value="<?php echo $post["Facebook_Username"]["value"];?>"/>
                <label for="Twitter_Username">Twitter</label>
                <input type="text" id="Twitter_Username" name="Twitter_Username" class="half left bottom" placeholder="Twitter" value="<?php echo $post["Twitter_Username"]["value"];?>"/>
                <label for="LinkedIn_Username">LinkedIn</label>
                <input type="text" id="LinkedIn_Username" name="LinkedIn_Username" class="half bottom" placeholder="LinkedIn" value="<?php echo $post["LinkedIn_Username"]["value"];?>"/>
            </fieldset>

            <input type="submit" value="Submit" class="btn" name="sbmt-ap-signup" />
        </form>
    </div>
<?php
    }
?>
</section>
<?php
}
