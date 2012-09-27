<?php
if (isset($post)){
?>
<h3>Applicant Information</h3>
<form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"];?>">
    <div>
        <label for="videoProfile">Your <strong>Profile Video</strong>
        </label> <br />
        		<table> <tr> <td>
        		<?php
        
	        		$video   = $db->return_specific_item('', 'tblVideos', 'filename', 0, "jobID='0' AND questionID='0' AND userID='" . (int)$_SESSION['userID'] . "' AND sysOpen='1' AND sysActive='1'") ;
                    $videoID = $db->return_specific_item('', 'tblVideos', 'itemID', 0, "jobID='0' AND questionID='0' AND userID='" . (int)$_SESSION['userID'] . "' AND sysOpen='1'") ;

                    if ($video !== 0) {
                    	
                    ?>
                        <h4> Current Profile Video </h4>	
                    	<embed src="/includes/apps/ams-media/flx/captureModule.swf" quality="high" bgcolor="#000000" width="450" height="330" name="captureModule" FlashVars="reviewFile=<?php echo $video; ?>" align="middle" allowScriptAccess="sameDomain" allowFullScreen="true" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflash" />

                    	
        		</td><td>	
                    	
                    <?php
                    } 
                    
                        if ($videoID == 0) {
                            $qry = sprintf("INSERT INTO tblVideos (userID, jobID, questionID, filename, sysDateInserted, sysDateLastMod) VALUES ('%d', '%d', '%d', '', NOW(), NOW())",
                                (int)$_SESSION['userID'],
                                0,
                                0);
                            $db->query($qry);
                            $videoID = $db->insert_id();
                        }
                        
                       echo "<h4> Update Profile Video </h4>";
                        //550 x 400
                        echo '<embed src="/includes/apps/ams-media/flx/captureModule.swf" quality="high" bgcolor="#000000" width="450" height="330" name="captureModule" FlashVars="itemID=' . $videoID . '&securityKey=' . md5("iLikeSalt" . $videoID) . '" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflash" />';

                    
                    ?>
        		</td></tr></table>
        
        <p>Your video profile is a short clip that all HR managers will see. It might include some general information about you.</p>
        
       
        
        
    
    
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
        <input type="password" id="password" name="password" class="half left bottom" placeholder="Password" <?php echo (!isset($_SESSION["userID"]) ? 'required="required"' : ''); ?>/>
        <label for="confirmPassword">Re-Type Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" class="half bottom" placeholder="Re-Type Password" <?php echo (!isset($_SESSION["userID"]) ? 'required="required"' : ''); ?>/>
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
<?php
}