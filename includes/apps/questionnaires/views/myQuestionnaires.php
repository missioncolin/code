<h4>My Questionnaires</h4>

<?php 
//fini_set('display_errors', 'off');
if ($this INSTANCEOF Quipp){
	
	$getQuestionnairesQS = sprintf("SELECT * FROM tblQuestionnaires WHERE hrUserID = '%d' AND sysOpen = '1' AND sysActive = '1'", $_SESSION['userID']);
	$getQuestionnairesQry = $db->query($getQuestionnairesQS);
	if(is_resource($getQuestionnairesQry)){
		if($db->num_rows($getQuestionnairesQry) > 0){
			print "<ul>";
			while($qnr = $db->fetch_assoc($getQuestionnairesQry)){
				print "<li><a href=\"".$_SERVER['REQUEST_URI']."?action=edit&qnrID=".$qnr['itemID']."\" >".$qnr['label']."</a></li>";
			}
			print "</ul>";
		}else{
			print "You haven't created any questionnaires. <a href=\"/questionnaires&action=new\">Click here</a> to create one.";
		}
	}
	
	print "<a href=\"/questionnaires&action=new\" class='btnStyle'>Create New</a>";
}