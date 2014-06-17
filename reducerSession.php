<?php
include "include/servers.php";
include "include/mydb.php";

if(!mydb_connect($ADMIN_NODE)) die();
register("REDUCER");
?>

<!doctype html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Reducer | CS234 Demo</title>
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
</div>

<div class="row">
 <div class="small-12 large-12 columns reducerRequestStats">
  <h6>Requested Tokens: 0/0</h6>
  <div class="progress"><span class="meter" style="width: 0%"></span></div>
 </div>
</div>

<div class="row">
 <div class="small-12 large-12 columns reducerProcessStats">
  <h6>Processed Tokens: 0/0</h6>
  <div class="progress"><span class="meter" style="width: 0%"></span></div>
 </div>
</div>

<div class="row">
 <div class="small-12 large-12 columns taskLog">
  Initiating Reducer Session...Done
 </div>
</div>

<script src="js/vendor/jquery.js"></script>
<script src="js/foundation.min.js"></script>
<script>
$(document).foundation();

$(document).ready(function(){
	var taskStatus = "PENDING";
	var intervalTaskStatus = setInterval(function(){
		$.getJSON("ajax/getTaskStatus.php", function(data){
			taskStatus = data;
			//console.log(taskStatus);
			$(".taskStatus").html("Status: " + taskStatus);

			if(taskStatus == "WORKING"){
				clearInterval(intervalTaskStatus);
				getToken();
			}
		});
	}, 1000);

	function getToken(){
		$(".taskLog").append("<br/>Retrieving Tokens...");
		$.getJSON("ajax/getToken.php", function(data){

			console.log(data);

			$(".reducerRequestStats h6").html("Requested Files: " + data["REQUESTED_COUNT"] + "/" + data["FILE_COUNT"]);
			$(".reducerRequestStats .meter").css("width", Math.floor(100 * data["REQUESTED_COUNT"] / data["FILE_COUNT"]) + "%");

			$(".reducerProcessStats h6").html("Processed Files: " + data["PROCESSED_COUNT"] + "/" + data["FILE_COUNT"]);
			$(".reducerProcessStats .meter").css("width", Math.floor(100 * data["PROCESSED_COUNT"] / data["FILE_COUNT"]) + "%");

			// All files are processed, done
			if(data["FINISHED"] == 1){
				$(".taskLog").append("<br/>All tokens are processed.");

				$.getJSON("ajax/finishNode.php", function(){
					$(".taskStatus").html("Status: DONE");
					$(".taskLog").append("<br/>Task Done.");
				});
			}

			// A Token is available for processing
			else if(data["FILE_ID"] > 0){
				//console.log(data["TOKEN"]);

				$(".taskLog").append("File " + data["FILE_ID"] + " retrieved.");

				getToken();
			}

			// Waiting for mappers
			else{
				setTimeout(getToken, 1000);
			}
		});
	}
});

</script>
</body>
</html>
