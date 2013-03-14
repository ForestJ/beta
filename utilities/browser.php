<?php

$serverIP = trim($_SERVER['SERVER_NAME']);
$remoteIP = trim($_SERVER['REMOTE_ADDR']);
$remoteIsServer = ($serverIP == $remoteIP || $serverIP == "localhost");

if($remoteIsServer || $_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['SERVER_NAME'] == "localhost") {
	
	include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/includedFunctions.php');
	include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/databaseConnection.php');
	
	$docRoot = "https://" . $_SERVER['SERVER_NAME'];
	$thisScript = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER[REQUEST_URI];
	
	$path = isset($_GET['p']) ? $_GET['p'] : "";
	$check = isset($_GET['c']) ? $_GET['c'] : "";
	$checkName = isset($_GET['cn']) ? $_GET['cn'] : "";
	$newName = isset($_GET['n']) ? $_GET['n'] : "";
	$chmod = isset($_GET['chmod']) ? $_GET['chmod'] : "";
	
	if($check != "") {
		echo (is_dir($check) && is_readable($check) ? "ok" : "fail");
		exit(0);
	}
	
	if($checkName != "") {
		if($newName == "") {
			echo "<img src='img/error.png' style='position:relative; top:3px;'></img> name is empty";
			exit(0);
		}
		
		$newName  = preg_replace("/[^A-Za-z0-9\.\-\+\ \_]/", "", $newName);
		
		if($newName == "") {
			echo "<img src='img/error.png' style='position:relative; top:3px;'></img> name has no allowed characters";
			exit(0);
		}
		
		$result = mysql_query( "SELECT * FROM `bfs`.`realms` WHERE `realms`.`name` = '$newName'") or die('Query failed: ' . mysql_error());
		if(mysql_num_rows($result) != 0) {
			echo "<img src='img/error.png' style='position:relative; top:3px;'></img> name already exists";
			exit(0);
		}
		
		echo "ok";
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
			<!--failure-->
			apache doesn't have the permission to read "$selectedDir". <br>
			<a href="belnetchmod://com.belnet.AppleScript.PermissionsTool?path=$selectedDir" style="color:#1155ff">
			<img src='$docRoot/img/type_shell.png' style="position:relative; top:3px;"></img> launch a command line tool to fix it</a>
			<br><br>or<br><br>
			<a href='#' onclick="loadContentsBasedOnPath('$parentPath')" style="color:#1155ff">
			<img src='$docRoot/img/parent.gif'/ > return to the previous directory</a>
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


?>