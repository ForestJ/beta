<?php
if($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']) exit(0);

$lanIPWarning = ($lanIP == "127.0.0.1" ? "<p style='color:red; font-weight:bold; font-size:14px;'>WARNING: unable to determine your computer's LAN address. are you connected to the internet?</p><br><br>" : "");

$username = isset($_POST['username']) ? $_POST['username'] : "";
if($username != "") {
	$username = preg_replace("/[^A-Za-z0-9\.\-\+\ \_]/", "", $username);
	$rand = dechex(mt_rand());
	$q = "INSERT INTO `bfs`.`user` (`id`, `name`, `ip`, `time`, `version`, `updated`) VALUES ('" . $rand . "', '" . $username . "', '" . $lanIP . "', '0', '" . $version . "', 'first');";
	mysql_query($q) or die('Query failed: ' . mysql_error());
	$q = "INSERT INTO `bfs`.`nodes` (`id`, `name`, `ip`) VALUES ('" . $rand . "', '" . $username . "', '$lanIP');";
	mysql_query($q) or die('Query failed: ' . mysql_error());
	$q = "INSERT INTO `bfs`.`ips` (`ip`) VALUES ('$lanIP');";
	mysql_query($q) or die('Query failed: ' . mysql_error());
	header( 'Location: ' . "https://".$lanIP."/" ) ;
	exit(0);
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
			<div style="font-family:sans-serif; font-size:25px; padding-bottom:5px;">name ur node</div>
			<form method="post">
			<input type="text" class="input" style="width:100px;" name="username" maxlength='5' />
			<input type="submit" class="input"  value="go" />
			</form>
			<div style="font-family:sans-serif; font-size:11px; padding-top:5px;">(5 character limit)</div>
		</div>
	</div>
</div>
END;

include($_SERVER['DOCUMENT_ROOT'].'/compile.php');
exit(0);
?>