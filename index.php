<?php

include_once ($_SERVER['DOCUMENT_ROOT']."/includes/version.php");
// sets $version

include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/databaseConnection.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/includedFunctions.php');

$verbose = true;
$verbage = "";
$lanIP = "";

// returns with the format ?getarg1=value1&getarg2=value2&
$getstr = rebuildGetString();

$lanIP = guessLanIPUsingShell();

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

$title = "belnet";
$serverIP = trim($_SERVER['SERVER_NAME']);
$remoteIP = trim($_SERVER['REMOTE_ADDR']);
$remoteIsServer = ($serverIP == $remoteIP || $serverIP == "localhost");
//$readReadme = isset($_COOKIE['readme']) ? true : false;
$user = "";
$userid = "";
$userip = "";
$dbversion = 0.1;
$lastNetCheck = 0;
$ips = array();
$nodes = array();
$pgTitle = $title;
$pgHead = "";
$pgBody = "";
$pgFoot = "";
$userNetwork = "release";
$recentUpdate = "first";


// load basic information about this node, its name and how long since it updated references
$query = 'SELECT * FROM user';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$line = mysql_fetch_array($result, MYSQL_ASSOC);
if($line != false) {
	$userid = $line['id'];
	$user = $line['name'];
	$userip = $line['ip'];
	$dbversion = $line['version'];
	$userNetwork = $line['network'];
	$verbose = $userNetwork == "development";
	$lastNetCheck = $line['time'];
	$recentUpdate = $line['updated'];
	
	//$recentUpdate can also be = first for first run. which is handled inside dash.php
	if(($dbversion != $version || $recentUpdate == "true") && $remoteIsServer) {
		include($_SERVER['DOCUMENT_ROOT'].'/newVersion.php');
	}
	
} else {
	if($remoteIsServer) {
		include($_SERVER['DOCUMENT_ROOT'].'/newUser.php');
		exit(0);
	} else {
		$pgBody .= "this node has not been set up yet";
		include($_SERVER['DOCUMENT_ROOT']."/compile.php");
		exit(0);
	}
}

// load references
$query = 'SELECT * FROM ips';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$i = 0;
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$ips[$i] = $line['ip'];
	$i ++;
}

$manualConnect = isset($_GET['manualconnect']) ? $_GET['manualconnect'] : "";

if(mysql_num_rows($result) < 2 || $userip == "" || $manualConnect != "") {
	if($remoteIsServer) {
		include('connect.php');
		exit(0);
	} else {
		$pgBody .= "this node has not been set up yet";
		include("compile.php");
		exit(0);
	}
}


$query = 'SELECT * FROM nodes';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$nodes[$line['id']] = array('id' => $line['id'], 'name' => $line['name'], 'ip' => $line['ip'], 'online' => $line['online']);
	//if($verbose) $verbage .= "".$line['id'].",  ".$line['name'].",  ".$line['ip'].",  ".$line['online']."<br>";
}

// when its time to do it, collect peer info from all peers
$filecache = array();
$updatingHTML = "";//"<div style='display:none; '>update is availiable from [0]</div>";
if(time() - $lastNetCheck > 800 || time() - $lastNetCheck < 0 || isset($_GET['netupdate'])) {
	
	$maxVersion = 0.0;
	$maxVersionIP = "";
	include($_SERVER['DOCUMENT_ROOT'].'/netUpdate.php');
	
	if($maxVersion > $version) {
		$updatingHTML = <<< END
<div>update is availiable from [$maxVersionIP]</div>
<iframe src="/utilities/performUpdate.php?ip=$maxVersionIP"></iframe>

END;
	}
	
	$q = "UPDATE `bfs`.`user` SET `time` = '" . time() . "' WHERE `user`.`name` = '" . $user . "' LIMIT 1 ;";
	mysql_query($q) or die('Query failed: ' . mysql_error());
}

//load the files cache
$query = 'SELECT * FROM filecache';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$newHosts = array();
	$strArr = explode(":", $line['hosts']);
	foreach($strArr as $s) {
		if($s == "") continue;
		$values = explode("|", $s);
		$host = $values[0];
		$realm = $values[1];
		$fname = $values[2];
		
		$newHosts[$host] = array('realm' => $realm, 'fname' => $fname);
	}
	$filecache[$line['hash']] = array('hosts' => $newHosts, 'size' => $line['size']);
}

if($verbose)  $verbage .= "<br>user: " . $userip . " <br> Server: " . $serverIP."<BR> remote: " . $remoteIP."<BR><BR>";

if($userip != $serverIP) {
	if(go_curl("https://" . $userip . "/test.php", 1) == "") {
		if($remoteIsServer) {
			include($_SERVER['DOCUMENT_ROOT'].'/routerConfig.php');
			exit(0);
		} else {
			$pgBody .= "this node has not been set up yet";
			include($_SERVER['DOCUMENT_ROOT']."/compile.php");
			exit(0);
		}
	}
}

$homeAddr = "https://" . $userip . "/";

$includeJQueryBasic = "<script src=\"".$homeAddr."jquery.js\"></script>";
$includeJQueryUI = "<link rel=\"stylesheet\" href=\"".$homeAddr."jquery-ui.css\" />"
		  . "<script src=\"".$homeAddr."jquery-ui.js\"></script>";

$pgFoot .= $updatingHTML;

include($_SERVER['DOCUMENT_ROOT'].'/dash.php');

?>
