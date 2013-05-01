<?php

require dirname(__DIR__) . '/includes/init.php';

if(!$auth->has_permission("modifyusers")) {
	$quipp->system_log("User Export Has Been Blocked Because of Insufficient Privileges.");
    header('Location: /admin/?blocked');
	die('You do not have permission to access this resource');
}

if (!isset($_GET['group'])) {
    header('Location: /admin/users.php');
    die('Group not found');
}

$qry = sprintf("SELECT g.nameFull, g.nameSystem, f.fieldLabel, f.itemID AS fieldID
    FROM sysUGFLinks AS fl 
    INNER JOIN sysUGroups AS g ON g.itemID = fl.groupID
    INNER JOIN sysUGFields AS f ON f.itemID = fl.fieldID
    WHERE fl.groupID='%d'
    AND f.sysStatus='active'
    AND f.sysOpen='1'
    ORDER BY f.myOrder",
        (int)$_GET['group']);
$res = $db->query($qry);

if ($db->valid($res)) {

    header("Content-type: application/csv");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    $fields = array();
    
    $return = '"User ID"';
    $return .= ',"Username"';
    $return .= ',"Registration Date"';

    $groupName = '';
    while ($field = $db->fetch_assoc($res)) {
        if ($groupName == '') {
           header("Content-Disposition: attachment; filename=" . slug($meta['title']) . "-{$field['nameSystem']}.csv");
        }
        $return .= ',"' . $field['fieldLabel'] . '"';
        array_push($fields, "meta('{$field['fieldLabel']}', u.itemID)");
        $groupName = $field['nameFull'];
    }
    $return .= "\r\n";

    
    $uQry = sprintf("SELECT u.itemID, u.userIDField, u.regDate, %s 
        FROM `sysUGLinks` AS ug 
        INNER JOIN `sysUsers` AS u ON u.itemID=ug.userID
        WHERE ug.groupID='%d'
        AND ug.sysStatus='active'
        AND ug.sysOpen='1'
        AND u.sysStatus='active'
        AND u.sysOpen='1'",
            implode(',', $fields),
            (int)$_GET['group']);
    $uRes = $db->query($uQry);
    
    if ($db->valid($uRes)) {
        while($u = $db->fetch_assoc($uRes)) {
            $return .= '"' . implode('","' , $u) . '"';
            $return .= "\r\n";

        }
    }
    die($return);
    
} else {
    header('Location: /admin/users.php');
    die('Group not found');
}
