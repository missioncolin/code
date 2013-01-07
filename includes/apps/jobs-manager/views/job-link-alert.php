<?php

global $quipp;
global $user;

require dirname(__DIR__) . '/JobManager.php';

$j = new JobManager($db, $_SESSION['userID']);
$quipp->js['footer'][] = "/includes/apps/jobs-manager/js/jobs-manager.js";

if ($user->info['Job Credits'] == 0) { $buyLink = "<a href=\"/buy-job-credits\">Buy Credits Now</a>";}else{$buyLink = "";}
echo alert_box('<h2>Tips</h2><p>Job links are active for 60 days</p><p>Cut and paste this into your job posting on any site</p><p>Your job link will be available in the My Jobs page</p><p>To learn how to incorporate your job <a href="">Click here</a></p> '.$user->info['Job Credits'].' credits. ' . $buyLink, 3);
?>

<div class="colASplit">
	<table class="simpleTable">
		<thead>
			<tr>
				<th>Your link is active!</th>
			<tr>		
		</thead>
		<tbody>
			<tr>
				<td>Paste this link into your job ad: </td>
			</tr>
			<tr>
				<td><span class="copyAndPaste"><?php echo $_SERVER['SERVER_NAME']."/apply/".$_GET['jobID'];?></span></td>
			</tr>
			<tr>
				<td><div class="successAlert"></div>
				<?php
				if($user->info['Job Credits'] > 0){
					//echo "<a href=\"\" data-job=\"".$_GET['jobID']."\" data-expiry=\"".$job["dateExpires"]."\" class=\"btn activate grey\">Use 1 Credit to Activate Now</a>";
					echo "<a href=\"\" data-job=\"".$_GET['jobID']."\" class=\"btn reactivateLanding grey\">Use 1 Credit to Activate Now</a>";
				}else{
					echo "<a href=\"buy-job-credits&redirect=".$_GET['jobID']."\" class=\"btn red\">Buy Credits to Activate Job</a>"; 
				}
				?>
				
				<a href="applications" class="btn ">Return to Job List</a></td>
			</tr>
		</tbody>
	</table>
</div>

<div id="confirm" style="display:none">
<div class="popUp">
<h2></h2>
<p></p>
<a class="btn" id="popUpOk">Ok</a>&nbsp;<a class="btn red" id="popUpNo">Cancel</a>
</div>
</div>