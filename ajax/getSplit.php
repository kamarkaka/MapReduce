<?php
include "../include/servers.php";
include "../include/mydb.php";

if(!authenticate()) die();
if(!mydb_connect($ADMIN_NODE)) die();

$fileID = 0;

$data["FILE_ID"] = 0;
$data["CONTENT"] = "";
$data["REQUESTED_COUNT"] = 0;
$data["PROCESSED_COUNT"] = 0;
$data["FILE_COUNT"] = 1;

mysql_query("START TRANSACTION;");

// update mapper status to WORKING
mysql_query("UPDATE `users` SET `STATUS` = 'WORKING' WHERE `ID` = '".mysql_real_escape_string($_SESSION["ID"])."' LIMIT 1;");

// retrieve first available file to process
$result = mysql_query("SELECT `ID` FROM `files` WHERE (`REQUESTED` = 0) OR (`REQUESTED` = 1 AND `PROCESSED` = 0 AND TIMESTAMPDIFF(SECOND, `REQUEST_TIME`, NOW()) > 10) ORDER BY `ID` ASC LIMIT 1;");
if(mysql_num_rows($result) == 1){
	$row = mysql_fetch_row($result);
	$fileID = $row[0];
	$data["FILE_ID"] = $fileID;
	$str = file_get_contents("../data/pages/".$fileID.".txt");

	if($str !== false){
		$data["CONTENT"] = preg_replace("/[^a-zA-Z0-9]+/", " ", $str);

		// update corresponding file status
		mysql_query("UPDATE `files` SET `REQUESTED` = 1, `REQUEST_ID` = '".mysql_real_escape_string($_SESSION["ID"])."', `REQUEST_TIME` = NOW() WHERE `ID` = '".mysql_real_escape_string($fileID)."' LIMIT 1;");

		// update user status
		mysql_query("UPDATE `users` SET `REQUESTED` = `REQUESTED` + 1 WHERE `ID` = '".mysql_real_escape_string($_SESSION["ID"])."' LIMIT 1;");
	}

}
else{
	$data["FILE_ID"] = 0;
	$data["CONTENT"] = "NULL";
}

// retrieve number of requested files
$result = mysql_query("SELECT SUM(`REQUESTED`), SUM(`PROCESSED`) FROM `users` WHERE `TASK_ID` = 1 AND `TYPE` = 'MAPPER';");
if(mysql_num_rows($result) == 1){
	$row = mysql_fetch_row($result);
	$data["REQUESTED_COUNT"] = $row[0];
	$data["PROCESSED_COUNT"] = $row[1];
}

$result = mysql_query("SELECT COUNT(*) FROM `files` WHERE 1;");
if(mysql_num_rows($result) == 1){
	$row = mysql_fetch_row($result);
	$data["FILE_COUNT"] = $row[0];
}

$result = mysql_query("SELECT `IP_ADDRESS` FROM `users` WHERE `TASK_ID` = 1 AND `TYPE` = 'REDUCER';");
while($row = mysql_fetch_row($result)){
	$data["REDUCER_NODE"][] = $row[0];
}

mysql_query("COMMIT;");

echo json_encode($data);