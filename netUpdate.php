<?php

include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/databaseConnection.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/includedFunctions.php');

$lastTime = 0.0;

if($verbose) $lastTime = microtime(true);
// first we will update our list of files based on whats in the shared folder.
$files = array();

$result = mysql_query( "SELECT * FROM `bfs`.`realms`") or die('Query failed: ' . mysql_error());	
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if($verbose)  $verbage .= "REALM ". $line['name'] ." : " . $line['path'] . "<BR>";
	$temp = find_files_recurse($line['path']);
	$cut = strlen($line['path']);
	foreach($temp as $f) {
		$hash = fast_md5_file($f);
		$cf = substr($f, $cut);
		if($verbose) $verbage .= $line['name'].$cf."<br>";
		$files[$hash] = array('realm' => $line['name'], 'fname' => $cf, 'size' => filesize($f));
	}
}


$cwd = my_get_cwd().'/shared';
if($verbose)  $verbage .= $cwd . "<BR>";

if($verbose)  $verbage .= "<BR> TIME RECURSIVE AND MD5: " . (microtime(true) - $lastTime) . "<BR><BR>";

// clear out and re-add
mysql_query("DELETE FROM `bfs`.`files` WHERE 't' = 't'") or die('Query failed: ' . mysql_error());	
foreach($files as $hash => $file) {
	$escapedName = str_replace("\\", "\\\\", strtolower($file['fname']));
	$realm = strtolower($file['realm']);
	$q = "INSERT INTO `bfs`.`files` (`hash`, `realm`, `fname`, `size`) VALUES ( '".$hash."', '".$realm."', '".$escapedName."', '" . $file['size'] . "' );";
	if($verbose)  $verbage .= $q . "<BR>";
	mysql_query($q) or die('Query failed: ' . mysql_error());	
}

// make a list of requests from the current list of known nodes
// the requested pages give thier best idea of the requesters (this) ip
// as well as a | separated list of ips they know about
$urls = array();
$urlsHash = array();
foreach($ips as $ip) {
	if(hasIP($ip)) {
		$u = "https://" . $ip . "/utilities/myIPAndOtherKnownIPs.php?req=".$ip."&lan=".$lanIP;
		$urlsHash[$u] = $u;
	}
}
foreach($urlsHash as $key => $value) { 
	$urls[] = $value; 
	if($verbose)  $verbage .= $value ."<br>";
}

// do the multi url curl request!!  
//separate results separated by *, separate types of result separated by , and ips separated by |
$usersAndMyIp =  implode_curl_multi("*", $urls);
if($verbose)  $verbage .= "<br><br>";

mysql_query("DELETE FROM `bfs`.`ips`") or die('Query failed0: ' . mysql_error());	
$ips = array();

$myIPs = array();
$arr = explode("*", $usersAndMyIp);
foreach($arr as $a) {
	if($a == "") continue;
	$ex = explode(":", $a);
	if($verbose)  $verbage .= $a . "&" . $ex[0] . "&" . $ex[1] ."<br>";
	// count occurances of a specific result from the "whats my ip" test
	if(array_key_exists($ex[0], $myIPs)) {
		$myIPs[$ex[0]] += 1;
	} else {
		$myIPs[$ex[0]] = 1;
	}
	// concat the ips without redundancy
	$IPsArr = explode("|", $ex[1]);
	foreach($IPsArr as $ip) {
		if(!in_array($ip, $ips) && hasIP($ip) && $ip != "127.0.0.1") {
			$ips[] = $ip;
			$q = "INSERT INTO `bfs`.`ips` (`ip`) VALUES ('". $ip . "')";
			mysql_query($q) or die('Query failed 18: ' . mysql_error());
		}			
	}
}

// based on number of occurances, chose something to be used as "my ip" the public ip of this node.
$newUserIp = "";
$max = 0;
foreach($myIPs as $key => $value) {
	if(($value > $max || ($value == $max && $key != $lanIP)) && hasIP($key) && $key != "127.0.0.1") {
		$newUserIp = $key;
		$max = $value;
	}
}

// if its different from before, update everything!
/*
if($verbose)  {
	$verbage .= "<BR> res: " . $newUserIp . "<BR> <BR> ";
	$verbage .= (hasIP($newUserIp) ? "y":"n") ."<BR> ";
	$verbage .= (($userip != $newUserIp || $nodes[$userid]['ip'] != $userip) ? "y":"n") ."<BR> ";
}
*/
if(hasIP($newUserIp) && ($userip != $newUserIp || $nodes[$userid]['ip'] != $userip)) {
	$userip = $newUserIp;
	$q = "UPDATE `bfs`.`user` SET `ip` = '" . $userip . "' WHERE `user`.`id` = '" . $userid . "' LIMIT 1 ;";
	mysql_query($q) or die("Query failed67: " . mysql_error());
	$nodes[$userid]['ip'] = $userip;
	$q = "UPDATE `bfs`.`nodes` SET `ip` = '" . $userip . "' WHERE `nodes`.`id` = '" . $userid . "' LIMIT 1 ;";
	mysql_query($q) or die("Query failed70: " . mysql_error());
	
	if($verbose)  $verbage .= "<BR> SET MY IP: ".$nodes[$userid]['ip']." as $userid<BR><BR> ";
	
	if(!in_array($userip, $ips)) {
		$ips[] = $userip;
		$q = "INSERT INTO `bfs`.`ips` (`ip`) VALUES ('". $userip . "')";
		mysql_query($q) or die('Query failed 75: ' . mysql_error());
	}
}


// Now we will do the second half - updating the list of nodes that are online,
// updating the list of all hosted files,
// and updating the posts and comments
// Also we will disseminate our own IP in the same step. 
// first make this list or URLS as before.
$urls = array();
$urlsHash = array();
foreach($ips as $ip) {
	if(hasIP($ip) && $ip != "127.0.0.1") {
		$u = "https://" . $ip . "/utilities/registerMeAndReturnNodeInfoWithFiles.php?ip=".$ip."&add=" . $userip;
		$urlsHash[$u] = $u;
	}
}
foreach($urlsHash as $key => $value) { 
	$urls[] = $value; 
	if($verbose)  $verbage .= $value ."<br>";
}

// gogo curl... nodes separated by comma, data by | in result.
$collection =  implode_curl_multi(">", $urls);

// set all the nodes offline. the ones that responded will be set back online.
$q = "UPDATE `bfs`.`nodes` SET `online` = '0' WHERE `nodes`.`online` = '1';";
mysql_query($q) or die('Query failed37: ' . mysql_error());

mysql_query("DELETE FROM `bfs`.`filecache`") or die('Query failed1: ' . mysql_error());	

mysql_query("DELETE FROM `bfs`.`posts`") or die('Query failed2: ' . mysql_error());
mysql_query("ALTER TABLE `bfs`.`posts` AUTO_INCREMENT =1") or die('Query failed3: ' . mysql_error());

$maxVersion = 0.0;
$maxVersionIP = "";
$posts = array();
$results = explode(">", $collection);
$resHash = array();
foreach($results as $result) {
	if($result == "") continue;
	// written in utilities/registerMeAndReturnNodeInfoWithFiles.php
	$resTypes = explode("*", $result);
	$userResult = $resTypes[0];
	$filesResult = $resTypes[1];
	$postsResult = $resTypes[2];
	$versionResult = $resTypes[3];
	
	$values = explode("|", $userResult);
	$userIPResult = $values[0];
	$userIDResult = $values[1];
	if(!array_key_exists($userIDResult, $resHash) || $resHash[$userIDResult]['ip'] == $lanIP) {
		$resHash[$userIDResult] = array('ip' => $userIPResult, 'ident' => $userResult, 'files' => $filesResult, 'posts' => $postsResult);
	}
	if($versionResult > $maxVersion) {
		$maxVersion = $versionResult;
		$maxVersionIP = $userIPResult;
	}
}
if($verbose) $verbage .= "<br>VERSION: " . $maxVersion."<br>";

foreach($resHash as $key => $val) {
	$identStr = $val['ident'];
	$filesStr = $val['files'];
	$postsStr = $val['posts'];

	$values = explode("|", $identStr);
	$userIPResult = $values[0];
	$userIDResult = $values[1];
	$userNameResult = $values[2];
	if(array_key_exists($userIDResult, $nodes)) {
		$nodes[$userIDResult]['ip'] = $userIPResult; $nodes[$userIDResult]['online'] = 1;
		$q = "UPDATE `bfs`.`nodes` SET `ip` = '" . $values[0] . "', `online` = '1' WHERE `nodes`.`id` = '" . $values[1] . "' LIMIT 1 ;";
		mysql_query($q) or die('Query failed 49: ' . mysql_error());
	} else {
		if($verbose)  $verbage .= "<br>Adding Node: " . $userIDResult . " " . $userIPResult;
		$nodes[$userIDResult] = array('id' => $userIDResult, 'name' => $userNameResult, 'ip' => $userIPResult, 'online' => 1);
		$q = "INSERT INTO `bfs`.`nodes` (`id` , `name` , `ip`, `online`) VALUES ('" . $userIDResult . "', '" . $userNameResult . "', '" . $userIPResult . "', '1');";
		mysql_query($q) or die('Query failed 531: ' . mysql_error());
	}
	
	if($filesStr != "") {
		$fileStrings = explode(":", $filesStr);
		foreach($fileStrings as $str) {
			$values = explode("|", $str);
			$fHash  = $values[0];
			$fRealm = $values[1];
			$fName  = $values[2];
			$fSize  = $values[3];			
			if(!array_key_exists($fHash, $filecache)) {
				$filecache[$fHash] = array('hosts' => array(), 'size' => $fSize);
			}
			$filecache[$fHash]['hosts'][$userIDResult] = array('realm' => $fRealm, 'fname' => $fName);
		}
	}
	
	if($postsStr != "") {
		$postStrings = explode(":", $postsStr);
		foreach($postStrings as $str) {
			$values = explode("|", $str);
			if(!array_key_exists($values[1], $posts)) {
				$posts[$values[1]] = array('ids' => array(), 'rehash' => $values[2], 'userid' => $values[3], 'body' => $values[4], 'attach' => $values[5]);
			}
			$posts[$values[1]]['ids'][] = $values[0];
		}
	}
}


uasort($posts, 'cmpPost');


foreach($filecache as $hash => $arr) {
	$first = true;
	$q = "";
	foreach($arr['hosts'] as $nodeid => $realmAndFName) {
		$q .= ($first ? "" : ":") . $nodeid . '|' . $realmAndFName['realm'] . '|' . $realmAndFName['fname'];
		$first = false;
	}
	$q = "INSERT INTO `bfs`.`filecache` (`hash`, `hosts`, `size`) VALUES ('". $hash . "', '" . $q . "', '" . $arr['size'] . "' )";
	mysql_query($q) or die('Query failed 532: ' . mysql_error());
}

foreach($posts as $hash => $post) {
	$q = "INSERT INTO `bfs`.`posts` (`id`, `hash`, `rehash`, `userid`, `body`, `attach`) VALUES (NULL, '" . $post['hash'] . "', '" . $post['rehash'] . "', '" . $post['userid'] . "', '" . $post['body'] . "', '" . $post['attach'] . "' )";
	mysql_query($q) or die('Query failed 533: ' . mysql_error());
}

if($maxVersion != $version) {
	//$updatefile = go_curl("https://".$maxVersionIP."/utilities/build.php?update=t"); 
}

ext_curl("belnet-nodes.net84.net/?id=$userid&name=$user&ip=$userip", 3); 

if($verbose) {
	 $verbage .= "<br>Nodes:<br>";
	foreach($nodes as $key=>$value) {
		 $verbage .= $key . " " . $value['ip'] . "<br>";
	}
	 $verbage .= "<br>";
}

// speacial comparison for sorting array of posts. Based on averages of several possibly differing ids.
function cmpPost($a, $b) {
	$ac = 0;
	$av = 0.0;
	$bc = 0;
	$bv = 0.0;
	foreach($a['ids'] as $id) {
		$ac ++;
		$av += $id;
	}
	foreach($b['ids'] as $id) {
		$bc ++;
		$bv += $id;
	}
	$av /= $ac;
	$bv /= $bc;

    if ($av == $bv) {
        return 0;
    }
    return ($av < $bv) ? -1 : 1;
}

 // MD5 hash of filetype, file size, and 4kb from the middle of the file
function fast_md5_file ($path) {
	$type = filetype($path);
	$bytes = filesize($path);
	$readbytes = 4096;
	$seekbytes = round($bytes/2);
	$contents = "";
	if($seekbytes > $bytes - $readbytes) {
		$seekbytes = $bytes - $readbytes;
	}
	
	$fp = fopen($path, 'rb');
	if($fp) {
		fseek($fp, $seekbytes);
		$contents = fread($fp, $readbytes);
	}
	return md5($type.$bytes.$contents);
}

function find_files_recurse ($dir) {

	$temp = scandir($dir);
	$result = array();
	foreach($temp as $value)
    {
        if($value === '.' || $value === '..' || $value[0] == '.') {
			continue;
		}
        if(is_file("$dir/$value")) {
			$result[]="$dir/$value";
		} else {
			foreach(find_files_recurse("$dir/$value") as $value) {
				$result[]=$value;
			}
		}
    } 
	return $result;
}


?>