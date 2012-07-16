<?php
require '../../includes/init.php';
$usr = new User($db);
if (isset($_POST['id'])) {

	parse_str($_POST['fields'], $fields);
	parse_str($_POST['groups'], $groups);
	//yell('print', $fields, $groups);
	
	if (!isset($groups['my_groups_list'])) {
		$groups['my_groups_list'] = false;
	}
	
	if (!isset($fields['meta'])) {
		$fields['meta'] = false;
	}
	
	
	//print User::build_user_editor($_POST['id'], $groups['my_groups_list'], $fields['meta']);
	echo $usr->build_user_editor($_POST['id'], $groups['my_groups_list'], $fields['meta']);

}

?>