<?php

$errors = "";

$tab = isset($_GET['t']) ? $_GET['t'] : "fs";
$share = isset($_GET['share']) ? $_GET['share'] : "";

$showRightBar = true;
$adminDash = "";
$mainContent = "";
$rightContent = "";
$tab1Style = "tabOff";
$tab2Style = "tabOff";
$tab3Style = "tabOff";

if($remoteIsServer || $_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['SERVER_NAME'] == "localhost") {

$cwd = my_get_cwd();
$firstrun = "";
$shareHTML = "";
if($share != "" && $remoteIsServer) {
	include($_SERVER['DOCUMENT_ROOT'].'/share.php');
}

if($recentUpdate == "first") {
	include($_SERVER['DOCUMENT_ROOT'].'/firstrun.php');
}

$adminDash = <<<END
<div style="padding:10px; ">
	$firstrun
	$shareHTML
	<div style="display:inline;">
	<b>node administration toolbar:</b>&nbsp;
	<a href="$getstr netupdate=t">[update everything now]</a>, 
	<a href="?manualconnect=t">[manual connnect]</a>, 
	<a href="?cleandb=t">[database reset]</a>, 
	<a href="$getstr share=t">[edit shared folders]</a>
	</div> 
</div> 
END;
}
$developmentModeText = "";
if($network == "development") {
	$developmentModeText = "<div style='padding:6px; background:url(img/development_mode.jpg)'> "
			. "<span style='padding:3px; font-weight:bold; background:#122530; color:#ffbb80;'>Development Mode Activated - Using Test Network</span></div> ";
}

$rightContent .= "<table>";
foreach($nodes as $node) {
	$rightContent .= "<tr><td><img class='icon1' src='img/" . ($node['online'] == 1 ? "active" : "inactive") . ".png'/>";
	$rightContent .= ($node['online'] == 1 ? "<a href='https://".$node['ip']."/'><b>&nbsp;&nbsp;".$node['name'] ."</b></a>" : "&nbsp;&nbsp;<b>".$node['name']."</b>");
	$rightContent .= " </td><td style='color:#999999;'>(".$node['id'].")</td></tr>";
}
$rightContent .= "</table>";

if($tab == "bbs") {
	$tab3Style = "tabOn";
	//$mainContent = etc
	include ($_SERVER['DOCUMENT_ROOT']."/bbs.php");
} else if($tab == "about") {
	$tab2Style = "tabOn";
	$showRightBar = false;
	//$mainContent = etc
	include ($_SERVER['DOCUMENT_ROOT']."/about.php");
} else {
	$tab1Style = "tabOn";
	//$mainContent = etc
	include_once ($_SERVER['DOCUMENT_ROOT']."/filesystem.php");
	/*
	if(isset($_GET['file'])) {
		include ($_SERVER['DOCUMENT_ROOT']."/file.php");
	} else {
		include_once ($_SERVER['DOCUMENT_ROOT']."/filesystem.php");
	}
	*/
	
}

$mainWidth = ($showRightBar ? 80 : 99);
$rightComment1 = ($showRightBar ? "" : "<!--");
$rightComment2 = ($showRightBar ? "" : "-->");

$pgHead .= <<<END
<style type="text/css">
body {
	margin:0px;
	padding:0px;
	font-family:sans-serif; 
	font-size:13px;
}

a:link {color:#4477cc; text-decoration:none;}
a:visited {color:#4477cc; text-decoration:none;} 
a:hover {color:#4477cc; text-decoration:none;} 
a:active {color:#4477cc; text-decoration:none;} 

h2 {
	font-weight:bold; 
	font-size:14px;
	border-bottom:1px solid #bbbbbb;
	white-space:nowrap;
	padding-bottom:2px;
	width:90%;
	color:#444444;
}
.tabOff {
	padding:7px;
	font-weight:bold; 
	font-size:14px;
	border-left:1px solid #bbbbbb;
	border-bottom:1px solid #bbbbbb;
	background:#eeeeee;
	color:#666666;
}
.tabOn {
	padding:7px;
	font-weight:bold; 
	font-size:14px;
	border-left:1px solid #bbbbbb;
	background:#ffffff;
	color:#444444;
}
</style>
END;
$pgBody .= <<<END

<!--HEADER-->
<div style="background:#9359fa; border-bottom:1px solid #452775; color:#ffffff; font-size:18px; padding:5px; padding-bottom:7px; padding-top:7px;">
<!--SEARCH-->
<div style="position:absolute; top:0px left:0px; width:70%; text-align:center; font-size:14px; padding-top:3px">
	<a href="/?t=about&m=install" style="font-weight:bold; color:#ffffff;"> install now!</a>
	<!--
	<form method="post" style="display:inline; background:#ffffff; border:1px solid #bbbbbb;">
	<input type="text" style="position:relative; bottom:2px; background:#ffffff; border:0px;" name="ip" value=""/>
	<input type="submit" style="background:url(img/search.gif); background-repeat:no-repeat; margin-right:3px; border:0px;" value="&nbsp;" />
	</form> 
	-->
</div> 

<!--HEADER CONTENTS-->
<div style="display:inline;"><b>$title</b> <span style="font-size:12px;">v$version</span></div> 
<div style="display:inline; float:right; text-align:right; padding-top:3px; font-size:15px;">node info: $userip <b>``$user``</b> ($userid)</div> 
</div>

$developmentModeText

<div style="background:#f6ff72; border-bottom:1px solid #bbbbbb; border-top:1px solid #727630; color:#555555; font-size:14px; min-height:6px;">
$adminDash
</div>
<!--MAIN COLUMN-->
<div style="position:relative; float:left; border-right:1px solid #bbbbbb; margin:0px; width:$mainWidth%;">
	<table border="0" cellspacing="0" style="width:100%"><tr>
	<td class="$tab1Style" style="border-left:0px;"><a href="?t=fs">file system</a></td>
	<td class="$tab2Style"><a href="?t=about">about</a></td>
	<td class="$tab3Style"><!--<a href="?t=bbs">bulletin board system</a>--> bulletin board system (soon)</td>
	</tr></table><br>
<div style="padding:10px; padding-right:10px; padding-top:10px;">
	$mainContent
</div>
</div>

<!--RIGHT COLUMN-->
$rightComment1
<div style="position:relative; float:left; margin:0px; width:19%;">
<div style="padding:10px; padding-right:10px; padding-top:10px;">
	<div class="tabOn">nodes in network:</div>
	$rightContent
</div>
</div>
$rightComment2

<!--FOOTER-->
<div style="float:left; width:100%; background:#eeeeee; margin-top:20px; border-top:1px solid #bbbbbb; color:#555555; font-size:13px; ">
<div style="padding:5px; text-align:center;">
<br><br>
<div style="position:relative; bottom:45px;">
brought to you by &nbsp
<img border='0' style="position:relative; top:45px; opacity:0.6; filter:alpha(opacity=60);" src='img/daft.png'/>
&nbsp; &nbsp;at beloit college</div><br>
<!--
<span style="color:#aaaaaa;"><br>
i see billions of people and billions of years all leading up to us standing right here<br>
and if that that aint a miracle, i'm not a sinner, i'm a man trying to dam a river <br>
so we're not all just water under the bridge <br>
give me a lake where my sisters and brothers can swim<br>
- el guante<br></span>
-->


<br><br>
<div style="padding:5px; text-align:left;">
$verbage
</div>
END;

$pgFoot .= "</div></div>";

include($_SERVER['DOCUMENT_ROOT'].'/compile.php');
exit(0);


?>
*/