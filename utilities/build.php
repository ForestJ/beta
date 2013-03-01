<?php
include_once($_SERVER['DOCUMENT_ROOT']."/includes/version.php"); // sets $version
include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/nodePassword.php');

include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/databaseConnection.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/includedFunctions.php');

$validating = isset($_GET['v']) ? $_GET['v'] : "";
$isUpdate = (isset($_GET['update']) && $_GET['update'] == "t");

$cwd = my_get_cwd();

$userip = "localhost";
$query = 'SELECT * FROM user';
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$line = mysql_fetch_array($result, MYSQL_ASSOC);
if($line != false) {
	$userip = $line['ip'];
}

$md5s = "";
$update_md5 = "";
$distro_md5 = "";

if(!$isUpdate || $validating != "") {
	$path = $cwd."/temp_builds/belnet_distro$version.zip";
	
	$pwPath = $cwd."/includes/nodePassword.php";
	$pwPath2 = $cwd."/includes/nodePassword_temp.php";
	if(is_file($pwPath)) {
		copy($pwPath, $pwPath2);
		$contents = file_get_contents($pwPath2);
		$contents = str_replace($nodePassword, "CHANGEME", $contents);
		file_put_contents($pwPath2, $contents);
	}
	
	$md5s = "";
	build_recurse($cwd			, "common/htdocs"	  , "md5", "distro");
	build_recurse($cwd."/build/Mac" , "Mac"			  , "md5", "distro");
	build_recurse($cwd."/build/Windows" , "Windows"	  , "md5", "distro");
	security_md5_file(realpath ($cwd. "/shared/.htaccess"));
	$distro_md5 = md5($md5s);
	if($validating != "") {
		echo "distro_md5: ". $distro_md5 . "<br><br>";
	}
	
	
	$query = "SELECT * FROM builds WHERE hash = '$distro_md5';";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	if(mysql_num_rows($result) == 0) {
		$z = new ZipArchive();
		$z->open($path, ZIPARCHIVE::OVERWRITE);
		build_recurse($cwd					, "common/htdocs" , "build", "distro", $z);
		build_recurse($cwd."/build/Mac" 	, "Mac"			  , "build", "distro", $z);
		build_recurse($cwd."/build/Windows" , "Windows"	  	  , "build", "distro", $z);
		
		$z->addEmptyDir("common/htdocs/shared");
		//if($validating != "") echo "3"."\$zipFile->addEmptyDir(common/htdocs/shared);<br>";
		$z->addFile(realpath ($cwd. "/shared/.htaccess"), "common/htdocs/shared/.htaccess");
		//if($validating != "") echo "1"."\$zipFile->addFile(".realpath ($cwd. "/shared/.htaccess").", /common/htdocs/shared/.htaccess);<br>";
		$z->close();
		
		$query = "INSERT INTO `bfs`.`builds` (`hash`, `name`) VALUES ( '$distro_md5' , '$path' );";
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());
		
		if($validating != "") {
			echo "distro: http://belnet-nodes.net84.net/?md5=$distro_md5&action=pub<br><br>";
		}
		ext_curl("http://belnet-nodes.net84.net/?md5=$distro_md5&action=pub", 10);
	}

	if($validating == "") {
		outputZip($path);
	}
} 
if($isUpdate || $validating != "") {
	$path = $cwd."/temp_builds/belnet_update.zip";
	
	$md5s = "";
	build_recurse($cwd			, "htdocs"	 	, "md5", "update");
	build_recurse($cwd."/build" , "htdocs/build", "md5", "update");
	security_md5_file(realpath ($cwd. "/shared/.htaccess"));
	$update_md5 = md5($md5s);
	if($validating != "") {
		echo "update_md5: ". $update_md5 . "<br><br>";
	}
	
	$query = "SELECT * FROM builds WHERE hash = '$update_md5';";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	if(mysql_num_rows($result) == 0) {
		$z2 = new ZipArchive();
		$z2->open($path, ZIPARCHIVE::OVERWRITE);
		if(!$z2) echo "bigg error!!";
		build_recurse($cwd			, "htdocs"	 	, "build", "update",  $z2);
		build_recurse($cwd."/build" , "htdocs/build", "build", "update",  $z2);
		
		$z2->addEmptyDir("htdocs/shared");
		//if($validating != "") echo "3"."\$zipFile->addEmptyDir(shared);<br>";
		$z2->addFile(realpath ($cwd. "/htdocs/shared/.htaccess"), "htdocs/shared/.htaccess");
		//if($validating != "") echo "1"."\$zipFile->addFile(".realpath ($cwd. "/htdocs/shared/.htaccess").", ". "htdocs/shared/.htaccess".");<br>";
		$z2->close();
		
		$query = "INSERT INTO `bfs`.`builds` (`hash`, `name`) VALUES ( '$update_md5' , '$path' );";
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());
		
		if($validating != "") {
			echo "update: http://belnet-nodes.net84.net/?md5=$update_md5&action=pub<br><br>";
		}
		ext_curl("http://belnet-nodes.net84.net/?md5=$update_md5&action=pub", 10);
	}
	
	if($validating == "") {
		outputZip($path);
	}
}

if($validating != "") {
	//header("Location: https://$userip/");
}



function outputZip ($path) {
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename='.substr($path, strrpos($path, "/")+1) );
    header('Content-Length: ' . filesize($path));
    readfile($path);	

}

 
?>