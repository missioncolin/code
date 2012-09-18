<?php 
ini_set('display_errors', 'off');
if ($this INSTANCEOF Quipp){
?>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<h3>Job Application</h3>
<form id="job-form" method="post" action="<?php print $_SERVER['REQUEST_URI']; ?>">
	<table>
	<?php 
		$getQuestionsQS = sprintf("SELECT * FROM tblQuestions WHERE questionnaireID = '%d' AND sysOpen = '1'" , $_REQUEST['qnrID']);
//		yell('print', $getQuestionsQS);
		$getQuestionsQry = $db->query($getQuestionsQS);
		if($db->valid($getQuestionsQry)){
			if($db->num_rows($getQuestionsQry) > 0){
				while($qsn = $db->fetch_array($getQuestionsQry)){
					print "<tr>";
					print "<td>";
					print $qsn['label'];
					print "</td>";
					print "</tr>";
					print "<tr>";
					print "<td>";
					switch($qsn['type']){
						case 1://radio
						
							$getOptionsQS = sprintf("SELECT * FROM tblOptions WHERE questionID = '%d' AND sysOpen = '1'", $qsn['itemID']);
							$getOptionsQry = $db->query($getOptionsQS);
							if($db->valid($getOptionsQry)){
								if($db->num_rows($getOptionsQry) > 0){
									print "<ul>";
									while($opt = $db->fetch_array($getOptionsQry)){
										$id = $qsn['itemID'] ."_". $opt['itemID'];
										$name = $qsn['itemID'];
										print "<li>";
										print "<input type='radio' id='".$id."'  name='".$name."'  value='".$opt['itemID']."' /><label for='".$id."'>".$opt['label']."</label>";
										print "</li>";
									}
									print "</ul>";
								}else{
									print "No options available currently.";
								}
							}else{
								print "Error retrieving options.";
							}
						break;
						case 2://checkbox
							
							$getOptionsQS = sprintf("SELECT * FROM tblOptions WHERE questionID = '%d' AND sysOpen = '1'", $qsn['itemID']);
							$getOptionsQry = $db->query($getOptionsQS);
							if($db->valid($getOptionsQry)){
								if($db->num_rows($getOptionsQry) > 0){
									print "<ul>";
									while($opt = $db->fetch_array($getOptionsQry)){
										$id = $qsn['itemID'] ."_". $opt['itemID'];
										$name = $qsn['itemID'];
										print "<li>";
										print "<input type='checkbox' id='".$id."'  name='".$name."[]'  value='".$opt['itemID']."' /><label for='".$id."'>".$opt['label']."</label>";
										print "</li>";
									}
									print "</ul>";
								}else{
									print "No options available currently.";
								}
							}else{
								print "Error retrieving options.";
							}
						
						break;
						case 3://slider
							$name = $qsn['itemID'];
							$id = $name;
							$val = 0;
							print "<div class=\"slider\" rel=\"$name\" alt='".$val."'></div><input type=\"hidden\" name=\"$name\" id=\"$id\" value=\"".$val."\" /><div class='sliderValueHolder' rel='$id'>".$val."/20</div>";
						
						break;
						case 4://video
						
						case 5://file
						
						break;
						
					}
					print "</td>";
					print "</tr>";
				}
			}else{
				$feedback = "This questionnaire has no questions.";
			}
		}else{
			$feedback = "This questionnaire is not valid.";
		}
		
	?>
	</table>
</form>
<?php 
global $quipp;
	$quipp->js['footer'][] = "/js/jquery-ui-1.8.6.min.js";
	$quipp->js['footer'][] = "/includes/apps/questionnaires/js/questionnaires.js";
}