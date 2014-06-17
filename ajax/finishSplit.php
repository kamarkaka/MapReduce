<?php
include "../include/servers.php";
include "../include/mydb.php";

if(!authenticate()) die();
if(!mydb_connect($ADMIN_NODE)) die();

$result = mysql_query("UPDATE `files` SET `PROCESSED` = 1, `FINISH_TIME` = NOW() WHERE `ID` = '".mysql_real_escape_string($_POST["FILE_ID"])."' AND `PROCESSED` = 0 LIMIT 1;");
if(mysql_affected_rows() == 1) mysql_query("UPDATE `users` SET `PROCESSED` = `PROCESSED` + 1 WHERE `ID` = '".mysql_real_escape_string($_SESSION["ID"])."' LIMIT 1;");

echo json_encode($_SESSION["ID"]);