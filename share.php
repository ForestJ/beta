<?php

$pgHead .= <<<END
<style type="text/css">
.leftcell {
	padding:7px;
	border-bottom: 1px solid #aaaaaa;
	border-right: 1px solid #aaaaaa;
}
.rightcell {
	padding:5px;
	border-bottom: 1px solid #aaaaaa;
}
</style>
END;

$hasForm = false;
$i = 0;
while(isset($_POST["realm$i"])) {
	if(isset($_POST["path$i"])) $hasForm = true;
	$i++;
}

if($hasForm) {
	mysql_query("DELETE FROM `bfs`.`realms` where 1 = 1") or die('Query failed0: ' . mysql_error());
	
	$i = 0;
	while(isset($_POST["realm$i"])) {
		$realmName = trim($_POST["realm$i"]);
		$realmPath = isset($_POST["path$i"]) ? trim($_POST["path$i"]) : "";
		if($realmPath != "") {
			$q = "INSERT INTO `bfs`.`realms` (`name`, `path`) VALUES ('$realmName', '$realmPath');";
			mysql_query($q) or die('Query failed: ' . mysql_error());
		}
		$i++;
	}
	
	go_curl("https://localhost/?netupdate=t", 15);
	
	header("location: $getstr");
	exit(0);
}


// make a list of requests from the current list of known nodes
// the requested pages give us a list of thier realms delimited by :
$urls = array();
$urlsHash = array();
foreach($ips as $ip) {
	if(hasIP($ip)) {
		$u = "https://" . $ip . "/utilities/realms.php";
		$urlsHash[$u] = $u;
	}
}
foreach($urlsHash as $key => $value) { 
	$urls[] = $value; 
}

// do the multi url curl request!!  
// realms separated by :, no per-node separation
$allrealms =  implode_curl_multi(":", $urls);
$realm_array = array();
$withRedundancies = explode(":", $allrealms);
foreach($withRedundancies as $r) {
	if(preg_match("/^\w/i", $r)) $realm_array[trim($r)] = $r;	
}

$marker = "___NODE";

$result = mysql_query( "SELECT * FROM `bfs`.`realms`") or die('Query failed: ' . mysql_error());	
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$realm_array[trim($line['name'])] = $line['path'];
}

$shareHTML .= <<<END
	<div style="margin:15px; padding: 10px; background:#ffffff; ">
	<a style="float:right; margin:5px; padding: 3px 0px 0px 2px; border:2px dashed #dddddd;" href="?t=fs"><img border="0" src="img/close.png"></img></a>
	<form name="sharepath" enctype="multipart/form-data" action="$getstr" method="POST">
	<table cellspacing="0">
	<tr><td></td><td colspan="2" style="padding:7px;"><b>edit shared folders</b></td></tr>
	
	<tr><td></td><td class="leftcell">name</td>
	<td class="rightcell">path to folder</td>
END;

$i = 0;
foreach($realm_array as $rname => $rpath) {
	// $rname == $rpath means this this node does not yet definition for this realm.
	if($rname == $rpath) $rpath = "";
	$shareHTML .= <<<END
			<tr><td></td><td  class="leftcell"><input type="hidden" name="realm$i" value="$rname" />$rname</td>
			<td class="rightcell"><input style="width:300px;" type="text" name="path$i" value="$rpath" /></td></tr>
END;
	
	$i++;
}

$shareHTML .= <<<END

	<tr><td style="padding:7px;"> add new folder:<br>&nbsp;</td>
	<td class="leftcell">name:<br><input type="text" name="realm$i" value="" /></td>
	<td class="rightcell">path:<br><input style="width:300px;" type="text" name="path$i" value="" /></td></tr>
	
	<tr><td></td><td colspan="2" style="padding:7px; text-align:right;"><input type="submit" value="Update Shared Folders" /></td></tr>
	</table>
	</form>
	</div>
END;



?>