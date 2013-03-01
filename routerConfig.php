<?php
if($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']) exit(0);

$routerIP = "";

if(stristr($_SERVER['SERVER_SOFTWARE'], "win")) {
	$ct = preg_match('/Default Gateway . . . . . . . . . : (\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b)/', shell_exec("ipconfig"), $matches);
	if($ct > 0) $routerIP = $matches[1]; 
} else {
	$ct = preg_match('/(\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b)/', shell_exec("netstat -nr | grep '^default'"), $matches);
	if($ct > 0) $routerIP = $matches[1]; 
}

$routerLine = "";
$routerManual = "follow one of these links: <a href=\"https://www.google.com/search?q=how+to+find+my+router+ip+mac\" target=\"_blank\">mac</a>, <a href=\"https://www.google.com/search?q=how+to+find+my+router+ip+windows\" target=\"_blank\">windows</a>";
if($routerIP == "") {
	$routerLine = "automatic router detection failed. to find the address of your router, ";
	$routerManual .=  "<br><br> once you have found the ip, make a new tab and enter it in.";
} else {
	$routerLine = "automatic router detection found: <a style=\"font-weight:bold; font-size:15px;\" href=\"http://" . $routerIP . "/\" target=\"_blank\">" . $routerIP . "</a>.";
	$routerManual = "<br><br><span style='font-size:12px; color:#888888;'> (if this is incorrect, ".$routerManual.")</span>";
}

$pgBody .= <<<END


<div style="font-family:sans-serif; font-size:25px; padding-bottom:5px;">router configuration</div>

<div style="font-family:sans-serif; color:#333333; font-size:14px;">

<div style="float:left; border:1px solid #aaaaaa; padding:5px; text-align:center; margin-right:20px; ">
<img src="img/router.png"></img><br>
<div style="color:#888888; border-top:1px solid #dddddd; padding-top:4px; font-size:12px;">time to deal with this beast</div>
</div>

<br><br>

$routerLine 
$routerManual
<br><br>
on navigating to the router, you will be challenged by a password dialog. if you set a router admin username and password (this is different from your wireless network password), then enter it. if you didn't, it's probably something stupid left by the manufacturer, like <br><br>
<div style="border: 1px solid #888888; padding:2px;width:130px; float:right; margin-right:30%;">&nbsp;&nbsp;&nbsp;&nbsp;admin<br>
<div style="border-top: 1px solid #dddddd;">&nbsp;</div></div>
<br><br><br>
<div style="border: 1px solid #888888; padding:2px;width:130px; float:right; margin-right:30%;">
<div style="position:relative; right:50px; display:inline; ">or</div>
admin<br><div style="border-top: 1px solid #dddddd;">&nbsp;&nbsp;&nbsp;&nbsp;password</div></div>
<br><br><br>
<div style="border: 1px solid #888888; padding:2px;width:130px; float:right; margin-right:30%;">
<div style="position:relative; right:50px; display:inline;">or</div>
&nbsp;<br><div style="border-top: 1px solid #dddddd;">&nbsp;</div></div>
<br><br><br>
<div style="margin-left:10%;"> 
if you can't log in to your router, try finding out what model it is and googling "[model name] login". sometimes the model will be on the password dialog. It is usually something really cryptic like "WRT54Gv4". otherwise the model should be printed on the router itself. (it might also have the default username and password there).
<br>
<br>
<div style="border-top:1px solid #aaaaaa; margin-left:30px; margin-right:30px;"></div>
<br>
</div>
<div style="float:left; border:1px solid #aaaaaa; padding:5px; text-align:center; margin-right:20px; ">
<div style="color:#888888; padding-bottom:4px; font-size:12px;">you should see something 
like this:</div>
<img src="img/routerpage.jpg"></img><br>
</div>

once you have logged into your router's administration page, try to locate a link called port forwarding and click it.
<br><br>
you want to forward two services, that is, introduce two new forwarding rules:
<br><br>
<pre><b>HTTP  (port  80) -> $lanIP<br>
HTTPS (port 443) -> $lanIP</b></pre>
<br>
once this is done, <a style="font-weight:bold; font-size:15px;" href="?r=">click here</a> to reload this page, and you should be set!
<br><br>
<br><br>
<br><br>
<br><br>

END;
include($_SERVER['DOCUMENT_ROOT'].'/compile.php');
exit(0);
?>