<?php
/**
* This allows managers to send out the username/password for the doctors only section to specific subscribers.
* We can use the same table for
*/

include '../../../includes/init.php';
require '../../classes/Editor.php';

require '../auth/kinderSmiles.auth.php';

$meta['title'] = 'Doctors Only: Subscription Manager';
$meta['title_append'] = ' &bull; Quipp CMS';

$hasPermission = false;
if ($auth->has_permission("modifySubscriptions") && $auth->has_permission("editorCCOK")){
    $hasPermission = true;
}
if ($hasPermission) {

    $isLoggedIn = false;
    
    if (isset($_GET["action"]) && preg_match("%(view|send)%",$_GET["action"],$axn)){
        if (isset($_POST["username"]) && isset($_POST["password"])){
            $storedPword = $db->return_specific_item(false, 'sysUsers', 'userIDPassword', false, "userIDField = '".$db->escape(trim($_POST["username"]),true)."'");
            if ($storedPword !== false && md5(trim($_POST["password"])) == $storedPword){
                $isLoggedIn = true;
            }
        }
        if ($isLoggedIn === true){
            if ($axn[1] == "send"){
                 $sent = 1;
                 $message = "";
                 if (isset($_POST["sendEmail"])){
                    $headers  = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                    $headers .= 'From: CCOK at Kindersmiles <no-reply@kindersmiles.com>' . "\r\n";
                	       
                    $body = '<p>Greetings %NAME%,</p><p>You are currently registered with the Center for Cosmetic Orthodontics at Kindersmiles to view the Doctors Only section.
                    Below are the login requirements to access the content on this site:</p>';
                    $body .= '<p>Username: pro@kindersmiles.com</p>';   
                    $body .= '<p>Password: '.trim($_POST["password"]).'</p>';   
                	$body .= '<p>&nbsp</p>';
                	$body .= '<p>Please protect this username and password. If you no longer wish to use this service, <a href="http://'.$_SERVER["SERVER_NAME"].'/unsubscribe?user=%EMAIL%&req='.md5('onthefence').'">use this link to unsubscribe</a></p>';
                    $body .= '<p>-------<br>Sent from '.$_SERVER["SERVER_NAME"].'</p>';
                		
                	$content = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/apps/contact-form/views/referCCOKEmail.php");               	
                	
                    foreach($_POST["sendEmail"] as $address => $sName){
                        $pContent = $content;
                        $body = str_replace('%NAME%', $sName, $body);
                        $body = str_replace('%EMAIL%', urlencode($address), $body);
                        try{
                    	   
                    	   $pContent = str_ireplace('%BODY%',$body,$pContent);
                    	   $pContent = str_ireplace('%SERVERNAME%',$_SERVER["SERVER_NAME"],$pContent);
                    	   $pContent = str_ireplace('%TITLE%','Doctors Only Registration',$pContent);
                    	
                    	   mail($address, 'Registration Update: Center for Cosmetic Orthodontics at Kindersmiles', $pContent, $headers);
                        }
                        catch(Exception $e){
                            $sent = 0;
                            $message .= $sendEmail;
                        } 
                    
                    }
                 }
                 if ($sent == 1){
                    header("location:".$_SERVER["PHP_SELF"]."?sent=1");
                 }
                 else{
                    $_GET["action"] = "view";
                    require_once($_SERVER["DOCUMENT_ROOT"]."/includes/lib/MCAPI.class.php");
                    require_once($_SERVER["DOCUMENT_ROOT"]."/includes/lib/MCconfig.inc.php");
                    $mc = new MCAPI($mcAPI);
                 }
            }
            else if ($axn[1] == "view"){
                require_once($_SERVER["DOCUMENT_ROOT"]."/includes/lib/MCAPI.class.php");
                require_once($_SERVER["DOCUMENT_ROOT"]."/includes/lib/MCconfig.inc.php");
                $mc = new MCAPI($mcAPI);
            }
        }
    }

    include "../../templates/header.php";
?>
<h1>Doctors Only: Subscription Manager</h1>
<p>This allows the ability to send all or specific users credentials to the restricted section of the website.</p>
<p>Users can only be subscribed or unsubscribed via <a href="https://login.mailchimp.com" target="_blank">MailChimp</a>.</p>

<div class="boxStyle">
	<div class="boxStyleContent">
		<div class="boxStyleHeading">
			<h2>Subscribers</h2>
		</div>
		<div class="clearfix">&nbsp;</div>
		<div id="template">
<?php
        if (isset($_GET["action"]) == "view" && $isLoggedIn === true){
        
            if (isset($sent) && $sent == 0 && $message != ""){
                echo alert_box("The following emails were not sent: ".str_replace(",","<br />",$message), 2);
            }
                
            $lists = $mc->lists(array("list_name"=>"Kindersmiles Subscribers"));
            
            if (isset($lists["total"]) && $lists["total"] == 1){
                //get the lists
                $mcLID = $lists["data"][0]["id"];
                //get all members
                $members = $mc->listMembers($mcLID, 'subscribed');
                if (is_array($members)){
                    foreach($members["data"] as $info){
                        //get member info by email
                        $mInfo = $mc->listMemberInfo($mcLID,array($info["email"]));
                        if (!$mc->errorCode){
                            //see if they belong to doctors only section
                            if (isset($mInfo["data"][0]["merges"]["GROUPINGS"])){
                                foreach($mInfo["data"][0]["merges"]["GROUPINGS"] as $groups){
                                    if (strstr($groups["groups"], "Doctors Only") !== false){
                                        
                                        $name = $mInfo["data"][0]["merges"]["LNAME"].", ".$mInfo["data"][0]["merges"]["FNAME"];
                                        
                                        $mcUser[] = array(
                                            "email"         => $info["email"],
                                            "timestamp"     => strtotime($info["timestamp"]),
                                            "fname"         => $mInfo["data"][0]["merges"]["FNAME"],
                                            "lname"         => $mInfo["data"][0]["merges"]["LNAME"]
                                        );
                                    }
                                }
                            }
                            
                        }
                    }
                }
            }
            if (isset($mcUser)){
                rsort($mcUser);
?>
            <form action="<?php echo $_SERVER["PHP_SELF"]."?action=send";?>" id="subSend" name="subSend" method="post">
            <table id="adminTableList" class="adminTableList tablesorter" width="100%" cellpadding="5" cellspacing="0" border="1">
            <thead>
            <tr><th>User Name</th><th>Email</th><th>Date Subscribed</th><th>Send Credentials</th></tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="3" style="border-right:0px">&nbsp;</td>
                <td style="border-left:0px">
                <input type="checkbox" name="selectAll" id="selectAll" value="all" />&nbsp;Select All
                <input type="hidden" name="username" id="username" value="<?php echo trim($_POST["username"]);?>" />
                <input type="hidden" name="password" id="password" value="<?php echo trim($_POST["password"]);?>" />
                </td>
            </tr>
            </tbody>
            <tbody>
<?php
            $u = 0;
            foreach ($mcUser as $subscriber){
                echo '<tr>';
                echo '<td>'.$subscriber["lname"].', '.$subscriber["fname"].'</td><td>'.$subscriber["email"].'</td><td>'.date("Y-m-d",$subscriber["timestamp"]).'</td>';
                echo '<td><input type="checkbox" name="sendEmail['.$subscriber["email"].']" id="sendEmail_'.$u.'" value="Dr '.$subscriber["lname"].'" /></td>';
                echo '</tr>';
                $u++;
            }
?>
            </tbody>
            <tbody>
            <tr>
                <td colspan="3" style="border-right:0px">&nbsp;</td>
                <td style="border-left:0px"><input type="submit" name="send" id="send" value="Send Email" class="btnStyle blue" /></td>
            </tr>
            </tbody>
            </table>
            </form>
<?php
                $quipp->js['footer'][] = "/admin/apps/doctors-subscriptions/js/doctors-subscriptions.js";
            }
            else{
                echo '<p>There are currently no subscribers<p>';
            }
        }
        else{
            if (isset($_GET["sent"])){
                echo "<h4>An email was sent to all selected users</h4>";
            }
?>
            <p>All passwords are securely stored, so you must "login" to access your subscriber list and send the credentials</p>
            <p>&nbsp;</p>
            <form action="<?php echo $_SERVER["PHP_SELF"]."?action=view";?>" id="subLogin" name="subLogin" method="post">
            <table id="adminTableList" class="adminTableList tablesorter" width="100%" cellpadding="5" cellspacing="0" border="1">
            <thead>
            <tr><th>User Name</th><th>Password</th><th>&nbsp;</th></tr>
            </thead>
            <tbody>
            <tr>
                <td><input type="text" name="username" id="username" value="" class="uniform" /></td>
                <td><input type="password" name="password" id="password" value="" class="uniform" /></td>
                <td><input type="submit" name="login" id="submit" value="Submit" class="btnStyle green" /></td>
            </tr>
            </tbody>
            </table>
            </form>
<?php
        }
?>
		
		</div>
		<div class="clearfix">&nbsp;</div>
	</div>
</div>

<?php
    include "../../templates/footer.php";

}
else{
    echo 'no permission';

}