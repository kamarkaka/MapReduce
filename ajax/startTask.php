<?php
include "../include/servers.php";
include "../include/mydb.php";

if(!authenticate()) die();
if(!mydb_connect($ADMIN_NODE)) die();

$query = "UPDATE `tasks` SET `STATUS` = 'WORKING' WHERE `STATUS` = 'PENDING' AND `ID` = 1 LIMIT 1;";
$result = mysql_query($query);
if(!$result){
	die();
}
$row = mysql_affected_rows();

echo json_encode($row);