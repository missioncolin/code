<?php
$meta   = $frms->getMetaFieldsByGroup('hr-managers');
$post   = array();
foreach($meta as $fields){
    $post[str_replace(" ","_",$fields["fieldLabel"])] = array("value" => $frms->get_meta($fields["fieldLabel"]));
}
$provs  = $db->query("SELECT `itemID`, `provName` FROM `sysProvince` WHERE countryID IN (38, 213) ORDER BY `countryID`, `provName`");
if ($db->valid($provs)){
    while ($row = $db->fetch_assoc($provs)){
        if ($row["itemID"] == $post["Company_Province"]["value"]){
            $post["Company_Province"]["value"] = trim($row["provName"]);
        }
    }
}
?>
<div class="fifty">
<h3 class="profileViewHeading">

<?php
	$logoFile = "/uploads/profiles/" . $_SESSION["userID"] . "/" . $post["Company_Logo"]["value"];

	if(!file_exists($logoFile)) {
		$logoFile = "/themes/Intervue/img/hrPlaceholder.jpg";
	}

?>
<img src="<?php echo $logoFile; ?>" alt="Company Logo" height="80px" width="auto" /><span><?php echo $post["Company_Name"]["value"];?></span>
<a href="/profile/edit">Edit Profile</a>
</h3>
<form>
    <fieldset>
        <legend>Username</legend>
        <input type="text" class="full" placeholder="System Login" value="<?php echo $post["Email"]["value"];?>" disabled="disabled"/>
    </fieldset>
    <fieldset>
        <legend>Contact Information</legend>
        <label for="First_Name">First Name</label>
        <input type="text" value="<?php echo $post["First_Name"]["value"];?>" disabled="disabled" />
        <label for="Last_Name">Last Name</label>
        <input type="text" value="<?php echo $post["Last_Name"]["value"];?>" disabled="disabled"/>
        <label for="Email">Email Address / Login</label>
        <input type="text" value="<?php echo $post["Email"]["value"];?>" disabled="disabled"/>
    </fieldset>
    <fieldset>
        <legend>Company Name &amp; Location</legend>
        <label for="Company_Name">Company Name</label>
        <input type="text" value="<?php echo $post["Company_Name"]["value"];?>" disabled="disabled"/>
        <label for="Company_Address">Address</label>
        <input type="text" value="<?php echo $post["Company_Address"]["value"];?>" disabled="disabled"/>
        <label for="Company_City">City</label>
        <input type="text" value="<?php echo $post["Company_City"]["value"];?>" disabled="disabled"/>
        <label for="Company_Postal_Code">Postal Code/Zip Code</label>
        <input type="text" value="<?php echo $post["Company_Postal_Code"]["value"];?>" class="half left" disabled="disabled"/>
        <label for="Company_Province">Province/State</label>
        <input type="text" value="<?php echo $post["Company_Province"]["value"];?>" class="half" disabled="disabled"/>
        <label for="Company_Country">Country</label>
        <input type="text" value="<?php echo ($post["Company_Country"]["value"] == 213 ? "United States" : "Canada");?>" disabled="disabled" />
    </fieldset>
    <fieldset>
        <legend>Website &amp; Social Links</legend>
        <label for="Website_or_Blog_URL">Website URL</label>
        <input type="text" class="half left" value="<?php echo $post["Website_or_Blog_URL"]["value"];?>" placeholder="Website URL" disabled="disabled"/>
        <label for="Facebook_Username">Facebook Username</label>
        <input type="text" class="half" value="<?php echo $post["Facebook_Username"]["value"];?>" placeholder="Facebook Username" disabled="disabled" />
        <label for="Twitter_Username">Twitter Handle</label>
        <input type="text" value="<?php echo $post["Twitter_Username"]["value"];?>" placeholder="Twitter Handle" disabled="disabled" class="half left bottom"/>
        <label for="LinkedIn_Username">LinkedIn Email</label>
        <input type="text" value="<?php echo $post["LinkedIn_Username"]["value"];?>" placeholder="LinkedIn ID" disabled="disabled" class="half bottom" />
    </fieldset>
    <fieldset>
        <legend>Company Information &amp; Size</legend>
        <label for="Business_Type">Business Type</label>
        <input type="text" class="half left" value="<?php echo $post["Business_Type"]["value"];?>" placeholder="Business Type" disabled="disabled" />
        <label for="Year_Founded">Founded</label>
        <input type="text" class="half" value="<?php echo $post["Year_Founded"]["value"];?>" placeholder="Founded" disabled="disabled"/>
        <label for="Business_Size">Size</label>
        <input type="text" class="half left bottom" value="<?php echo $post["Business_Size"]["value"];?>" placeholder="Size" disabled="disabled"/>
        <label for="Industry">Industry</label>
        <input type="text" class="half bottom" value="<?php echo $post["Industry"]["value"];?>" placeholder="Industry" disabled="disabled"/>
    </fieldset>

    <fieldset>
        <legend>About The Company</legend>
        <label for="Company_Bio">About The Company</label>
        <textarea><?php echo $post["Company_Bio"]["value"];?></textarea>
    </fieldset>
</form>
<a href="/profile/edit" class="btn green">Edit Profile</a>
</div>