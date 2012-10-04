<?php
global $message;
if ($this INSTANCEOF Quipp){

    $submitted = false;
    require_once(dirname(__DIR__)."/Forms.php");
    if (!isset($frms)){
        $frms = new Forms($db);
    }
    
    $meta   = $frms->getMetaFieldsByGroup('applicants');
    $post   = array();
    foreach($meta as $fields){
        $post[str_replace(" ","_",$fields["fieldLabel"])] = array("code" => $fields["validationCode"], "value" => $frms->get_meta($fields["fieldLabel"]), "label" => $fields["fieldLabel"]);
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
        if (!empty($_POST["password"]) && (empty($_POST["confirmPassword"]) || $db->escape($_POST["password"], true) !== $db->escape($_POST["confirmPassword"], true))){            
            $message = (empty($message)) ?"<li>Password Confirmation is required and must match the Password</li>" : $message . "<li>Password Confirmation is required and must match the Password</li>";
            $valid = false;
        }
        if (isset($_FILES["Video_Profile"]) && ($_FILES["Video_Profile"]["error"] != 0 && $_FILES["Video_Profile"]["error"] != 4)){
            
           $valid = false;
           $message = (empty($message)) ?"<li>".$uploadErrors[$_FILES["Video_Profile"]["error"]]."</li>" : $message . "<li>".$uploadErrors[$_FILES["Video_Profile"]["error"]]."</li>";
        }
        if ($valid == true){
            $message = "";

            if (!$frms->updateUserAccount($post, $_POST["password"])){
                $valid = false;
            }
        }
        
    }
?>
<section id="profileEdit">
    
<?php
    if ($submitted == true && $valid == true){
        header('location: /profile');       
    }

?>
<div id="form">

<p>**Leave "Password" field empty to keep your current one</p>


<?php
        if (!empty($message)){
            echo "<div id='steps' class='errorMessage'>";
            echo "<div class='fail'><span></span></div>";
            echo '<div class="padMe">';
            echo "Your account was not updated. The following error(s) occurred: <ul>".$message."</ul>";
            echo "</div></div>";
        }
        include_once(__DIR__."/applicant-form-fields.php");
?>
        
</div>

</section>
<?php
}
