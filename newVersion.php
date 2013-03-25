<?php

//echo $version . "  " . $userid;

if($dbversion != $version) {	
	// set by includes/version.php
	if($dbversion < $lastDatabaseReboot) {
		getDatabase("reset");
	}
	
	$q = "UPDATE `bfs`.`user` SET `version` =  '$version' WHERE `user`.`id` = '$userid';";
	mysql_query($q) or die('Query failed: ' . mysql_error());
	$q = "UPDATE `bfs`.`user` SET `updated` =  'true' WHERE `user`.`id` = '$userid';";
	mysql_query($q) or die('Query failed: ' . mysql_error());
} else {
	$q = "UPDATE `bfs`.`user` SET `updated` =  'false' WHERE `user`.`id` = '$userid';";
	mysql_query($q) or die('Query failed: ' . mysql_error());
}

$pgHead .= <<<END
<style type="text/css">
	#outer {height: 400px; overflow: hidden; position: relative; width: 100%;}
	#outer[id] {display: table; position: static;}
	
	#middle {position: absolute; top: 50%; width: 100%; text-align: center;} /* for explorer only*/
	#middle[id] {display: table-cell; vertical-align: middle; position: static;}
	
	#inner {position: relative; top: -50%; text-align: center;} /* for explorer only */
	#inner {width: 500px; margin-left: auto; margin-right: auto;} /* for all browsers*/
</style>
END;

$pgBody .= <<<END
$lanIPWarning 
<div id="outer">
	<div id="middle">
		<div id="inner">
			<div style="font-family:sans-serif; font-size:25px; padding-bottom:5px;">you've been updated to the latest version!</div>
			<div style="font-family:sans-serif; font-size:11px; padding-top:5px; width:400px; margin-left:50px; background:#fefefe; text-align:left;">
			What's new:
			<ul>
				<li>v0.1
				<ul><li>pre-alpha release</li></ul>
				</li>
				
				<li>v0.2
				<ul><li>first ever alpha release, test of auto-update system</li></ul>
				</li>
				
				<li>v0.3
				<ul><li>second test of auto-update system</li></ul>
				</li>
				
				<li>v0.51
				<ul><li>third test of auto-update</li></ul>
				</li>
				
				<li>v0.6
				<ul><li>post-update operation so that the updater, install scripts, and others can be updated</li></ul>
				</li>
				
				<li>v0.7
				<ul><li>3rd party server for active node list and automated update verification</li></ul>
				</li>
				
				<li>v0.75
				<ul><li>dramatically simplified the auto-update process and fixed a ton of bugs</li></ul>
				</li>
				
				<li>v0.8
				<ul><li>usability improvements, introduced multiple shared folders</li></ul>
				</li>
		
				<li>v0.87
				<ul><li>launch daemon (launchd) start on osx; GUI shared folder chooser</li></ul>
				</li>
		
				<li>v0.91
				<ul><li>development mode / test network</li></ul>
				</li>
	
			</ul>
			</div>
			<form method="post">
			<input type="submit" style="width:100px" class="input"  value="ok" />
			</form>
		</div>
	</div>
</div>
END;

include($_SERVER['DOCUMENT_ROOT'].'/compile.php');
exit(0);
?>