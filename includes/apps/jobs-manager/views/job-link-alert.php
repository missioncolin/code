<?php

global $quipp;
global $user;

require dirname(__DIR__) . '/JobManager.php';

if (isset($_SESSION['newReg'])) {

	unset($_SESSION['newReg']);
}

$j = new JobManager($db, $_SESSION['userID']);
$quipp->js['footer'][] = "/includes/apps/jobs-manager/js/jobs-manager.js";

?>
   
<!-- New breadcrumb setup: If creating a new user, and moving to create a job, display this: --->
<!--         <ul id="stepsNew"<?php if (!isset($_GET['step'])) { ?> class="hide"<?php } ?>> -->

 <!---- Handles breadcrumb for newly registered users ---->
<ul id="stepsNew"<?php if ($j->totalJobs() > 1) { ?> class="hide"<?php } ?>>
    <?php if (isset($_GET['step']) || in_array($_GET['p'], $signUpPages)) { ?>
    <li><span>1</span>Create Account</li>
    <li<?php if (isset($_GET['step']) && $_GET['step'] == '1') { ?> class="current"<?php } ?>><span>2</span>Name Your Job</li>
    <li<?php if (isset($_GET['step']) && $_GET['step'] == '2') { ?> class="current"<?php } ?>><span>3</span>Add Required Skills and Experience</li>
    <li<?php if (isset($_GET['step']) && $_GET['step'] == '3') { ?> class="current"<?php } ?>><span>4</span>Add intervue Questions</li>
    <li<?php if (isset($_GET['step']) && $_GET['step'] == '4') { ?> class="current"<?php } ?>><span>5</span>Activate Link</li>
    <?php } ?>
</ul>


<!--   If just creating a new job, use this: -->
<ul id="steps"<?php if (($j->totalJobs() == 1)) { ?> class="hide"<?php } ?>>
    <?php if (isset($_GET['step'])) { ?>
    <li<?php if ($_GET['step'] == '1') { ?> class="current"<?php } ?>><span>1</span>Name Your Job</li>
    <li<?php if ($_GET['step'] == '2') { ?> class="current"<?php } ?>><span>2</span>Add Required Skills and Experience</li>
    <li<?php if ($_GET['step'] == '3') { ?> class="current"<?php } ?>><span>3</span>Add intervue Questions</li>
    <li<?php if ($_GET['step'] == '4') { ?> class="current"<?php } ?>><span>4</span>Activate Link</li>
    <?php } ?>
</ul>
	
<?php

if ($user->info['Job Credits'] == 0){
	$title = "Buy Credits";
	$jobCreditsLine = "<a href=\"#\" class=\"btn red\">To activate your link you must buy credits</a>";
	$buttonLink = "<a href=\"/buy-job-credits?redirect=" . $_GET['jobID'] . "\" class=\"btn green\" target=\"_blank\">Buy Credits</a>";
}else if ($user->info['Job Credits'] > 0){
	$title = "Activate Your Link";
	$creditNote = '<tr><td><div class="btn">Activate your link to make it live</div></td><tr>';
	$jobCreditsLine = "<strong>You have " . $user->info['Job Credits'] . " credits</strong>";
	$buttonLink = "<a href=\"\" class=\"btn activate green\" data-job=\"".$_GET["jobID"]."\">Activate Link</a>";
} 



if ($user->info['Job Credits'] == 0) { $buyLink = "<a href=\"/buy-job-credits?redirect=" . $_GET['jobID'] . "\">Buy Credits Now</a>";}else{$buyLink = "";}
echo alert_box('<h2>Tips</h2><p>Job links are active for 60 days</p><p>Cut and paste this into your job posting on any site</p><p>Your job link will be available in the My Jobs page</p><p>To learn how to incorporate your job <a href="/how-it-works">Click here</a></p> '.$user->info['Job Credits'].' credits. ' . $buyLink, 3);
?>

<!--options table--> 
<div class="colASplit">
	<div class="optionsTable">
		<table class="simpleTable">
			<thead>
				<tr>
					<th><?php echo $title; ?></th>
				<tr>		
			</thead>
			<tbody>
				<tr>
					<?php echo ($user->info['Job Credits'] > 0 ) ? $creditNote : ''; ?>
					<td><?php echo $jobCreditsLine; ?></td>
				</tr>
				<tr>
					<td><div class="successAlert"></div>
					<?php
						echo $buttonLink;
					?>
					&nbsp;<strong>OR</strong>&nbsp; 
					<a href="applications" class="btn ">Go to my jobs</a></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<!--Link presentation table--> 
<div class="colASplit">
	<div class="activeTable" style="display: none;">
		<table class="simpleTable">
			<thead>
				<tr>
					<th colspan="3">Your Link is Active</th>
				<tr>		
			</thead>
			<tbody>
				<tr>
					<td width="33%"></td>
					<td width="34%"><span class="btn green">Your Link Is Active</span></td>
					<td width="33%">Copy and paste this link into your external job posting</td>
				</tr>
				<tr>
					<td colspan="3"><span class="yourLink">http://<?php echo $_SERVER['SERVER_NAME']."/apply/".$_GET['jobID']; ?></span></td>
				</tr>
				<tr>
					<td colspan="3"><a href="applications" class="btn ">Go to my jobs</a></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>



<div id="confirm" style="display:none">
	<div class="popUp">
		<h2></h2>
		<p></p>
		<a class="btn green" id="popUpOk">Ok</a>&nbsp;<a class="btn red" id="popUpNo">Cancel</a>
	</div>
</div>



<?php 
/*
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
*/
?>