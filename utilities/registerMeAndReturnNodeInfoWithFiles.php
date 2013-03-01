<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/databaseConnection.php');

$add = isset($_GET['add']) ? $_GET['add'] : "";

$query = "SELECT * FROM `bfs`.`ips` WHERE `ips`.`ip` = '" . $add . "';";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

if(mysql_num_rows($result) == 0) {
	$q = "INSERT INTO `bfs`.`ips` (`ip`) VALUES ('" . $add . "')";
	mysql_query($q) or die('Query failed: ' . mysql_error());
}

$query = 'SELECT * FROM user';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$line = mysql_fetch_array($result, MYSQL_ASSOC);
$ip = isset($_GET['ip']) ? $_GET['ip'] : "";
if($line != false) {
	echo $ip . "|" . $line['id'] . "|" . $line['name'];
}

echo "*";

$query = 'SELECT * FROM files';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
if(mysql_num_rows($result) > 0) {
	$first = true;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		echo ($first ? "" : ":") . $line['hash'] . "|" .$line['realm'] . "|" .  $line['fname'] . "|" . $line['size'];
		$first = false;
	}
}

echo "*";

$query = 'SELECT * FROM posts';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
if(mysql_num_rows($result) > 0) {
	$first = true;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		echo ($first ? "" : ":") . $line['id'] . "|" .  $line['hash'] . "|" .  $line['rehash'] . "|" .  $line['userid'] . "|" .  $line['body']. "|" .  $line['attach'];
		$first = false;
	}
}

echo "*";

$query = 'SELECT * FROM user';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
if(mysql_num_rows($result) > 0) {
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		echo $line['version'];
	}
}
?>