<?php

global $quipp;
global $user;

require dirname(__DIR__) . '/JobManager.php';

$j = new JobManager($db, $_SESSION['userID']);
$quipp->js['footer'][] = "/includes/apps/jobs-manager/js/jobs-manager.js";

if ($user->info['Job Credits'] == 0) { $buyLink = "<a href=\"/buy-job-credits\">Buy Credits Now</a>";}else{$buyLink = "";}
echo alert_box('Your job has been created. Before you can collect applicant information, the job must be activated. One (1) credit will be deducted from your account. You currently have '.$user->info['Job Credits'].' credits. ' . $buyLink, 3);
?>


<br/> <br/>
Paste this link into your job ad: 
<br/>
<span style="font-weight:bolder; font-size:larger;"><?php echo $_SERVER['SERVER_NAME']."/apply/".$_GET['jobID'];?></span>

<br/>
<div class="successAlert"></div>
<br/>
<?php
if($user->info['Job Credits'] > 0){
	//echo "<a href=\"\" data-job=\"".$_GET['jobID']."\" data-expiry=\"".$job["dateExpires"]."\" class=\"btn activate grey\">Use 1 Credit to Activate Now</a>";
	echo "<a href=\"\" data-job=\"".$_GET['jobID']."\" class=\"btn reactivateLanding grey\">Use 1 Credit to Activate Now</a>";
}else{
	echo "<a href=\"buy-job-credits&redirect=".$_GET['jobID']."\" class=\"btn red\">Buy Credits to Activate Job</a>"; 
}
?>

<a href="applications" class="btn ">Return to Job List</a> 


<div id="confirm" style="display:none">
<div class="popUp">
<h2></h2>
<p></p>
<a class="btn" id="popUpOk">Ok</a>&nbsp;<a class="btn red" id="popUpNo">Cancel</a>
</div>
</div>