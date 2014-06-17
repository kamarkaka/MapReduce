<?php
include "include/servers.php";
include "include/mydb.php";

if(!mydb_connect($ADMIN_NODE)) die();
register("MAPPER");
$REDUCER_NODE = getReducerNodes();

//var_dump($REDUCER_NODE);
?>


<!doctype html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Mapper | CS234 Demo</title>
 <link rel="stylesheet" href="css/normalize.css">
 <link rel="stylesheet" href="css/foundation.css">
 <link rel="stylesheet" href="css/custom.css">
 <script src="js/vendor/modernizr.js"></script>
</head>

<body>

<div class="row">
 <div class="small-12 large-12 columns">
  <a href="index.php" class="button expand">Home</a>
 </div>
</div>

<div class="row panel">
 <div class="small-6 large-6 columns userID">User ID: <?php echo $_SESSION["ID"]." ".$_SERVER["SERVER_ADDR"]; ?></div>
 <div class="small-6 large-6 columns taskStatus">Status: ----</div>
 <div class="small-12 large-12 columns reducerStats"></div>
</div>

<div class="row">
 <div class="small-12 large-12 columns mapperRequestStats">
  <h6>Requested: 0/0</h6>
  <div class="progress"><span class="meter" style="width: 0%"></span></div>
 </div>
</div>

<div class="row">
 <div class="small-12 large-12 columns mapperProcessStats">
  <h6>Processed: 0/0</h6>
  <div class="progress"><span class="meter" style="width: 0%"></span></div>
 </div>
</div>

<div class="row">
 <div class="small-12 large-12 columns taskLog">
  Initiating Mapper Session...Done
 </div>
</div>

<script src="js/vendor/jquery.js"></script>
<script src="js/foundation.min.js"></script>
<script>
var reducerCount = 0, reducerIP = [], sentCount = 0;
var started = false;
var tokenized = false;
var fileID = 0;
var finished = false;

$(document).foundation();

$(document).ready(function(){

	console.log(reducerIP);

	var intervalTaskStatus = setInterval(function(){
		$.getJSON("ajax/getTaskStatus.php", function(taskStatus){
			//console.log(taskStatus);
			$(".taskStatus").html("Status: " + taskStatus);

			if(taskStatus == "WORKING"){
				clearInterval(intervalTaskStatus);
				getSplit();
			}
		});
	}, 1000);
});

function getSplit(){
	started = true;
	$(".taskLog").append("<br/>Retrieving data...");
	$.getJSON("ajax/getSplit.php", function(data){
		console.log(data);

		$(".mapperRequestStats h6").html("Requested: " + data["REQUESTED_COUNT"] + "/" + data["FILE_COUNT"]);
		$(".mapperRequestStats .meter").css("width", Math.floor(100 * data["REQUESTED_COUNT"] / data["FILE_COUNT"]) + "%");

		$(".mapperProcessStats h6").html("Processed: " + data["PROCESSED_COUNT"] + "/" + data["FILE_COUNT"]);
		$(".mapperProcessStats .meter").css("width", Math.floor(100 * data["PROCESSED_COUNT"] / data["FILE_COUNT"]) + "%");

		reducerCount = data["REDUCER_NODE"].length;
		sentCount = data["REDUCER_NODE"].length;

		$(".reducerStats").html("");
		for(var i = 0; i < reducerCount; i++){
			reducerIP[i] = data["REDUCER_NODE"][i];
			$(".reducerStats").append('<div class="small-6 large-3 columns">Reducer ' + i + ': ' + reducerIP[i] + '</div>');
		}

		// All files are processed, done
		if(data["PROCESSED_COUNT"] == data["FILE_COUNT"]){
			$(".taskLog").append("No available files.");

			$.getJSON("ajax/finishNode.php", function(){
				$(".taskStatus").html("Status: DONE");
				$(".taskLog").append("<br/>Task Done.");
				finished = true;
			});
		}

		// A file is available for processing
		else if(data["FILE_ID"] > 0 & data["CONTENT"] != null){
			//console.log(data["CONTENT"]);

			$(".taskLog").append("File " + data["FILE_ID"] + " retrieved.");

			// Text processing
			fileID = data["FILE_ID"];
			tokenizer(data["FILE_ID"], data["CONTENT"]);
		}

		// All files are requested, wait
		else{
			setTimeout(getSplit, 1000);
		}
	});
}

function tokenizer(fileID, fileContent){
	$(".taskLog").append("<br/>Start processing...");
	var tokens = fileContent.replace(/\W+/g, " ").split(" ");

	var reducerSize = [];
	reducerSize[0] = Math.ceil(10 / reducerCount);
	reducerSize[1] = Math.ceil(26 / reducerCount);

	var tokenID = 0;
	var reducers = [];
	for(var i = 0; i < reducerCount; i++){
		reducers[i] = [];
	}

	for(var i = 0; i < tokens.length; i++){
		if(tokens[i] == "") continue;

		tokenID = tokens[i].toLowerCase().charCodeAt();

		var reducerID = 0;

		if(tokenID >= 48 && tokenID <= 57){ // numberical characters
			reducerID = Math.floor((tokenID - 48) / reducerSize[0]);
		}
		else if(tokenID >= 97 && tokenID <= 122){ // alphabetical characters
			reducerID = Math.floor((tokenID - 97) / reducerSize[1]);
		}
		else{ // other chars send to last reducer
			reducerID = reducerCount - 1;
		}

		reducers[reducerID].push(tokens[i]);
	}

	//console.log(reducers);

	$(".taskLog").append("<br/>Tokens Extracted");

	for(var i = 0; i < reducerCount; i++){
		$.ajax({
			url: "http://" + reducerIP[i] + ":8080/CS234/ajax/saveToken.php",
			type: "POST",
			data: {FILE_ID: fileID, TOKENS: reducers[i]}
		}).done(function(data){
			$(".taskLog").append("<br/>Tokens sent to reducer");
			//console.log("returned data: " + data);
			tokenized = true;
			sentCount--;

		}).error(function(data){
			$(".taskLog").append("<br/>Reducer error");
		});
	}
}

$(document).ajaxStop(function(){
	if(started == true & finished == false & tokenized == true & sentCount == 0){

		$.ajax({
			url: "ajax/finishSplit.php",
			type: "POST",
			data: {FILE_ID: fileID}
		}).done(function(data){
			$(".taskLog").append("<br/>All Tokens are sent");
			tokenized = false;
			sentCount = reducerCount;

			if(started == true & finished == false) getSplit();
		});
	}
});

</script>
</body>
</html>

