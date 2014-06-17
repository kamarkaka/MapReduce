<?php
include "../include/servers.php";
include "../include/mydb.php";

if(!authenticate()) die();
if(!mydb_connect($ADMIN_NODE)) die();
$REDUCER_NODE = getReducerNodes();
//var_dump($REDUCER_NODE);

$data["REDUCERS"] = $REDUCER_NODE;

mysql_query("DELETE FROM `users` WHERE 1;");
mysql_query("UPDATE `tasks` SET `STATUS` = 'PENDING' WHERE `ID` = 1;");
mysql_query("UPDATE `files` SET `REQUESTED` = 0, `PROCESSED` = 0, `REQUEST_ID` = 0, `REQUEST_TIME` = '0000-00-00 00:00:00', `FINISH_TIME` = '0000-00-00 00:00:00';");

session_destroy();

echo json_encode($data);