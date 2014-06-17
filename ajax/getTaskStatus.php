<?php
include "../include/servers.php";
include "../include/mydb.php";

if(!authenticate()) die();
if(!mydb_connect($ADMIN_NODE)) die();



$result = mysql_query("SELECT `STATUS` FROM `tasks` WHERE `ID` = 1 LIMIT 1;");
$row = mysql_fetch_row($result);
$data = $row[0];

echo json_encode($data);