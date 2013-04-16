<?php

require dirname(dirname(dirname(__DIR__))) . '/init.php';

session_destroy();

$subQry = sprintf("UPDATE tblApplications SET sysActive = 1 WHERE jobID = '%d' AND userID = '%d'", $_POST['job'], $_POST['user']);
$subRes = $db->query($subQry);

if ($db->affected_rows($subRes) == 1) {
	echo 1;    
} else {
    echo 0;
}