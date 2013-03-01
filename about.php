<?php

$fmode = isset($_GET['m']) ? $_GET['m'] : 'readme';
// test  
$fTab1Style = "tab1Off";
$fTab2Style = "tab1Off";
$fTab3Style = "tab1Off";
$fTab4Style = "tab1Off";
$fTab5Style = "tab1Off";

if($fmode == "upload") {
	$fTab2Style = "tab1On";
} else if($fmode == "search") {
	$fTab3Style = "tab1On";
} else if($fmode == "share") {
	$fTab4Style = "tab1On";
} else if($fmode == "install") {
	$fTab5Style = "tab1On";
} else {
	$fTab1Style = "tab1On";
}

$pgHead .= <<<END
<style type="text/css">
.tab1Off {
	padding:7px;
	font-weight:bold; 
	font-size:14px;
	border-top:1px solid #bbbbbb;
	border-bottom:1px solid #bbbbbb;
	border-right:1px solid #bbbbbb;
	background:#eeeeee;
	color:#666666;
}
.tab1On {
	padding:7px;
	font-weight:bold; 
	font-size:14px;
	border-top:1px solid #bbbbbb;
	border-right:1px solid #bbbbbb;
	background:#ffffff;
	color:#444444;
}
</style>
END;

$mainContent .= <<<END
	<table border="0" cellspacing="0" style="width:100%"><tr>
	<td class="$fTab1Style" style="border-left:1px solid #bbbbbb;"><a href="?t=about&m=readme">read me</a></td>
	<!--
	<td class="$fTab2Style"><a href="?t=about&m=upload">upload</a></td>
	<td class="$fTab3Style"><a href="?t=about&m=search">search</a></td>
	<td class="$fTab4Style"><a href="?t=about&m=up">share</a></td>
	-->
	<td class="$fTab5Style"><a href="?t=about&m=install">install</a></td>
	<td style="border-bottom:1px solid #bbbbbb;">&nbsp;</td>
	</tr></table>
	<div style="border-left:1px solid #bbbbbb; border-bottom:1px solid #bbbbbb; border-right:1px solid #bbbbbb; padding:10px;">
END;

include($_SERVER['DOCUMENT_ROOT'].'/about/'.$fmode.'.php');

$mainContent .= "</div>";

?>