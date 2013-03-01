<?php
include ($_SERVER['DOCUMENT_ROOT'].'/includes/includedFunctions.php');
$lanIP = isset($_GET['lan']) ? $_GET['lan'] : "";
$lanConnect = implode_curl_multi_with_null(">", array("https://".$lanIP."/test.php", "https://".$_SERVER['REMOTE_ADDR']."/test.php"));
$conArr = explode(">", $lanConnect);

$preferredId = 1;
if(strstr($lanIP, "144") != false && $conArr[0] != "") {
	$preferredId = 0;
}

echo ($preferredId == 0 ? $lanIP : $_SERVER['REMOTE_ADDR']) . ":";

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/databaseConnection.php');
$query = 'SELECT `ip` FROM ips';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());	
$ip = isset($_GET['req']) ? $_GET['req'] : "";
if(mysql_num_rows($result) > 0) {
	$first = true;
	while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		echo ($first ? "" : "|") . $line['ip'];
		$first = false;
	}
	echo ($first ? "" : "|") . $ip;
}

//echo "asdasd$lanIPas" . strstr($lanIP, "144") . "asdadas".$_SERVER['REMOTE_ADDR']."asda";
?>