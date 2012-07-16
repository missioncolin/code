<?php 
if ($this INSTANCEOF Quipp){
global $message;
$sent = 0;

if (!empty($_POST) && isset($_POST["sub-sign-up"]) && validate_form($_POST)) {
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/lib/MCAPI.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/includes/lib/MCconfig.inc.php");
    $mc = new MCAPI($mcAPI);
    $lists = $mc->lists(array("list_name"=>"Kindersmiles Subscribers"));
    
    $newsletter = "";
    
    if (isset($lists["total"]) && $lists["total"] == 1){
        $mcLID = $lists["data"][0]["id"];
        $groups = $mc->listInterestGroupings($mcLID);
        if ($groups[0]["name"] == "Newsletter"){
            $subs = $groups[0]["groups"];
            $groupID = $groups[0]["id"];
            foreach($subs as $intGroup){
                if ($intGroup["display_order"] == $this->siteID){
                    $newsletter = $intGroup["name"];
                }
            }
        }
    }
    if ($newsletter != ""){
        if ($mc->listSubscribe($mcLID, $db->escape(trim($_POST["RQvalMAILEmail_Address"]),true), array(
            'GROUPINGS' => array(array(
                'id'        => $groupID,
                'groups'    => $newsletter
            )),
            'FNAME'     => $db->escape(trim($_POST["RQvalALPHFirstName"]),true),
            'LNAME'     => $db->escape(trim($_POST["RQvalALPHLastName"]),true)
        
        ), 'html', false)){
            $sent = 1;
        }
        else{
            $message = "Error: Unable to register at this time";
            if ($mc->errorCode == 214){
                $message = "You are already subscribed to this newsletter";
            }
        }
    }
    else{
        $message = "Error: Unable to subscribe at this time";
    }
}

?>
<div class="blank" style="width:460px !important;">

	<?php
	    $post = array(
	       "RQvalALPHFirstName"     => "",
	       "RQvalALPHLastName"      => "",
	       "RQvalMAILEmail_Address" => ""
	    );
		if($sent == 1){
			print alert_box("<strong>Success!</strong> You are now subscribed to the newsletter.", 1);
		}else if (isset($message) && $message != '') {
			print alert_box($message, 2);
			foreach ($_POST as $key => $value){
			     $post[$key] = $value;
			}
		}
	
	?>
	<form action="<?php print $_SERVER['REQUEST_URI']; ?>" method="post">
		<table>
			<tr>
				<td style="width:120px"><label for="RQvalALPHFirstName" class="req">First Name</label></td>
				<td><input type="text" name="RQvalALPHFirstName" id="RQvalALPHFirstName"  value = "<?php echo $post["RQvalALPHFirstName"];?>" /></td>
			</tr>
            <tr>
				<td style="width:120px"><label for="RQvalALPHLastName" class="req">Last Name</label></td>
				<td><input type="text" name="RQvalALPHLastName" id="RQvalALPHLastName"  value = "<?php echo $post["RQvalALPHLastName"];?>" /></td>
			</tr>
			<tr>
				<td style="width:120px"><label for="RQvalMAILEmail_Address" class="req">Email Address</label></td>
				<td><input type="email" name="RQvalMAILEmail_Address" id="RQvalMAILEmail_Address" value = "<?php echo $post["RQvalMAILEmail_Address"];?>"/></td>
			</tr>

			<tr>
				<td colspan="2"><div class="submitWrap"><input type="submit" value="Sign Up" name="sub-sign-up" class="btnStyle blue" /></div></td>
			</tr>
		</table>
	</form>
</div>
<?php
}