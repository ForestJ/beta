<?php
include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/databaseConnection.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/includedFunctions.php');

$serverIP = trim($_SERVER['SERVER_NAME']);
$remoteIP = trim($_SERVER['REMOTE_ADDR']);
$remoteIsServer = ($serverIP == $remoteIP || $serverIP == "localhost");
if(!$remoteIsServer) exit(0);

$defaultip = "";

if($manualConnect == "") {
	$result = ext_curl("http://belnet-nodes.net84.net/?action=list", 10);
	$lines = explode("\n", $result);
	foreach($lines as $line) {
		//example line: 6e8db246:mac:144.89.175.162:online:
		//echo $line . "<br>";
		$values = explode(":", $line);
		if($values[3] == "online" && $defaultip == "" && $values[2] != $serverIP && $values[2] != $remoteIP && $values[2] != "127.0.0.1") {
			$defaultip = $values[2];
		} 
	}
}

$lanIPWarning = ($lanIP == "127.0.0.1" ? "<p style='color:red; font-weight:bold; font-size:14px;'>WARNING: unable to determine your computer's LAN address. are you connected to the internet?</p><br><br>" : "");

// set by the form. which is set by $manualConnect. which is set by index.php before this file is included.
$manual = isset($_POST['manual']) ? $_POST['manual'] : "";
$ip = isset($_POST['ip']) ? $_POST['ip'] : $defaultip;
if($ip != "") {
	if(preg_match('/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/', $ip, $matches)) {
		$ip = $matches[0];
		
		if($ip == $_SERVER['SERVER_ADDR']) {
			$pgBody .= "enter the lan address of a different node, not this one";
		} else if($ip != "") {
			$body = go_curl('https://'.$ip.'/utilities/myIPAndOtherKnownIPs.php?req='.$ip.'&lan='.$lanIP, 10);
			
			if($body != "") {
				$arr = explode(":", $body);
				$userip = $arr[0];
				$body = $arr[1] . "|" . $arr[0];
				if($manual == "") {
					$q = "INSERT INTO `bfs`.`ips` (`ip`) VALUES ";
					$values = explode("|", $body);
					$first = true;
					foreach($values as $value) {
						$q .= ($first ? "" : ", ") . "('" . $value . "')";
						$first = false;
					}
					//$pgBody .= $q;
					mysql_query($q) or die("Query failed. the server at '" . $ip . "' is confused or corrupted: " . mysql_error());
					
					//$q = "UPDATE `bfs`.`user` SET `updated` =  'first';";
					//mysql_query($q) or die("Query2 failed. " . mysql_error());
					
					header( 'Location: ' . "https://localhost/" );
					exit(0);
				} else {
					$q = "INSERT INTO `bfs`.`ips` (`ip`) VALUES ('" . $ip . "');";
					header( 'Location: ' . "https://localhost/?netupdate=t" );
				}
			} else {
				if($pgBody == "") $pgBody .= "the server at '" . $ip . "' could not be contacted ";
			}
			
		}
		
	} else {
		$pgBody .= "'" . $ip . "' is not a valid address ";
	}
}

$pgHead .= <<<END
<style type="text/css">
	#outer {height: 400px; overflow: hidden; position: relative; width: 100%;}
	#outer[id] {display: table; position: static;}
	
	#middle {position: absolute; top: 50%; width: 100%; text-align: center;} /* for explorer only*/
	#middle[id] {display: table-cell; vertical-align: middle; position: static;}
	
	#inner {position: relative; top: -50%; text-align: center;} /* for explorer only */
	#inner {width: 500px; margin-left: auto; margin-right: auto;} /* for all browsers*/
	
	.input {
	line-height:18px;
	font-size:18px;
	};
</style>
END;
$pgBody .= <<<END
$lanIPWarning
<div id="outer">
	<div id="middle">
		<div id="inner">
			<div style="font-family:sans-serif; font-size:25px; padding-bottom:5px;">enter the address of any other node</div>
			<form method="post">
			<input type="text" class="input"  name="ip" value="$ip"/>
			<input type="hidden" name="manual" value="$manualConnect"/>
			<input type="submit" class="input"  value="go" />
			</form>
			<div style="font-family:sans-serif; font-size:13px; padding-top:8px;">e.g. the node you downloaded the distribution from</div>
		</div>
	</div>
</div>
END;
include($_SERVER['DOCUMENT_ROOT'].'/compile.php');
exit(0);
?>