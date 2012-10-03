<a href="/create-job&step=1" class="createAJobCallout">Create A Job Today!</a>

<section id="profileEdit">
<div id="form">
<?php
if ($this INSTANCEOF Quipp && isset($_SESSION['userID'])){
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
	<article>
		<img src="/themes/Intervue/img/example.png" alt="example" width="390" height="236" />
		<a href="#">Create Questions</a>
	</article>
	<article>
		<img src="/themes/Intervue/img/example2.png" alt="example2" width="388" height="236" />
		<a href="#">View Jobs</a>
	</article>
</div>