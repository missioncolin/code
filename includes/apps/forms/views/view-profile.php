<?php
$groups = array();
if ($this INSTANCEOF Quipp && isset($_SESSION['userID'])){
    require_once(dirname(__DIR__) ."/Forms.php");
    $profile = false;
    $frms = new Forms($db,$_SESSION['userID']);
    $groups = $frms->getUserGroups();
    
    $hrManager = false;
    if (isset($groups[1]) && in_array('hr-managers', $groups[1])){
        $hrManager = true;
    }
}
if ($hrManager === true){
?>
<a href="/create-job&step=1" class="createAJobCallout">Create A Job Today!</a>
<?php
}
?>
<section id="profileEdit">
<div id="form">
<?php
if (!empty($groups)){
    require_once(dirname(__DIR__) ."/Forms.php");
    $profile = false;
    $frms = new Forms($db,$_SESSION['userID']);
    $groups = $frms->getUserGroups();
    if (!empty($groups)){
        foreach($groups as $group){
            if ($group["nameSystem"] == 'applicants'){
                include_once(__DIR__ ."/applicant-profile-view.php");
                $profile = true;
                break;
            }
            if ($group["nameSystem"] == 'hr-managers'){
                include_once(__DIR__ ."/hr-profile-view.php");
                $profile = true;
                break;
            }
        }
    }
    if ($profile === false){
        
        echo '<p>You do not have permission to view this page</p>';
    }
    
}
?>
</div>
</section>

<div class="leftColumn">
    <?php
    if ($hrManager === true){
        ?>
	<article>
		<a href="/applications">
		<img src="/themes/Intervue/img/example2.png" alt="example2" width="388" height="236" />
		View Jobs</a>
	</article>
	<article>
		<a href="/questionnaires">
		<img src="/themes/Intervue/img/example.png" alt="example" width="390" height="236" />
		Create Questions</a>
	</article>
<?php
    }
?>
</div>