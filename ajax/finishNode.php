<?php
include "../include/servers.php";
include "../include/mydb.php";

if(!authenticate()) die();
if(!mydb_connect($ADMIN_NODE)) die();

mysql_query("UPDATE `users` SET `STATUS` = 'DONE' WHERE `ID` = '".mysql_real_escape_string($_SESSION["ID"])."' LIMIT 1;");

echo json_encode($_SESSION["ID"]);