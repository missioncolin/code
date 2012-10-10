<?php
$meta   = $frms->getMetaFieldsByGroup('applicants');
$post   = array();
foreach($meta as $fields){
    $post[str_replace(" ","_",$fields["fieldLabel"])] = array("value" => $frms->get_meta($fields["fieldLabel"]));
}
$grav_url = "http://www.gravatar.com/avatar/" . md5(strtolower(trim($post["Email"]["value"]))) . "?d=" . urlencode("http://".$_SERVER["SERVER_NAME"]."/themes/Intervue/img/profilePicExample.jpg") . "&s=80";
?>
<h3 id="applicantProfileEdit"><div class="imgWrap"><img src="<?php echo $grav_url;?>" alt="avatar" width="80px" height="80px" /></div><?php echo $post["First_Name"]["value"]." ".$post["Last_Name"]["value"];?><a href="/profile/edit">Edit Profile</a></h3>
<form>
    <fieldset>
        <legend>Username</legend>
        <input type="text" class="full" placeholder="System Login" value="<?php echo $post["Email"]["value"];?>" disabled="disabled"/>
    </fieldset>
    <fieldset>
        <legend>Account Details</legend>
        <label for="First_Name">First Name</label>
        <input type="text" class="full" value="<?php echo $post["First_Name"]["value"];?>" disabled="disabled" />
        <label for="Last_Name">Last Name</label>
        <input type="text" class="full" value="<?php echo $post["Last_Name"]["value"];?>" disabled="disabled"/>
        <label for="Email">Email Address / Login</label>
        <input type="text" class="full" placeholder="Email Address" value="<?php echo $post["Email"]["value"];?>" disabled="disabled"/>
    </fieldset>

    <fieldset>
        <legend>Website &amp; Social Links</legend>
        <label for="Website_or_Blog_URL">Website</label>
        <input type="text" class="half left" placeholder="Website URL" value="<?php echo $post["Website_or_Blog_URL"]["value"];?>" disabled="disabled"/>
        <label for="Facebook_Username">Facebook</label>
        <input type="text" class="half" placeholder="Facebook Username" value="<?php echo $post["Facebook_Username"]["value"];?>" disabled="disabled"/>
        <label for="Twitter_Username">Twitter</label>
        <input type="text" class="half left bottom" placeholder="Twitter Handle" value="<?php echo $post["Twitter_Username"]["value"];?>" disabled="disabled"/>
        <label for="LinkedIn_Username">LinkedIn</label>
        <input type="text" class="half bottom" placeholder="LinkedIn ID" value="<?php echo $post["LinkedIn_Username"]["value"];?>" disabled="disabled"/>
    </fieldset>
</form>