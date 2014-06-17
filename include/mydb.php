<?php
session_start();

function mydb_connect($host){
	@mysql_close();

	$link = mysql_connect($host, "cs234", "cs234");
	if(!$link) return false;

	$db_selected = mysql_select_db("cs234", $link);
	if(!$db_selected) return false;

	return true;
}

function register($type){
	echo "checking session...<br/>";
	if(isset($_SESSION["ID"])){
		echo "session set...<br/>";
		$result = mysql_query("SELECT `SESSION_ID`,`IP_ADDRESS`,`TYPE` FROM `users` WHERE `ID` = '".mysql_real_escape_string($_SESSION["ID"])."' LIMIT 1;");
		if(mysql_num_rows($result) != 1){
			echo "no session id #".$_SESSION["ID"]." in database...<br/>";
			unset($_SESSION["ID"]);
		}
		else{
			$row = mysql_fetch_row($result);
			if($row[1] != $_SERVER["REMOTE_ADDR"] || $row[2] != $type){
				echo "session info does not match: (".$_SERVER["REMOTE_ADDR"].",".$row[1]."),(".$type.",".$row[2].")<br/>";
				// mysql_query("DELETE FROM `users` WHERE `ID` = '".mysql_real_escape_string($_SESSION["ID"])."' LIMIT 1;");
				unset($_SESSION["ID"]);
			}
		}
	}

	if(!isset($_SESSION["ID"])){
		echo "session does not set...<br/>";
		mysql_query("INSERT INTO `users` (`IP_ADDRESS`,`TYPE`,`SESSION_ID`) VALUES ('".mysql_real_escape_string($_SERVER["REMOTE_ADDR"])."','".mysql_real_escape_string($type)."', '".mysql_real_escape_string(session_id())."');");
		$_SESSION["ID"] = mysql_insert_id();
	}

	echo "session id #".$_SESSION["ID"];
}

function authenticate(){
	if(!isset($_SESSION["ID"])) return false;
	else return true;
}

function getReducerNodes(){
	$reducers = array();
	$result = mysql_query("SELECT `IP_ADDRESS` FROM `users` WHERE `TASK_ID` = 1 AND `TYPE` = 'REDUCER';");
	while($row = mysql_fetch_row($result)){
		$reducers[] = $row[0];
	}

	return $reducers;
}