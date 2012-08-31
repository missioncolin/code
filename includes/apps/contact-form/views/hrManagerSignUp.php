<?php 
ini_set('display_errors', 'off');
if ($this INSTANCEOF Quipp){
global $message;
 
if(!isset($$_REQUEST['action'])) $_REQUEST['action'] = "new";
if (!empty($_POST) && isset($_POST["hr-sign-up"]) ) {
	if(validate_form($_POST)){
 
		switch($_REQUEST['action']){
		
			case 'new':
				$qry = sprintf("INSERT INTO tblCompanies
								(
									name,
									address,
									city,
									postalCode,
									provinceID,
									countryID,
									sysDateInserted,
									sysDateLastMod
								)VALUES(
									'%s',
									'%s',
									'%s',
									'%s',
									'%d',
									'%d',
									NOW(),
									NOW()
								)",
								
									$_REQUEST['RQvalALPHCompany_Name'],
									$_REQUEST['RQvalALPHAddress'],
									$_REQUEST['RQvalALPHCity'],
									$_REQUEST['RQvalPOSTPostal_Code'],
									$_REQUEST['RQvalNUMBProvince'],
									$_REQUEST['RQvalNUMBCountry']
									
							);
									
					$db->query($qry);
					//yell('print', $qry);
					
					$qry = sprintf("INSERT INTO tblHRManagers
								(
									firstName,
									lastName,
									email,
									password,
									linkedinUsername,
									twitterUsername,
									facebookUsername,
									website,
									companyID,
									sysDateInserted,
									sysDateLastMod
								)VALUES(
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%d',
									NOW(),
									NOW()
								)",
								
									$_REQUEST['RQvalALPHFirst_Name'],
									$_REQUEST['RQvalALPHLast_Name'],
									$_REQUEST['RQvalMAILEmail_Address'],
									md5($_REQUEST['RQvalALPHPassword']),
									$_REQUEST['OPvalALPHLinkedIn_Username'],
									$_REQUEST['OPvalALPHTwitter_Username'],
									$_REQUEST['OPvalALPHFacebook_Username'],
									$_REQUEST['OPvalWEBSWebsite'],
									$db->insert_id()
									
							);
									
					$db->query($qry);
					//yell('print', $qry);
					$registrationComplete = 1;
									
			break;
			case 'edit':
			
			break;
		}

	
    
    }else{
        $error_message = "Error: Please review the following fields:<ul>$message</ul>";
    }
}


		if($registrationComplete == 1){
			print alert_box("<strong>Success!</strong> You have completed registration as an <strong>HR Manager</strong>!", 1);
		}else if (isset($error_message) && $error_message != '') {
			print alert_box($error_message, 2);

		}
	
	?>
	<form action="<?php print $_SERVER['REQUEST_URI']; ?>" method="post">
		<table>
			<tr><th colspan="2">Personal Information</th><!-- <th></th> --></tr>
			<tr>
				<td><label for="RQvalALPHFirst_Name" class="req">First Name</label></td>
				<td><input class="med" type="text" name="RQvalALPHFirst_Name" id="RQvalALPHFirst_Name"  value = "<?php echo $_REQUEST["RQvalALPHFirst_Name"];?>" /></td>
			</tr>
            <tr>
				<td><label for="RQvalALPHLast_Name" class="req">Last Name</label></td>
				<td><input class="med" type="text" name="RQvalALPHLast_Name" id="RQvalALPHLast_Name"  value = "<?php echo $_REQUEST["RQvalALPHLast_Name"];?>" /></td>
			</tr>
			<tr>
				<td><label for="RQvalMAILEmail_Address" class="req">Email Address</label></td>
				<td><input class="med" type="email" name="RQvalMAILEmail_Address" id="RQvalMAILEmail_Address" value = "<?php echo $_REQUEST["RQvalMAILEmail_Address"];?>"/></td>
			</tr>
			<tr>
				<td><label for="RQvalALPHPassword" class="req">Password</label></td>
				<td><input class="small-med" type="password" name="RQvalALPHPassword" id="RQvalALPHPassword"  value = "<?php echo $_REQUEST["RQvalALPHPassword"];?>" /></td>
			</tr>
			<tr>
				<td><label for="RQvalALPHRe-type_Password" class="req">Re-Type Password</label></td>
				<td><input class="small-med" type="password" name="RQvalALPHRe-type_Password" id="RQvalALPHRe-type_Password"  value = "<?php echo $_REQUEST["RQvalALPHRe-type_Password"];?>" /></td>
			</tr>
			
			<tr>
				<td><label for="OPvalALPHFacebook_Username" >Facebook Username</label></td>
				<td><input class="med" type="text" name="OPvalALPHFacebook_Username" id="OPvalALPHFacebook_Username" value = "<?php echo $_REQUEST["OPvalALPHFacebook_Username"];?>"/></td>
			</tr>
			<tr>
				<td><label for="OPvalALPHTwitter_Username" >Twitter Username</label></td>
				<td><input class="med" type="text" name="OPvalALPHTwitter_Username" id="OPvalALPHTwitter_Username" value = "<?php echo $_REQUEST["OPvalALPHTwitter_Username"];?>"/></td>
			</tr>
			<tr>
				<td><label for="OPvalALPHLinkedIn_Username" >LinkedIn Username</label></td>
				<td><input class="med" type="text" name="OPvalALPHLinkedIn_Username" id="OPvalALPHLinkedIn_Username" value = "<?php echo $_REQUEST["OPvalALPHLinkedIn_Username"];?>"/></td>
			</tr>
			<tr>
				<td><label for="OPvalWEBSWebsite" >Website</label></td>
				<td><input class="large" type="text" name="OPvalWEBSWebsite" id="OPvalWEBSWebsite" value = "<?php echo $_REQUEST["OPvalWEBSWebsite"];?>"/></td>
			</tr>
			
			<tr><th colspan="2">Company Information</th><!-- <th></th> --></tr>
			
			<tr>
				<td><label for="RQvalALPHCompany_Name" class="req">Company Name</label></td>
				<td><input class="large" type="text" name="RQvalALPHCompany_Name" id="RQvalALPHCompany_Name"  value = "<?php echo $_REQUEST["RQvalALPHCompany_Name"];?>" /></td>
			</tr>
			<tr>
				<td><label for="RQvalALPHAddress" class="req">Address</label></td>
				<td><input class="med" type="text" name="RQvalALPHAddress" id="RQvalALPHAddress"  value = "<?php echo $_REQUEST["RQvalALPHAddress"];?>" /></td>
			</tr>
			<tr>
				<td><label for="RQvalALPHCity" class="req">City</label></td>
				<td><input class="med" type="text" name="RQvalALPHCity" id="RQvalALPHCity"  value = "<?php echo $_REQUEST["RQvalALPHCity"];?>" /></td>
			</tr>
			<tr>
				<td><label for="RQvalNUMBProvince" class="req">Province / State</label></td>
				<td><?php  print get_prov_list("RQvalNUMBProvince", $_REQUEST['RQvalNUMBProvince'], $default = 9, $extraParam = ""); ?></td>
			</tr>
			<tr>
				<td><label for="RQvalNUMBCountry" class="req">Country</label></td>
				<td><?php print get_country_list("RQvalNUMBCountry", $_REQUEST['RQvalNUMBCountry'], $default = 38, $extraParam = ""); ?></td>
			</tr>
			<tr>
				<td><label for="RQvalPOSTPostal_Code" class="req">Postal Code / Zip</label></td>
				<td><input class="small-med" type="text" name="RQvalPOSTPostal_Code" id="RQvalPOSTPostal_Code"  value = "<?php echo $_REQUEST["RQvalPOSTPostal_Code"];?>" /></td>
			</tr>
			

			<tr>
				<td></td><td colspan="2"><div class="submitWrap"><input type="submit" value="Register" name="hr-sign-up" class="btnStyle" /></div></td>
			</tr>
		</table>
		
		<input type="hidden" name="action" id="action" value="<?php print $_POST['action']; ?>" />
	</form>
<?php
}