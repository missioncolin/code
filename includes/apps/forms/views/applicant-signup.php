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
            if (0 !== ($userID = $frms->createUserAccount($post, $_POST["password"], 'applicants'))){
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
        include_once(__DIR__."/applicant-form-fields.php");
?>
        
    </div>
<?php
    }
?>
</section>
<?php
}
