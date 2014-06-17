<?php
include "include/servers.php";
include "include/mydb.php";

if(!mydb_connect($ADMIN_NODE)) die();
register("MASTER");
?>

<!doctype html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Admin | CS234 Demo</title>
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

<div class="row">
 <div class="small-6 large-6 columns">
  <a href="#" class="button disabled expand mapperCount">Mappers: 0</a>
 </div>
 <div class="small-6 large-6 columns">
  <a href="#" class="button disabled expand reducerCount">Reducers: 0</a>
 </div>
</div>

<div class="row panel">
 <div class="row">
  <div class="small-12 large-12 columns" style="text-align: center">MAPPERS</div>
 </div>
 <div class="row mapperList"></div>
</div>

<div class="row panel">
 <div class="row">
  <div class="small-12 large-12 columns" style="text-align: center">REDUCERS</div>
 </div>
 <div class="row reducerList"></div>
</div>

<div class="row panel">
 <div class="row">
  <div class="small-12 large-12 columns startTime" style="text-align: center">Start Time: </div>
  <div class="small-12 large-12 columns finishTime" style="text-align: center">Finish Time: </div>
  <div class="small-12 large-12 columns elapsedTime" style="text-align: center">Total Time: </div>
 </div>
</div>

<div class="row buttonStartTask">
 <div class="small-12 large-12 columns">
  <a href="" class="button expand">Start Task</a>
 </div>
</div>

<div class="row buttonRestartTask">
 <div class="small-12 large-12 columns">
  <a href="" class="button expand">Restart Task</a>
 </div>
</div>

<div class="row">
 <div class="small-12 large-12 columns taskLog"></div>
</div>

<script src="js/vendor/jquery.js"></script>
<script src="js/foundation.min.js"></script>
<script>
var startTime = 0, finishTime = 0;
var reducerCount = 0, finishReducerCount = 0;

$(document).foundation();

function getUser(){
	$.getJSON("ajax/getUser.php", function(data){
		//console.log(data);
		$(".mapperCount").html("Mappers: " + data[0]);
		$(".reducerCount").html("Reducers: " + data[1]);

		reducerCount = data[1];
	});
}
var intervalUserCount = setInterval(getUser, 1000);

$(document).ready(function(){
	$(".buttonStartTask a").click(function(event){
		event.preventDefault();

		startTime = $.now();

		$.getJSON("ajax/startTask.php", function(data){
			//console.log(data);

			if(data == 1){
				//console.log(intervalUserCount);
				clearInterval(intervalUserCount);
			}
		});
	});

	$(".buttonRestartTask a").click(function(event){
		event.preventDefault();
		$.getJSON("ajax/restartTask.php", function(data){
			//console.log(data);

			$(".taskLog").append("local database reset...");

			$.each(data["REDUCERS"], function(key, val){
				$.getJSON("http://" + val + ":8080/CS234/ajax/restartTaskRemote.php", function(msg){
					//console.log(msg);
					$(".taskLog").append("<br/>remote database " + msg["REMOTE_ADDR"] + " reset...");
				});
			});

			$(".mapperList").html("");
			$(".reducerList").html("");
			intervalUserCount = setInterval(getUser, 1000);

			//if(data == 1){
			//	location.reload();
			//}
		});
	});

	setInterval(function(){
		$.getJSON("ajax/getUsers.php", function(data){
			//console.log(data);

			$.each(data["MAPPERS"], function(key, val){
				//console.log(key +" "+ val);
				if($(".mapperList .user" + val[0]).length == 0){
					$(".mapperList").append('<div class="small-6 large-3 columns user' + val[0] + '"><ul class="pricing-table"><li class="title">Node ' + val[0] + '</li><li class="bullet-item">' + val[4] + '</li><li class="bullet-item mapperListStatus">' + val[1] + '</li><li class="bullet-item mapperListReq">REQ: ' + val[2] + '/' + data["FILE_COUNT"] + '</li><li class="bullet-item mapperListProc">PROC: ' + val[3] + '/' + data["FILE_COUNT"] + '</li></ul></div>');
				}
				else{
					$(".mapperList .user" + val[0] + " .mapperListStatus").html(val[1]);
					$(".mapperList .user" + val[0] + " .mapperListReq").html("REQ: " + val[2] + "/" + data["FILE_COUNT"]);
					$(".mapperList .user" + val[0] + " .mapperListProc").html("PROC: " + val[3] + "/" +  + data["FILE_COUNT"]);
				}
			});

			$.each(data["REDUCERS"], function(key, val){
				//console.log(key + " " + val);
 				if($(".reducerList .user" + val[0]).length == 0){
 					$(".reducerList").append('<div class="small-6 large-3 columns user' + val[0] + '"><ul class="pricing-table"><li class="title">Node ' + val[0] + '</li><li class="bullet-item">' + val[4] + '</li><li class="bullet-item reducerListStatus">' + val[1] + '</li><li class="bullet-item reducerListFiles">' + val[2] + ' Files</li><li class="bullet-item reducerListTokens">' + val[3] + ' Tokens</li></ul></div>');
 				}
 				else{

 	 				if(val[1] == "DONE" & $(".reducerList .user" + val[0] + " .reducerListStatus").html() != "DONE") finishReducerCount++;

 					$(".reducerList .user" + val[0] + " .reducerListStatus").html(val[1]);
 					$(".reducerList .user" + val[0] + " .reducerListFiles").html(val[2] + " Files");
 					$(".reducerList .user" + val[0] + " .reducerListTokens").html(val[3] + " Tokens");

 	 				if(finishReducerCount == reducerCount){
 	 					finishReducerCount = 0;
 	 	 				finishTime = $.now();
 	 	 				$(".startTime").html("Start Time: " + startTime);
 	 	 				$(".finishTime").html("Finish Time: " + finishTime);
 	 	 				$(".elapsedTime").html("Total Time: " + (finishTime - startTime) + "ms");
 	 	 			}
 				}
			});
		});
	}, 1000);
});

</script>
</body>
</html>