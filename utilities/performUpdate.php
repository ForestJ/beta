<?php

include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/databaseConnection.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/includedFunctions.php');

$totalSeen = 0;
$totalTouched = 0;

$cwd = my_get_cwd();

if(isset($_GET['ip'])) {
	$ip = $_GET['ip'];
	
	$updatefile = go_curl("https://$ip/utilities/build.php?update=t",10);
	
	$zipPath = $cwd."/temp_builds/cur_update.zip";
	@file_put_contents($zipPath, $updatefile) or die("failed to write zip file");
	
	if(unzip($zipPath, $cwd."/temp_update/")) {
		
		$md5s = "";
		build_recurse($cwd."/temp_update/htdocs/"		, ""	   , "md5", "update");
		build_recurse($cwd."/temp_update/htdocs/build/"  , "/build" , "md5", "update");
		$the_md5 = md5($md5s);
		
		echo "update with fingerprint: $the_md5 <br>";
		
		$verification = ext_curl("http://belnetstatus.blogspot.com/", 15);
		$new_veri = ext_curl("http://belnet-nodes.net84.net/?md5=$the_md5", 15);
		$old_veri = strstr($verification, $the_md5);
		echo "old_veri: $old_veri | new_veri: " . $new_veri . "<br>";
		if($old_veri || strstr($new_veri, "true")) {
			
			$target = substr($cwd, 0, strrpos($cwd, "/"));
			$target .= "/htdocs";
			
			$totalTouched = 0;
			$totalSeen = 0;
			perform_update_recurse( "", $cwd."/temp_update/htdocs", $target );
			
			deleteDir($cwd."/temp_update");
			@mkdir($cwd."/temp_update");
			
			$query = 'SELECT * FROM user';
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());
			$line = mysql_fetch_array($result, MYSQL_ASSOC);
			go_curl("https://" . $line['ip'] . "/", 5);
			
			echo " UPDATE HAPPENED: $totalTouched/$totalSeen files changed";
		} else {
			echo " hash check failed.";
			
			if(!strstr($verification, "belnet") && !strstr($verification, "status") && !strstr($verification, "admin")) {
				echo "<br><br> failed to verify.";	
			}
		}	
	} else {
		echo " failed to unzip";
	}
}

function perform_update_recurse ($dir, $origdir, $copydir) {
	global $totalTouched; global $totalSeen;
	$temp = scandir($origdir.$dir);
	foreach($temp as $value) {
        if($value === '.' || $value === '..') {
			continue;
		}
		$originalPath = $origdir."$dir/$value";
		$copyPath = $copydir."$dir/$value";
		
        if(is_file($originalPath)) {
			$totalSeen ++;
			//echo "file: $origdir"."$dir/$value -> $copydir"."$dir/$value <br>\n";
			if(!is_file($copyPath) || md5_file($originalPath) != md5_file($copyPath)) {
				$totalTouched ++;
				echo "file: $origdir"."$dir/$value -> $copydir"."$dir/$value <br>\n";
				unlink($copyPath);
				copy($originalPath, $copyPath);
				chmod($copyPath, 0750);
			}
		} else {
			//echo "folder: $origdir"."$dir/$value -> $copydir"."$dir/$value <br>\n";
			if(!is_dir($copyPath)) mkdir($copyPath);
			chmod($copyPath, 0750);
			perform_update_recurse("$dir/$value", $origdir, $copydir);
		}
    } 
}

function unzip($file, $path){
    $zip = zip_open($file);
    if(is_resource($zip)){
        $tree = "";
        while(($zip_entry = zip_read($zip)) !== false){
            //echo "Unpacking ".zip_entry_name($zip_entry)."\n";
            if(strpos(zip_entry_name($zip_entry), "/") !== false){
                $last = strrpos(zip_entry_name($zip_entry), "/");
                $dir = substr(zip_entry_name($zip_entry), 0, $last);
                $file = substr(zip_entry_name($zip_entry), strrpos(zip_entry_name($zip_entry), "/")+1);
                if(!is_dir($path.$dir)){
                	//echo "@mkdir($path.$dir, 0755, true)";
                    @mkdir($path.$dir, 0755, true) or die("Unable to create $path$dir\n");
                }
                if(strlen(trim($file)) > 0){
                	//echo "file_put_contents($path.$dir.\"/\".$file";
                    $return = @file_put_contents($path.$dir."/".$file, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
                    if($return === false){
                        die("Unable to write file $path$dir/$file\n");
                    }
                }
            }else{
            	//echo "file_put_contents($file,";
                file_put_contents($path.$file, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
            }
        }
    }else{
        //echo "Unable to open zip file\n";
        return false;
    }
    return true;
}
?>