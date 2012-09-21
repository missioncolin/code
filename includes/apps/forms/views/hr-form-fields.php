<?php
if (isset($post) && isset($provs)){
?>

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
<?php
    if (isset($_SESSION["userID"]) && !empty($post["Company_Logo"]["value"])){
        echo '<div>';
        echo 'Current Image: <img src="/uploads/profiles/'.$_SESSION["userID"].'/'.$post["Company_Logo"]["value"].'" alt="Company Logo" height="80px" />';
        echo '</div>';
    }
?>
    <fieldset>
        <legend>Hiring Manager</legend>

        <label for="First_Name">First Name</label>
        <input type="text" id="First_Name" name="First_Name" class="full" placeholder="First Name" value="<?php echo $post["First_Name"]["value"];?>" required="required"/>
        <label for="Last_Name">Last Name</label>
        <input type="text" id="Last_Name" name="Last_Name" class="full" placeholder="Last Name" value="<?php echo $post["Last_Name"]["value"];?>" required="required"/>
        <label for="Email">Email Address</label>
        <input type="text" id="Email" name="Email" class="full" placeholder="Email Address" value="<?php echo $post["Email"]["value"];?>" required="required"/>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="full" placeholder="Password" <?php echo (!isset($_SESSION["userID"]) ? 'required="required"' : ''); ?>/>

        <label for="confirmPassword">Re-Type Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" class="full bottom" placeholder="Re-Type Password" <?php echo (!isset($_SESSION["userID"]) ? 'required="required"' : ''); ?>/>
    </fieldset>
    <fieldset>
        <legend>Company Name &amp; Location</legend>
        <label for="Company_Name">Company Name</label>
        <input type="text" id="Company_Name" name="Company_Name" class="full" placeholder="Company Name" value="<?php echo $post["Company_Name"]["value"];?>" required="required"/>
        <label for="Company_Address">Address</label>
        <input type="text" id="Company_Address" name="Company_Address" class="half left" placeholder="Address" value="<?php echo $post["Company_Address"]["value"];?>" required="required"/>
        <label for="Company_City">City</label>
        <input type="text" id="Company_City" name="Company_City" class="half" placeholder="City" value="<?php echo $post["Company_City"]["value"];?>" required="required"/>
        <label for="Company_Postal_Code">Postal Code/Zip Code</label>
        <input type="text" id="Company_Postal_Code" name="Company_Postal_Code" class="half" placeholder="Postal Code" value="<?php echo $post["Company_Postal_Code"]["value"];?>" required="required"/>
        <label for="Company_Province">Province/State</label>
        <div class="select half">
        <select id="Company_Province" name="Company_Province" required="required">
<?php
        if ($db->valid($provs)){
            while ($row = $db->fetch_assoc($provs)){
                echo '<option value="'.$row["itemID"].'"'.($post["Company_Province"]["value"] == $row["itemID"] ? ' selected="selected"':'').'>'.$row["provName"].'</option>';
            }
        }
?>               
        </select>
        </div>
        <label for="Company_Country">Country</label>
        <div class="half bottom left select">
        <select name="Company_Country" id="Company_Country" class="half bottom" required="required">
        <option value="38"<?php ($post["Company_Country"]["value"] == 38 || empty($post["Company_Country"]["value"]) ? ' selected="selected"':'')?>>Canada</option>
        <option value="213"<?php ($post["Company_Country"]["value"] == 213 ? ' selected="selected"':'')?>>United States</option>
        </select>
        </div>
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
<?php
}