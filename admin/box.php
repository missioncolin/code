<?php

require '../includes/init.php';
require 'classes/Content.php';
//yell($_SERVER['DOCUMENT_ROOT']);

$regionID  = (isset($_GET['regionID'])) ? $_GET['regionID'] : 0;
$pageID    = (isset($_GET['pageID'])) ? $_GET['pageID'] : 0;
$contentID = (isset($_GET['contentID'])) ? $_GET['contentID'] : 0;

$boxTitle = "";
$boxContent = "";
$pb = 'NewContent';
$box 		= new Content;
$hideTitle = '';

if ($contentID != 0) {
	
	$rs 		= $box->get_content($contentID);
	$boxTitle 	= $rs['adminTitle'];
	$boxContent = $rs['htmlContent'];
	$boxStyle	= $rs['divBoxStyle'];
	$hideTitle  = ($rs['divHideTitle'] == '1') ? ' checked="checked"' : '';
	$pb = 'UpdateContent';
} 

if (isset($_POST['pb']) && $_POST['pb'] == sha1("UpdateContent")) {

	$box->insert_content('update', $_POST['contentID']);

} else if (isset($_POST['pb']) && $_POST['pb'] == sha1("NewContent")) {

	$box->insert_content('new', $_POST['contentID']);
	
} else {
 	
 	array_push($quipp->js['footer'], '/js/tinymce/jscripts/tiny_mce/jquery.tinymce.js', '/js/tinymce/jscripts/tiny_mce/plugins/tinybrowser/tb_tinymce.js.php');
	$quipp->js['onload'] .= '$("#boxBodyContent").css("height", "480px");';
	$quipp->js['onload'] .=  tinyMCE('boxBodyContent', 'common');
	
	require 'templates/headerLight.php';
?>


	<div id="boxEditorWrapper" style="padding:5px;">
		<form id="boxContentForm" name="boxContentForm" method="post" enctype="multipart/form-data" action="<?php print $_SERVER['PHP_SELF']; ?>">
			<div style="padding-bottom:3px;">
				<input type="text" id="boxTitle" name="boxTitle" value="<?php print $boxTitle;  ?>" style="width:400px;" /> <input type="checkbox" name="hideTitle" id="hideTitle" value="1" <?php print $hideTitle; ?> /> <label for="hideTitle">Hide?</label>
				
	 			<div style="float:right; margin-top: -10px;">
		 			<table>
		 				<tr><td>Box Style</td><td><select name="boxStyle" id="boxStyle"><option value="blank">Blank</option><option value="expandable">Expand/Collapse</option><option value="Boxy">Boxy</option></select></td><td><img id="loader" src="/images/admin/ajax-loader.gif" style="display:none;" /><input type="button" value="Save" id="saveBtn" name="saveBtn" class="btnStyle green"/></td></tr>
		 			</table>
				</div>
				
			</div>
			<div>
				<textarea id="boxBodyContent" name="boxBodyContent"><?php print $boxContent;  ?></textarea>
			</div>
				
			<input type="hidden" id="contentID" name="contentID" value="<?php print $contentID;  ?>" />
			<input type="hidden" id="regionID" name="regionID" value="<?php print $regionID;  ?>" />
			<input type="hidden" id="pageID" name="pageID" value="<?php print $pageID;  ?>" />
			<input type="hidden" id="pb" name="pb" value="<?php print sha1($pb);  ?>" />
		</form>
	</div>



<?php 

	require 'templates/footerLight.php';  
} 

?>