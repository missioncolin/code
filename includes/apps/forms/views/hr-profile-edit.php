<?php
global $message;
if ($this INSTANCEOF Quipp){

    $submitted = false;
    require_once(dirname(__DIR__)."/Forms.php");
    if (!isset($frms)){
        $frms = new Forms($db);
    }
    $provs  = $db->query("SELECT `itemID`, `provName` FROM `sysProvince` WHERE countryID IN (38, 213) ORDER BY `countryID`, `provName`");
    
    $meta   = $frms->getMetaFieldsByGroup('hr-managers');
    $post   = array();
    foreach($meta as $fields){
        $post[str_replace(" ","_",$fields["fieldLabel"])] = array("code" => $fields["validationCode"], "value" => $frms->get_meta($fields["fieldLabel"]), "label" => $fields["fieldLabel"]);
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
            else if ($field == "Job_Credits" || $field == "Company_Logo"){
                unset($post["Job_Credits"]);
            }
        }
        
        
        if (validate_form($validate)){
            $valid = true;
        }
        if (!empty($_POST["password"]) && (empty($_POST["confirmPassword"]) || $db->escape($_POST["password"], true) !== $db->escape($_POST["confirmPassword"], true))){            
            $message = (empty($message)) ?"<li>Password Confirmation is required and must match the Password</li>" : $message . "<li>Password Confirmation is required and must match the Password</li>";
            $valid = false;
        }
        if (isset($_FILES["Company_Logo"]) && ($_FILES["Company_Logo"]["error"] != 0 && $_FILES["Company_Logo"]["error"] != 4)){
            
           $valid = false;
           $message = (empty($message)) ?"<li>".$uploadErrors[$_FILES["Company_Logo"]["error"]]."</li>" : $message . "<li>".$uploadErrors[$_FILES["Company_Logo"]["error"]]."</li>";
        }
        if ($valid == true){
            $message = "";

            if ($frms->updateUserAccount($post, $_POST["password"])){

                if (isset($_FILES["Company_Logo"]) && ($_FILES["Company_Logo"]["error"] !== 4)){
                    $root = dirname(dirname(dirname(dirname(__DIR__))))."/uploads/profiles";
                    $post["Company_Logo"]["value"] = upload_file("Company_Logo", $root."/".$_SESSION["userID"]."/", $frms->mimeTypes, $frms->thumbnails, true);
                    if (strstr($post["Company_Logo"]["value"],'<strong>') === false){
                        $frms->set_meta($post["Company_Logo"]["label"], $post["Company_Logo"]["value"]);
                    }
                }
                else{
                    
                }
            }
            else{
                $valid = false;
            }
        }
        
    }
?>
<section id="profileEdit">

<?php
    if ($submitted == true && $valid == true){
?>
    <div class="success">
    <h3>Success! Your account was updated</h3>
    </div>
<?php        
    }
?>

    <div id="form">
<?php
        if (!empty($message)){
            echo '<div class="error">';
            echo "Your account was not updated. The following error(s) occurred: <ul>".$message."</ul>";
            echo '</div>';
        }

        include_once(__DIR__."/hr-form-fields.php");
?>

    </div>

</section>
<?php
}
