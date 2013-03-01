<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/databaseConnection.php');

$result = mysql_query( "SELECT * FROM `bfs`.`realms`") or die('Query failed: ' . mysql_error());
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	echo $line['name'] ." : ";
}
?>