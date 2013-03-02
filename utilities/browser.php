<?php

$serverIP = trim($_SERVER['SERVER_NAME']);
$remoteIP = trim($_SERVER['REMOTE_ADDR']);
$remoteIsServer = ($serverIP == $remoteIP || $serverIP == "localhost");

if($remoteIsServer || $_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['SERVER_NAME'] == "localhost") {
	
	include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/includedFunctions.php');
	
	$docRoot = "https://" . $_SERVER['SERVER_NAME'];
	$thisScript = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER[REQUEST_URI];
	
	$path = isset($_GET['p']) ? $_GET['p'] : "";
	$check = isset($_GET['c']) ? $_GET['c'] : "";
	
	if($check != "") {
		echo (is_dir($check) && is_readable($check) ? "ok" : "fail");
		exit(0);
	}
	
	if($path != "") {
		$path = str_replace("//", "/", $path);
		
		$pos = strrpos($path, "/");
		$parentPath = substr($path, 0, $pos);
		$selectedDir = substr($path, $pos+1, (strlen($path)-($pos+1)));
		if($parentPath == "") $parentPath = "/";
		$parentDisplayMode = ($path == "/" ? "none" : "block");
		
		if(!is_dir($path)) {
			echo "invalid directory";
		} else if(!is_readable($path)) {
			echo <<<END
			<div style='padding:3px; width:100%; background:#eeeeee;'>
			<span id="dialogpath">$path</span>
			</div><br>
			
			apache doesn't have the permission to read "$selectedDir". <br>
			assuming you are on a Mac, you need to do a "get info" on it and change its permissions.<br><br>
			<a href='#' onclick="loadContentsBasedOnPath('$parentPath')" style="color:#1155ff"><img src='$docRoot/img/parent.gif'/ > click here</a>
			 to return to the previous directory.
END;
		} else {
			
			$dirs = array();
			$files = array();
			
			$temp = scandir($path);
			foreach($temp as $value) {
				if($value === '.' || $value === '..') {
					continue;
				}
				if(is_file("$path/$value")) {
					$files["$path/$value"] = $value;
				} else if(is_dir("$path/$value")) {
					$dirs["$path/$value"] = $value;
				}
			}
			
			$contents = <<<END
				<table><tr><td>
				<div style='padding:3px; width:100%; background:#eeeeee; display:$parentDisplayMode'>
					 <a href='#' onclick="loadContentsBasedOnPath('$parentPath')"><img src='$docRoot/img/parent.gif'/ ></a>		
				</div>
				</td><td style='padding-left:30px;'>
				<div style='padding:3px; width:100%; background:#eeeeee;'>
					<span id="dialogpath">$path</span>
				</div>	
				</td></tr></table>

				<div style='height: 260px; width:100%;  border: 1px solid #777777;'>
				<div style='height: 100%; width:100%; overflow: auto;'>
				<table style='width:100%;'>
END;
			
			foreach($dirs as $key => $value) {
				$contents .= "<tr style='width:100%;'><td class='dir' style='width:100%;'>"
						  .  "<img class='icon1' src='$docRoot/img/type_folder.png'/><span>".$value . "</span>"
						  .  "</td></tr>";
			}
			$contents .= "</table>";
			foreach($files as $key => $value) {
				$contents .= "<span style='filter: alpha(opacity=50); opacity: 0.50;'>"
						  .  "<img class='icon1' src='$docRoot/img/".getTypeIconByFileName($value)."'/> ".$value
						  .  "</span><br>";
			}	

			
			$contents .= <<<END
			</div>
			</div>
					
			<div id="shareholder" style='padding:3px; width:100%; background:#eeeeee; display:none;'>
			<span id="shareinfo">share: <img class='icon1' src='$docRoot/img/type_folder.png'/></span><span id="sharepath"></span>
			</div>
			
END;
			
			echo $contents;
		}
	}

}
/*
				// get permissions in 0777 format
				$perms = substr(decoct(fileperms($path)),1);
				// check if it has no group.
				if($perms[2]."a" === "0a") {
					// make group readable and change to our group.
					$perms = $perms[0].$perms[1].($perms[2]+4).$perms[3];
					chgrp($path, filegroup($_SERVER['DOCUMENT_ROOT']));
				} else {
					// make readable to everyone
					$perms = $perms[0].$perms[1].$perms[2].($perms[3]+4);
				}
				chmod($path, $perms);
 */

?>