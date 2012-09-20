<?php
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
        
        global $message;
        
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
        else{
            $message = "";
            //create user account
            //get ID and create 1) folder - check for images and upload
            if (0 !== ($userID = $frms->createUserAccount($post, $_POST["password"]))){
                $root = dirname(dirname(dirname(dirname(__DIR__))))."/uploads/profiles";
                echo $root;
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
?>
    <div id="form">
    <h3>Thank you! Your account has been created. Please continue to login using your <strong>Email Address</strong> and the password you provided</h3>
    <a class="btn" href="/create-job" >Continue</a>
    </div>
<?php        
    }
    else{
        if (!empty($message)){
            echo '<div class="error">';
            echo "The following must be completed in order to create your account: <ul>".$message."</ul>";
            echo '</div>';
        }
?>

    <div id="form">
        <h3>Company Information</h3>
        <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"];?>">
            <div>
                <label for="companyLogo">Upload your <strong>Company Logo</strong></label>
                <input type="file" id="companyLogo" name="Company_Logo" />
            </div>
            <div>
                The following file types/extensions are accepted: 
                <ul>
                    <li>Image/JPEG (.jpg)</li>
                    <li>Image/PNG (.png)</li>
                </ul>
            </div>
            <fieldset>
                <legend>Hiring Manager</legend>

                <label for="First_Name">First Name</label>
                <input type="text" id="First_Name" name="First_Name" class="full bottom" placeholder="First Name" value="<?php echo $post["First_Name"]["value"];?>" required="required"/>
                <label for="Last_Name">Last Name</label>
                <input type="text" id="Last_Name" name="Last_Name" class="full bottom" placeholder="Last Name" value="<?php echo $post["Last_Name"]["value"];?>" required="required"/>
                <label for="Email">Email Address</label>
                <input type="text" id="Email" name="Email" class="full bottom" placeholder="Email Address" value="<?php echo $post["Email"]["value"];?>" required="required"/>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="full bottom" placeholder="Password"  required="required"/>

                <label for="confirmPassword">Re-Type Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="full bottom" placeholder="Re-Type Password" required="required"/>
            </fieldset>
            <fieldset>
                <legend>Company Name &amp; Location</legend>
                <label for="Company_Name">Company Name</label>
                <input type="text" id="Company_Name" name="Company_Name" class="full" placeholder="Company Name" value="<?php echo $post["Company_Name"]["value"];?>" required="required"/>
                <label for="Company_Address">Address</label>
                <input type="text" id="Company_Address" name="Company_Address" class="half left bottom" placeholder="Address" value="<?php echo $post["Company_Address"]["value"];?>" required="required"/>
                <label for="Company_City">City</label>
                <input type="text" id="Company_City" name="Company_City" class="half bottom" placeholder="City" value="<?php echo $post["Company_City"]["value"];?>" required="required"/>
                <label for="Company_Postal_Code">Postal Code/Zip Code</label>
                <input type="text" id="Company_Postal_Code" name="Company_Postal_Code" class="half bottom" placeholder="Postal Code" value="<?php echo $post["Company_Postal_Code"]["value"];?>" required="required"/>
                <label for="Company_Province">Province/State</label>
                <select id="Company_Province" name="Company_Province" class="half bottom" required="required">
 <?php
                if ($db->valid($provs)){
                    while ($row = $db->fetch_assoc($provs)){
                        echo '<option value="'.$row["itemID"].'">'.$row["provName"].'</option>';
                    }
                }
 ?>               
                </select>
                <label for="Company_Country">Country</label>
                <select name="Company_Country" id="Company_Country" class="half bottom" required="required">
                <option value="38">Canada</option>
                <option value="213">United States</option>
                </select>
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
            <fieldset>
                <legend>Company Information &amp; Size</legend>
                <label for="Business_Type">Business Type</label>
                <input type="text" id="Business_Type" name="Business_Type" class="half left" placeholder="Business Type" value="<?php echo $post["Business_Type"]["value"];?>"/>
                <label for="Year_Founded">Founded</label>
                <input type="text" id="Year_Founded" name="Year_Founded" class="half" placeholder="Founded" value="<?php echo $post["Year_Founded"]["value"];?>"/>
                <label for="Business_Size">Size</label>
                <input type="text" id="Business_Size" name="Business_Size" class="half left bottom" placeholder="Size" value="<?php echo $post["Business_Size"]["value"];?>"/>
                <label for="Industry">Industry</label>
                <input type="text" id="Industry" name="Industry" class="half bottom" placeholder="Industry" value="<?php echo $post["Industry"]["value"];?>" />
            </fieldset>

            <fieldset>
                <legend>About The Company</legend>
                <label for="Company_Bio">About The Company</label>
                <textarea id="Company_Bio" name="Company_Bio" class="bottom" rows="5"><?php echo $post["Company_Bio"]["value"];?></textarea>
            </fieldset>
            <input type="submit" value="Submit" class="btn" name="sbmt-hr-signup" />
        </form>
    </div>
<?php
    }
?>
</section>
<?php
}
