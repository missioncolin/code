<?php

class Editor
{


	function __construct($bucket = false)
	{


	}

	function commit_a_modify_action($qry, $actionLabel, $redirect = false, $extraParams = "")
	{
		global $db;

		$res = $db->query($qry);
		
		if (is_resource($res) || $res === true) {
			if ($redirect) { 
				header('Location:' . $_SERVER['PHP_SELF'] . '?' . $actionLabel . '=true' . $extraParams);
			} else {
				return $actionLabel . " worked!";
			}
		} else {
			return $actionLabel . " did not work<br />" . $db->error();
		}

	}


	/*
		package_editor_list_data("SELECT itemID, title, author FROM tblNews;", array('Article Title', 'Article Author'));

		This function takes a supplied query and builds an associative array for the display_editor_list.
		Most editors will use this to build a data source for a 'basic add/edit/delete' editor,
		in more complex cases, the developer can just opt to build their own array from scratch and not use this function.

	*/
	function package_editor_list_data($qry, $headings, $pageStart = false, $pageEnd = false, $edit = true, $delete = true)
	{
		global $quipp, $db;

		//$fields = array("Name", "System Name");
		$fields = null;
		$i=0;
		$res = $db->query($qry);
		
			
		if ($db->valid($res)) {
			//populate the visible headings
			foreach ($headings as $value) {
				$fields['headings'][$i]['label'] = $value;
				$i++;
			}

			//reset the iterator
			$i=0;
			//populate the data
			while ($rs = $db->fetch_assoc($res)) {

				foreach ($rs as $key => $value) {
					$fields['rows'][$i][$key]['data'] = $value;
					if ($key != "itemID") {
						$fields['rows'][$i][$key]['leadIn'] = str_shorten($value, 100);
					}
				}
				$i++;
			}

			//throw in some basic controls
			//Note: 'ITEMID' get's replaced by the itemID of that row when this is rendered out
			if ($delete) {
				$fields['controls'][0] = "<input class=\"btnStyle red noPad\" id=\"btnDelete_ITEMID\" type=\"button\" onclick=\"javascript:confirmDelete('?action=delete&id=ITEMID');\" value=\"Delete\" />";
			}
			if ($edit) {
				$fields['controls'][1] = "<input class=\"btnStyle blue noPad\" id=\"btnEdit_ITEMID\" type=\"button\" onclick=\"javascript:window.location='?view=edit&id=ITEMID';\" value=\"Edit\" />";
			}
			return $this->display_editor_list($fields);

		} else if ($res === false || is_resource($res) && $db->num_rows($res) == 0) {
			
			return '<div>No data present</div>';
		
		} else {

			return "bad query";
		}
	}

	/*
		This function takes a supplied associative array (in a pre-determined format) and builds an editor list table. You can use 'package_editor_list_data' which
		builds the necessary array from a supplied query and calls this function, or alternatively you can build your own array and call this function directly.
		An example of the expected array is included in this function. An itemID is required in all query results.
	*/
	function display_editor_list($fields)
	{ 
		//$fields is an array of results
		global $quipp, $db;

		/*
		$fields['headings'][0]['label'] = "Group Name";
		$fields['headings'][1]['label'] = "Col 2";
		$fields['headings'][2]['label'] = "Col 3";

		//Note: 'ITEMID' get's replaced by the itemID of that row when this is rendered out
		$fields['controls'][0] = "<input id=\"btnDelete_ITEMID\" type=\"button\" onclick=\"javascript:confirmDelete('?action=delete&id=ITEMID');\" value=\"Delete\" />";
		$fields['controls'][1] = "<input id=\"btnEdit_ITEMID\" type=\"button\" onclick=\"javascript:window.location='?action=edit&id=ITEMID';\" value=\"Edit\" />";




		$fields['rows'][0]['fieldTitleA']['leadIn'] = "Some sample lead in data...";
		$fields['rows'][0]['fieldTitleB']['leadIn'] = "And some more...";
		$fields['rows'][0]['fieldTitleC']['leadIn'] = "And even more!...";
		$fields['rows'][1]['fieldTitleA']['leadIn'] = "Some sample lead in data...";
		$fields['rows'][1]['fieldTitleB']['leadIn'] = "And some more...";
		$fields['rows'][1]['fieldTitleC']['leadIn'] = "And even more!...";
		$fields['rows'][2]['fieldTitleA']['leadIn'] = "Some sample lead in data...";
		$fields['rows'][2]['fieldTitleB']['leadIn'] = "And some more...";
		$fields['rows'][2]['fieldTitleC']['leadIn'] = "And even more!...";
		$fields['rows'][3]['fieldTitleA']['leadIn'] = "Some sample lead in data...";
		$fields['rows'][3]['fieldTitleB']['leadIn'] = "And some more...";
		$fields['rows'][3]['fieldTitleC']['leadIn'] = "And even more!...";

		$fields['rows'][0]['itemID']['data'] = "1";
		$fields['rows'][1]['itemID']['data'] = "2";
		$fields['rows'][2]['itemID']['data'] = "3";
		$fields['rows'][3]['itemID']['data'] = "4";
		*/


		//yell($fields);

		//table header
		$bufferForReturn =
			"<table id=\"adminTableList\" class=\"adminTableList tablesorter\" width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">
							<thead>
							<tr>";

		foreach ($fields['headings'] as $heading) {
			$bufferForReturn .= "<th>" . $heading['label'] . "</th>";
		}

		foreach ($fields['controls'] as $control) {
			$bufferForReturn .= "<th>&nbsp;</th>";
		}

		$bufferForReturn .= "</tr></thead><tbody>";
		// end of table header
		foreach ($fields['rows'] as $row) {
			$hideDelete = false;
			$bufferForReturn .= "<tr>";
			foreach ($row as $field => $data) {
				if (isset($data['leadIn']) && $field != 'sysGroup' && $field != 'sysOpen') {
					$bufferForReturn .= "<td>" . $data['leadIn'] . "</td>";
				} else if(isset($data['leadIn']) && $field == 'sysGroup' && $data['leadIn'] == '1') {
					$hideDelete = true;
				} else if (isset($data['leadIn']) && $field == 'sysOpen' && $data['leadIn'] == '0') {
				    $hideDelete = true;
				}
			}
			
			foreach ($fields['controls'] as $key => $control) {
				if ($hideDelete == true && $key == '0') {
					$bufferForReturn .= "<td style=\"width:50px;\" align=\"center\">-</td>";
				} else {
					$bufferForReturn .= "<td style=\"width:50px;\">" . str_replace("ITEMID", intval($row['itemID']['data'], 10), $control) . "</td>";
				}
			}

			$bufferForReturn .= "</tr>";
		}




		/*
		$sendMeIn .= "<tr $rowColour><td><span class=\"groupNameList\">$BodyRS[nameFull]</span></td><td>$BodyRS[nameSystem]</td>";
		if($BodyRS['itemID'] > 10 && $BodyRS['itemID'] != 14)	{
			$sendMeIn .= "<td style=\"width:50px;\"><input id=\"btnDelete_$BodyRS[itemID]\" type=\"button\" onclick=\"javascript:confirmDelete('$_SERVER[PHP_SELF]?pb=1&action=3&gid=$BodyRS[itemID]');" . $disableButtons . "\" value=\"$delButtonLabel\" /></td>";
		} else {
			$sendMeIn .= "<td style=\"width:50px; text-align:center;\">System Group</td>";
		}
			$sendMeIn .= "<td></td>";*/




		$bufferForReturn .= "</tbody></table>";

		return $bufferForReturn;

	}



	public function package_editor_contact_sheet($qry, $display) {
	
		global $db;
		
		$res = $db->query($qry);
		
		if ($db->valid($res)) {
			 
			while ($i = $db->fetch_assoc($res)) { 
				print '<div class="contactSheetItem">';
				print '<a href="' . $display['location'] . '../' . $i[$display['image']] . '" class="fancybox"><img src="' . $display['location'] . $i[$display['image']] . '" alt="' . $display['title'] . '" /></a><br />';
				print "<input class=\"btnStyle blue\" id=\"btnEdit_" . base_convert($i['itemID'], 10, 36) . "\" class=\"btnStyle blue\" type=\"button\" onclick=\"javascript:window.location='?view=edit&id=" . base_convert($i['itemID'], 10, 36) . "&album=" . $i['album'] . "';\" value=\"Edit\" />";
				print "<input class=\"btnStyle red noPad\" id=\"btnDelete_" . base_convert($i['itemID'], 10, 36) . "\" class=\"btnStyle red noPad\" type=\"button\" onclick=\"javascript:confirmDelete('?action=delete&id=" . base_convert($i['itemID'], 10, 36) . "');\" value=\"Delete\" />";
				print '</div>';
			}
		}
	}
	public function package_editor_photos($res, $img ,$tableStart = true, $tableEnd = true, $edit = true, $delete = true ,$reorder = false){
		global $db;
		
		if ($reorder != false && intval($reorder) > 0){
			for ($i = 1; $i <= $reorder; $i++){
				$options[$i] = $i;
			}
		}
		
		$display = "";
		if ($tableStart){
			$display .= "<table id=\"photoTableList\" class=\"adminTableList tablesorter\" width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
			$display .= "<thead><tr>";
			$display .= "<th>Photo</th>";
			if ($reorder != false){
				$display .= "<th width=\"90px\">Re-Order</th>";
			}
			$display .= "<th width=\"98px\">&nbsp;</th>";
			$display .= "<th width=\"90px\">&nbsp;</th>";
			$display .= "</tr></thead><tbody>";
		}
		if ($db->valid($res)) {
			while ($i = $db->fetch_assoc($res)){
				$delText = "<input class=\"btnStyle red noPad\" id=\"btnDelete_" . base_convert($i['itemID'], 10, 36) . "\" class=\"btnStyle red noPad\" type=\"button\" onclick=\"javascript:confirmDelete('?action=delete&id=" . base_convert($i['itemID'], 10, 36) . "');\" value=\"Delete\" />";
				$editText = "<input class=\"btnStyle blue\" id=\"btnEdit_" . base_convert($i['itemID'], 10, 36) . "\" class=\"btnStyle blue\" type=\"button\" onclick=\"javascript:window.location='?view=edit&id=" . base_convert($i['itemID'], 10, 36) . "&album=" . $i['album'] . "';\" value=\"Edit\" />";
				$photo = '<a href="' . $img['location'] . '../' . $i[$img['image']] . '" class="fancybox"><img src="' . $img['location'] . $i[$img['image']] . '" alt="' . $img['title'] . '" /></a>';
				if ($delete == false){
					$delText = "&nbsp;";
				}
				if ($edit == false){
					$editText = "&nbsp;";
				}
				$display .= '<tr><td><div class="contactSheetItem">'.$photo.'</div></td>';
				if ($reorder != false){
					$display .= "<td><select name=\"reorder[".trim($i["reorderID"])."]\" id=\"reorder\">";
					foreach ($options as $value => $text){
						$selected = "";
						$optVal = $text;
						if (trim($i["myOrder"]) == $text){
							$optVal .= "*";
							$selected = "selected=\"selected\"";
						}
						$display .= "<option value=\"".$text."\" ".$selected.">".$optVal."</option>";
					}					
					$display .= "</select></td>";
				}
				$display .= "<td>".$editText."</td>";
				$display .= "<td>".$delText."</td>";
				$display .= "</tr>";
			}
		}
		if ($tableEnd){
			$display .= "</table>";
			$display .= "<br /><input type=\"button\" class=\"btnStyle\" name=\"cancelUserForm\" id=\"cancelUserForm\" onclick=\"javascript:window.location.href='" . $_SERVER['PHP_SELF'] . "'\" value=\"Cancel\" />";
		}
		
		echo($display);
	}
	public function package_editor_albums($qry, $selectedAlbum = 0) {
	
		global $db;
		
		$innerReturn = "";
		
		$res = $db->query($qry);
		
		if ($db->valid($res)) {
			
			while ($i = $db->fetch_assoc($res)) { 
				
				if ($selectedAlbum == $i['itemID']) {
					$class = ' selected';
				} else {
					$class = '';
				}
				$featured = (trim($i['featured']) == 1)?"Yes":"No";
				$innerReturn .= '<tr class="albumItem' . $class . '">';
				//$innerReturn .= '<div class="albumItem' . $class . '" data-itemID="' . $i['itemID'] . '">';
				$innerReturn .= '<td><a href="?album=' . $i['itemID'] . '">' . $i['title'] . '</a></td>';
				$innerReturn .= '<td>'.$i['sysStatus'].'</td>';
				$innerReturn .= '<td>'.$featured.'</td>';
				$innerReturn .= '<td><a class="btnStyle green noPad" href="?album=' . $i['itemID'] . '">View Photos</a></td>';
				$innerReturn .= '<td><a class="btnStyle blue noPad" href="?view=editAlbum&id=' . base_convert($i['itemID'], 10, 36) .'">Edit Album</a></td>';
				//$innerReturn .= "<td><input class=\"btnStyle blue noPad\" id=\"btnEdit_" . base_convert($i['itemID'], 10, 36) . "\" type=\"button\" onclick=\"javascript:window.location='?view=editAlbum&id=" . base_convert($i['itemID'], 10, 36) . "';\" value=\"Edit\" /></td>";
				$innerReturn .= "<td><input class=\"btnStyle red noPad\" id=\"btnDelete_" . base_convert($i['itemID'], 10, 36) . "\" type=\"button\" onclick=\"javascript:confirmDelete('?action=deleteAlbum&id=" . base_convert($i['itemID'], 10, 36) . "');\" value=\"Delete\" /></td>";
				$innerReturn .= '</tr>';
			}
		}
		
		//We're returning this as a straight up table for now, we can do some cool gallery thumbs later:
		$toReturn = "<table id=\"adminTableList\" class=\"adminTableList tablesorter\" width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\"><thead><tr>";
		$toReturn .= "<th>Album Name</th>";
		$toReturn .= "<th width=\"90px\">Active</th>";
		$toReturn .= "<th width=\"90px\">Featured</th>";
		$toReturn .= "<th width=\"98px\">&nbsp;</th>";
		$toReturn .= "<th width=\"90px\">&nbsp;</th>";
		$toReturn .= "<th width=\"67px\">&nbsp;</th>";
		$toReturn .= "</tr></thead><tbody>";
		
		$toReturn .= $innerReturn;
		
		$toReturn .= "</tbody></table>";
		
		print $toReturn;
		
		
	}
	
	public function package_editor_option_list($tableName, $fieldName, $dropdownID, $selectedOne = false) {
	
		global $db;
		
		$res = $db->query("SELECT itemID, " . $fieldName . " FROM " . $tableName . " WHERE sysOpen = '1';");		
		$arr = array();
		
		$toReturn = '<select id="'.$dropdownID.'" name="'.$dropdownID.'" class="uniform">';
		$theList = '';
		
		if ($db->valid($res)) {
			
			while ($i = $db->fetch_assoc($res)) { 
			
				if ($selectedOne == $i['itemID']) {
					$selectedItem = '<option value="' . $i['itemID'] . '">' . $i[$fieldName] . ' *</option>';
				} else {
					$theList .= '<option value="' . $i['itemID'] . '">' . $i[$fieldName] . '</option>'; 
				}
			}
		}
		
		if (!empty($selectedItem)) {
			$toReturn .= $selectedItem;
		}
		$toReturn .= $theList;
		$toReturn .= '</select>';
		
		return($toReturn);
	}
	/**
	* Display a list of checkboxes for a form
	* @access public
	* @param string $elementName
	* @param array $elementOptions
	* @param array $checkedVals
	*/
	public function display_group_check_list($elementName,$elementOptions,$checkedVals,$requireCode,$tooltip = false){
		$checkList = "";
		if (is_array($elementOptions)){
			$checkList .= "<dl id=\"propertiesGroupsForm\" class=\"propertiesForm\">";
			foreach($elementOptions as $key => $value){
				if ($key !== "Other"){
					$checked = (in_array($value,$checkedVals))?"checked = \"checked\"":"";
					$checkList .= "<dd class=\"groupListItem\">";
					$checkList .= "<input type=\"checkbox\" name=\"".$requireCode.$elementName."[".$key."]"."\" value=\"".$value."\" ".$checked." id=\"".$elementName.$key."\" class=\"uniform\" />";
					$checkList .= "<label for=\"".$elementName.$key."\">".$value."</label>";
					$checkList .= "</dd>";
				}
			}
			if ($tooltip != false){
				$checkList .= "<dd><p>$tooltip</p><p>&nbsp;</p></dd>";
			}
			if (array_key_exists("Other",$elementOptions)){
				$checkList .= "<dd><label for=\"".$elementName."Other\">Other</label>:</dd>";
				$checkList .= "<dd><textarea rows=\"3\" cols=\"70\" name=\"".$requireCode.$elementName."[".$key."]"."\" id=\"".$elementName."Other\">".$elementOptions["Other"];
				$checkList .= "</textarea></dd>";
			}
			$checkList .= "</dl>";
		}
		return $checkList;
	}
}

?>