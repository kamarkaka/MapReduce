<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

include "../include/servers.php";
include "../include/mydb.php";

$basePath = "../data/tokens/";
$fileName = $_POST["FILE_ID"].".txt";
$str = implode("\r\n", $_POST["TOKENS"]);

$handle = fopen($basePath.$fileName, 'w') or die();
$written = fwrite($handle, $str);
fclose($handle);

if($written === FALSE) echo json_encode(0);
else echo json_encode($written);