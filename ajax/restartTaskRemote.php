<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

@unlink("../data/result.txt");

$files = scandir("../data/tokens/");
if(count($files) > 2){
	foreach($files as $index => $file){
		if($file != '.' || $file != '..') @unlink("../data/tokens/".$file);
	}
}

$data["REMOTE_ADDR"] = $_SERVER["SERVER_ADDR"];
echo json_encode($data);