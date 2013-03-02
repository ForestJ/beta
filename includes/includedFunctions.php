<?php
$timeout = 3;

function implode_curl_multi($delimiter, $nodes) {
	return implode_curl_multi_opt ($delimiter, $nodes, false);
}

function implode_curl_multi_with_null($delimiter, $nodes) {
	return implode_curl_multi_opt ($delimiter, $nodes, true);
}

function implode_curl_multi_opt ($delimiter, $nodes, $withNull) {
	global $timeout;
	$node_count = count($nodes);

	$curl_arr = array();
	$master = curl_multi_init();

	for($i = 0; $i < $node_count; $i++) {
		$url = $nodes[$i];
		$curl_arr[$i] = curl_init($url);
		curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_arr[$i], CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_arr[$i], CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($curl_arr[$i], CURLOPT_USERPWD, "user:turtyeah");
		curl_setopt($curl_arr[$i], CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
		curl_setopt($curl_arr[$i], CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_multi_add_handle($master, $curl_arr[$i]);
	}

	do {
		curl_multi_exec($master,$running);
	} while($running > 0);

	$res = "";
	for($i = 0; $i < $node_count; $i++) {
		$content = curl_multi_getcontent  ( $curl_arr[$i]  );
		//echo curl_getinfo($curl_arr[$i], CURLINFO_HTTP_CODE);
		if(curl_getinfo($curl_arr[$i], CURLINFO_HTTP_CODE) == 200 || $withNull)
			$res .= ((($res == "" || $content == "") && !$withNull) ? "" : $delimiter) . $content;
	}
	curl_multi_close($master);
	return $res;
}

function go_curl ($url, $timeout) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	curl_setopt($ch, CURLOPT_USERPWD, "user:turtyeah");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	
	$res = curl_exec($ch);
	
	//echo "CURLINFO_HTTP_CODE". curl_getinfo($ch, CURLINFO_HTTP_CODE) . "<br>";
	//echo "curl_errno" . curl_errno  (  $ch  );

	if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) $res = "";
	curl_close($ch); 
	return $res;
}

function ext_curl ($url, $timeout) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	
	$res = curl_exec($ch);
	
	//echo "CURLINFO_HTTP_CODE". curl_getinfo($ch, CURLINFO_HTTP_CODE) . "<br>";
	//echo "curl_errno" . curl_errno  (  $ch  );

	if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) $res = "";
	curl_close($ch); 
	return $res;
}

function security_md5_file ($path) {
	if(is_file($path)) {
		$md5 = md5(file_get_contents($path));
		//echo $md5." . " . $path . "<br>";
		return $md5;
	} else {
		return "";	
	}
}

//									USED VALUES:
//										   md5    distro
//										   build  update
function build_recurse($folder, $ziprefix, $mode, $mode2, &$zipFile = null, $subfolder = "/") {
	global $md5s;
    // we check if $folder has a slash at its end, if not, we append one
    $folder .= substr($folder, -1) == "/" ? "" : "/";
    $subfolder .= (substr($subfolder, -1) == "/" || $subfolder == "") ? "" : "/";
    // we start by going through all files in $folder
    $handle = opendir($folder);
    while ($f = readdir($handle)) {
        if (($f[0] != '.' || $f == '.htaccess' || $f == '.htpasswd') && $f != "nodePassword.php" && ($f != "nodePassword_temp.php" || $mode2 == "distro")) {
        	// 10485760b = 10MB
            if (is_file($folder . $f) && is_readable($folder . $f) && ($mode2 != "update" || filesize($folder . $f) < 10485760)) {
                // if we find a file, store it
                // if we have a subfolder, store it there
                if($mode == "md5") {
                	$md5s .= security_md5_file(realpath ($folder . $f));
                } else {
                	$zf = ($f == "nodePassword_temp.php" ? "nodePassword.php" : $f);
                	$zipFile->addFile($folder . $f, $ziprefix . $subfolder . $zf);
                }
                //echo "$mode2:"."file: ".$folder . $f." -> ". $ziprefix . $subfolder . $f."<br>";
            } elseif (is_dir($folder . $f) && $f[0] != '.') {
                // if we find a folder, create a folder in the zip
                if($mode != "md5") $zipFile->addEmptyDir($ziprefix . $subfolder.$f);
                //echo "$mode2:"."dir: ".$folder . $f." -> ".$ziprefix . $subfolder.$f."<br>";
                // and call the function again
                if($subfolder != "/" || ($f != "build" && $f != "temp_builds" && $f != "shared" && $f != "update")) {
                	build_recurse($folder . $f, $ziprefix, $mode, $mode2, $zipFile, $subfolder.$f );
                }
                //echo "4"."folderToZipRecurse(".$folder . $f.", \$zipFile, ". $subfolder.$f.");<br>";
            }
        }
    }
}

function my_get_cwd () {	
	$cwd = getcwd();
	//echo $cwd . "<br>";
	
	$htdocs = stristr($cwd, "htdocs", true);
	//echo $htdocs  . "<br>";
	$result = "";
	
	if($htdocs != false) {
		$result = $htdocs . "htdocs";
	} else {
		if(stristr($_SERVER['SERVER_SOFTWARE'], "win")) {
			$result = "C:/xampp/htdocs";
		} else {
			$result = "/Applications/XAMPP/xamppfiles/htdocs";
		}
	}
	return str_replace("\\", "/", $result);
}

function deleteDir($dirPath) {
    if (is_dir($dirPath)) {
	    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
	        $dirPath .= '/';
	    }
	    $handle = opendir($dirPath);
	    while ($f = readdir($handle)) {
	        if ($f != "." && $f != "..") {
		        if (is_dir($dirPath . $f)) {
		        	//echo "deleteDir: ". $dirPath . $f . "<br>";
		            deleteDir($dirPath . $f);
		        } else {
		        	//echo "unlink: ". $dirPath . $f . "<br>";
		            unlink($dirPath . $f);
		        }
	        }
	    }
	    //echo "rmdir: ".$dirPath . "<br>";
	    @rmdir($dirPath);
    }
}

function rebuildGetString () {
	$getstr = "?";
	$getstramp = "";
	$first = true;
	foreach($_GET as $key => $value) {
		if($key != 'netupdate' && $key != "manualconnect") {
			$getstr .= ($first ? "" : "&") . $key . "=" . $value;
			$getstramp = "&";
			$first = false;
		}
	}
	return $getstr.$getstramp;
}

function guessLanIPUsingShell () {
	$lanIP = "";
	if(stristr($_SERVER['SERVER_SOFTWARE'], "win")) {
		$ct = preg_match('/IPv4 Address. . . . . . . . . . . : (\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b)/', shell_exec("ipconfig"), $matches);
		if($ct > 0) $lanIP = $matches[1];
	} else {
		if(preg_match("/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/", shell_exec("ipconfig getifaddr en0"), $matches) != 0) {
			$lanIP = $matches[0];
		} else if(preg_match("/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/", shell_exec("ipconfig getifaddr en1"), $matches) != 0) {
			$lanIP = $matches[0];
		} else if(preg_match("/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/", shell_exec("ipconfig getifaddr en2"), $matches) != 0) {
			$lanIP = $matches[0];
		} else if(preg_match("/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/", shell_exec("ipconfig getifaddr en3"), $matches) != 0) {
			$lanIP = $matches[0];
		}
	}
	
	if(preg_match("/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/", $lanIP) == 0) {
		$lanIP = "127.0.0.1";
	}
	return $lanIP;
}

function getTypeIconByFileName ($name) {
	$typeIcon = "type_unknown.png";
	
	if(preg_match("/^.*\.(jpg|jpeg|png|gif)$/i", $name)) {
		$typeIcon = "type_img.png";
	} else if(preg_match("/^.*\.(mov|flv|m4v|wma|mpg|avi)$/i", $name)) {
		$typeIcon = "type_movie.png";
	} else if(preg_match("/^.*\.(mp3|wav|aiff|mp4|m4a|ogg|flac|wma)$/i", $name)) {
		$typeIcon = "type_music.png";
	} else if(preg_match("/^.*\.(pdf)$/i", $name)) {
		$typeIcon = "type_pdf.png";
	} else if(preg_match("/^.*\.(iso|dmg)$/i", $name)) {
		$typeIcon = "type_disk.png";
	} else if(preg_match("/^.*\.(zip|7z|bz2|tar|rar|sit)$/i", $name)) {
		$typeIcon = "type_zip.png";
	} else if(preg_match("/^.*\.(exe|bat|com)$/i", $name)) {
		$typeIcon = "type_app.png";
	}
	return $typeIcon;
}

function hasIP ($str) {
	return preg_match('/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/', $str);
}
?>
