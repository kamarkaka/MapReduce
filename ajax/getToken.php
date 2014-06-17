<?php
include "../include/servers.php";
include "../include/mydb.php";

if(!authenticate()) die();
if(!mydb_connect($ADMIN_NODE)) die();

// update mapper status to WORKING
mysql_query("UPDATE `users` SET `STATUS` = 'WORKING' WHERE `ID` = '".mysql_real_escape_string($_SESSION["ID"])."' LIMIT 1;");

// retrieve first available file to process
$filePath = "../data/tokens/";
$files = scandir($filePath);
if($files == FALSE){
	die();
}
else if(count($files) > 2){
	$fileName = $files[2];
	$data["FINISHED"] = 0;
	$data["FILE_ID"] = substr($fileName, 0, -4);
	$data["TOKEN"] = explode("\r\n", file_get_contents($filePath.$fileName));

	if(@$file = file_get_contents("../data/result.txt")){
		$result = json_decode($file, true);
	}
	else{
		$result = array();
	}

	$tokenCount = 0;
	foreach($data["TOKEN"] as $key0 => $value0){
		$token = strtolower(substr($value0, 0, 32));
		if(strlen($token) > 3){
			if(!isset($result[$value0])) $result[$value0] = 1;
			else $result[$value0]++;
			$tokenCount++;
		}
	}
	arsort($result);

 	$written = file_put_contents("../data/result.txt", json_encode($result));
 	$data["WRITTEN"] = $written;

// 	if(!mydb_connect($SERVER_IP)) die();
// 	$tokenCount = 0;
// 	foreach($data["TOKEN"] as $index => $value){
// 		$token = strtolower(substr($value, 0, 32));
// 		if(strlen($token) > 3){
//			mysql_query("INSERT INTO `results` (`TOKEN`,`COUNT`) VALUES ('".mysql_real_escape_string($token)."',1) ON DUPLICATE KEY UPDATE `COUNT` = `COUNT` + 1;");
//			$tokenCount++;
// 		}
// 	}
// 	if(!mydb_connect($ADMIN_NODE)) die();

 	// update user status
 	mysql_query("UPDATE `users` SET `REQUESTED` = `REQUESTED` + 1, `PROCESSED` = `PROCESSED` + 1, `TOKEN_COUNT` = `TOKEN_COUNT` + '".mysql_real_escape_string($tokenCount)."' WHERE `ID` = '".mysql_real_escape_string($_SESSION["ID"])."' LIMIT 1;");

 	unlink($filePath.$fileName);
}
else{
	$data["FILE_ID"] = 0;

	if(!mydb_connect($ADMIN_NODE)) die();

	$result = mysql_query("SELECT * FROM `users` WHERE `TYPE` = 'MAPPER' AND `TASK_ID` = 1 AND `STATUS` != 'DONE' LIMIT 1;");
	if(mysql_num_rows($result) == 1){
		$data["FINISHED"] = 0;
	}
	else{
		$data["FINISHED"] = 1;
	}
}

// retrieve statistical data
$result = mysql_query("SELECT `REQUESTED`,`PROCESSED`,`TOKEN_COUNT` FROM `users` WHERE `ID` = '".mysql_real_escape_string($_SESSION["ID"])."' LIMIT 1;");
if(mysql_num_rows($result) == 1){
	$row = mysql_fetch_row($result);
	$data["REQUESTED_COUNT"] = $row[0];
	$data["PROCESSED_COUNT"] = $row[1];
	$data["TOKEN_COUNT"] = $row[2];
}

$result = mysql_query("SELECT COUNT(*) FROM `files` WHERE 1;");
if(mysql_num_rows($result) == 1){
	$row = mysql_fetch_row($result);
	$data["FILE_COUNT"] = $row[0];
}

echo json_encode($data);