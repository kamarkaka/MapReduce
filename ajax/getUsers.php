<?php
include "../include/servers.php";
include "../include/mydb.php";

if(!authenticate()) die();
if(!mydb_connect($ADMIN_NODE)) die();

$query = "SELECT `ID`,`IP_ADDRESS`,`TYPE`,`STATUS`,`REQUESTED`,`PROCESSED`,`TOKEN_COUNT` FROM `users` WHERE `TASK_ID` = 1 ORDER BY `ID`;";
$result = mysql_query($query);
if(!$result){
	die();
}

$data["MAPPERS"] = Array();
$data["REDUCERS"] = Array();

while($row = mysql_fetch_assoc($result)){
	if($row["TYPE"] == "MAPPER"){
		$data["MAPPERS"][] = array($row["ID"], $row["STATUS"], $row["REQUESTED"], $row["PROCESSED"],$row["IP_ADDRESS"]);
	}
	else if($row["TYPE"] == "REDUCER"){
		$data["REDUCERS"][] = array($row["ID"], $row["STATUS"], $row["PROCESSED"], $row["TOKEN_COUNT"],$row["IP_ADDRESS"]);
	}
}

$result = mysql_query("SELECT COUNT(*) FROM `files` WHERE 1;");
$row = mysql_fetch_row($result);
$data["FILE_COUNT"] = $row[0];

echo json_encode($data);