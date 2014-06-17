<?php
include "../include/servers.php";
include "../include/mydb.php";

if(!authenticate()) die();
if(!mydb_connect($ADMIN_NODE)) die();

$query = "SELECT COUNT(*) FROM `users` WHERE `TYPE` = 'MAPPER' AND `TASK_ID` = 1;";
$result = mysql_query($query);
if(!$result){
	die();
}
$row = mysql_fetch_row($result);
$data[0] = $row[0];

$query = "SELECT COUNT(*) FROM `users` WHERE `TYPE` = 'REDUCER' AND `TASK_ID` = 1;";
$result = mysql_query($query);
if(!$result){
	die();
}
$row = mysql_fetch_row($result);
$data[1] = $row[0];

echo json_encode($data);