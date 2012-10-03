<?php
if (isset($post)){
?>
<h3>Applicant Information</h3>


<?php
        if (!empty($message)){
            echo '<div class="error">';
            echo "Your account was not updated. The following error(s) occurred: <ul>".$message."</ul>";
            echo '</div>';
        }
?>

<form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"];?>">
    <fieldset>
        <legend>Account Details</legend>

        <label for="First_Name">First Name</label>
        <input type="text" id="First_Name" name="First_Name" class="full" placeholder="First Name" value="<?php echo $post["First_Name"]["value"];?>" required="required"/>
        <label for="Last_Name">Last Name</label>
        <input type="text" id="Last_Name" name="Last_Name" class="full" placeholder="Last Name" value="<?php echo $post["Last_Name"]["value"];?>" required="required"/>
        <label for="Email">Email Address</label>
        <input type="text" id="Email" name="Email" class="full" placeholder="Email Address" value="<?php echo $post["Email"]["value"];?>" required="required"/>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="half left bottom" placeholder="Password" <?php echo (!isset($_SESSION["userID"]) ? 'required="required"' : ''); ?>/>
        <label for="confirmPassword">Re-Type Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" class="half bottom" placeholder="Re-Type Password" <?php echo (!isset($_SESSION["userID"]) ? 'required="required"' : ''); ?>/>
    </fieldset>

    <fieldset>
        <legend>Website &amp; Social Links</legend>
        <label for="Website_or_Blog_URL">Website</label>
        <input type="text" id="Website_or_Blog_URL" name="Website_or_Blog_URL" class="half left" placeholder="Website URL" value="<?php echo $post["Website_or_Blog_URL"]["value"];?>"/>
        <label for="Facebook_Username">Facebook</label>
        <input type="text" id="Facebook_Username" name="Facebook_Username" class="half" placeholder="Facebook Username" value="<?php echo $post["Facebook_Username"]["value"];?>"/>
        <label for="Twitter_Username">Twitter</label>
        <input type="text" id="Twitter_Username" name="Twitter_Username" class="half left bottom" placeholder="Twitter Handle" value="<?php echo $post["Twitter_Username"]["value"];?>"/>
        <label for="LinkedIn_Username">LinkedIn</label>
        <input type="text" id="LinkedIn_Username" name="LinkedIn_Username" class="half bottom" placeholder="LinkedIn ID" value="<?php echo $post["LinkedIn_Username"]["value"];?>"/>
    </fieldset>

    <input type="submit" value="Submit" class="btn" name="sbmt-ap-signup" />
</form>
<?php
}