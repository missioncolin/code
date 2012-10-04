<?php
global $message;
if ($this INSTANCEOF Quipp){

    $submitted = false;
    require_once(dirname(__DIR__)."/Forms.php");
    $frms = new Forms($db);
    $provs  = $db->query("SELECT `itemID`, `provName` FROM `sysProvince` WHERE countryID IN (38, 213) ORDER BY `countryID`, `provName`");
    
    $meta   = $frms->getMetaFieldsByGroup('hr-managers');
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
            $valid = true;
        }
        if (empty($_POST["password"]) || empty($_POST["confirmPassword"]) || ($db->escape($_POST["password"], true) !== $db->escape($_POST["confirmPassword"], true))){            
            $message = (empty($message)) ?"<li>Password is required and must match the Password Confirmation</li>" : $message . "<li>Password is required and must match the Password Confirmation</li>";
            $valid = false;
        }
        if (isset($_FILES["Company_Logo"]) && ($_FILES["Company_Logo"]["error"] != 0 && $_FILES["Company_Logo"]["error"] != 4)){
            
           $valid = false;
           $message = (empty($message)) ?"<li>".$uploadErrors[$_FILES["Company_Logo"]["error"]]."</li>" : $message . "<li>".$uploadErrors[$_FILES["Company_Logo"]["error"]]."</li>";
        }
        if ($valid == true){
            $message = "";
            //create user account
            //get ID and create 1) folder - check for images and upload
            if (0 !== ($userID = $frms->createUserAccount($post, $_POST["password"], 'hr-managers'))){
                $root = dirname(dirname(dirname(dirname(__DIR__))))."/uploads/profiles";
                mkdir($root."/".$userID);
                mkdir($root."/".$userID."/med");
                mkdir($root."/".$userID."/small");
                if (isset($_FILES["Company_Logo"]) && ($_FILES["Company_Logo"]["error"] !== 4)){
                    $post["Company_Logo"]["value"] = upload_file("Company_Logo", $root."/".$userID."/", $frms->mimeTypes, $frms->thumbnails, true);
                    if (strstr($post["Company_Logo"]["value"],'<strong>') === false){
                        $frms->set_meta($post["Company_Logo"]["label"], $post["Company_Logo"]["value"]);
                    }
                }
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
                HR Signup<br />
                <span>Frequently Asked Questions</span>
            </h2>
        </div>
        <ul>
            <li>
                <h4>This is a frequently asked question</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
            <li>
                <h4>This is a frequently asked question</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
            <li>
                <h4>This is a frequently asked question</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
            <li>
                <h4>This is a frequently asked question</h4>
                <p>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam quis risus eget urna mollis ornare vel eu leo. Donec id elit non mi porta gravida at</p>
            </li>
        </ul>
    </div>
<?php
    if ($submitted == true && $valid == true){
        //auto login
        $auth->login($post["Email"]["value"], $_POST["password"]);        
    }
    else{
?>

    <div id="form">
<?php
        if (!empty($message)){
            echo alert_box("The following must be completed in order to create your account: <ul>".$message."</ul>", 2);
        }
        include_once(__DIR__."/hr-form-fields.php");
?>

    </div>
<?php
    }
?>
</section>
<?php
}
