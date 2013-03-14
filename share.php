<?php

$pgHead .= <<<END
<style type="text/css">
.leftcell {
	padding:7px;
	border-bottom: 1px solid #aaaaaa;
	border-right: 1px solid #aaaaaa;
}
.rightcell {
	padding:5px;
	border-bottom: 1px solid #aaaaaa;
}
</style>
END;

$hasForm = false;
$i = 0;
while(isset($_POST["realm$i"])) {
	if(isset($_POST["path$i"])) $hasForm = true;
	$i++;
}

if($hasForm) {
	mysql_query("DELETE FROM `bfs`.`realms` where 1 = 1") or die('Query failed0: ' . mysql_error());
	
	$i = 0;
	while(isset($_POST["realm$i"])) {
		$realmName = trim($_POST["realm$i"]);
		$realmPath = isset($_POST["path$i"]) ? trim($_POST["path$i"]) : "";
		if($realmPath != "") {
			$q = "INSERT INTO `bfs`.`realms` (`name`, `path`) VALUES ('$realmName', '$realmPath');";
			mysql_query($q) or die('Query failed: ' . mysql_error());
		}
		$i++;
	}
	
	go_curl("https://localhost/?netupdate=t", 15);
	
	header("location: $getstr");
	exit(0);
}


// make a list of requests from the current list of known nodes
// the requested pages give us a list of thier realms delimited by :
$urls = array();
$urlsHash = array();
foreach($ips as $ip) {
	if(hasIP($ip)) {
		$u = "https://" . $ip . "/utilities/realms.php";
		$urlsHash[$u] = $u;
	}
}
foreach($urlsHash as $key => $value) { 
	$urls[] = $value; 
}

// do the multi url curl request!!  
// realms separated by :, no per-node separation
$allrealms =  implode_curl_multi(":", $urls);
$realm_array = array();
$withRedundancies = explode(":", $allrealms);
foreach($withRedundancies as $r) {
	if(preg_match("/^\w/i", $r)) $realm_array[trim($r)] = $r;	
}

$marker = "___NODE";

$result = mysql_query( "SELECT * FROM `bfs`.`realms`") or die('Query failed: ' . mysql_error());	
while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$realm_array[trim($line['name'])] = $line['path'];
}

$shareHTML .= <<<END

	$includeJQueryBasic
	$includeJQueryUI

	<div id="loadingbar" style="display:none;">
		<div style="width:100%; text-align:center;">
		<div style="margin-left:auto; margin-right:auto; border-radius:5px; padding:5px; border:1px solid #888888; width:260px; background:white;">
		<div style="width:260px; height:20px; background:url(images/animated-overlay.gif); filter:alpha(opacity=25); opacity: 0.25;">&nbsp;</div>
		</div>
		</div>
	</div>
	
	<div id="pickname" style="display:none;">
		<div style="width:100%; text-align:center;">
		<input type="text" name="newname" value="" onchange="validateName(this.value);" onkeypress="this.onchange();" onpaste="this.onchange();" oninput="this.onchange();" /><br>
		<span style="font-size:0.8em; ">create a short name for your new shared folder. e.g. 'tv' or 'comics'</span>
		<div id="nameerror"></div>
		</div>
	</div>
	
	<div id="dialog-modal" title="browse..." style="display:none; font-size:0.8em; ">
	</div>
	
	<script>
	
	var picknameContents =  
	 '	<br><br><br><div style="width:100%; text-align:center;">'
	+'	<input type="text" id="newname" value="" onchange="validateName(this.value);" onkeypress="this.onchange();" onblur="this.onchange();" onpaste="this.onchange();" oninput="this.onchange();" /><br>'
	+'	<span style="font-size:0.8em; ">create a short name for your new shared folder. e.g. \'tv\' or \'comics\'<br>'
	+'	letters, numbers, and .-+ _ are allowed</span>'
	+'	<br><br><div id="name-error"></div>'
	+'	</div>';
	
	var permsInterval;
	var permsToCheck;
	var doubleClickInterval;
	var folderToDoubleClick;
	
		function dialog_choseName(i) {
			$( "#dialog-modal" ).dialog({
				height: 450,
				width: 600,
				modal: true
			});
			$( "#dialog-modal" ).dialog(
			"option", "buttons", 
			[ 
				{ text: "Cancel", click: function() { $( this ).dialog( "close" ); } },
				{ text: "Next >>", click: function() { 
						$('#displayrealm'+i).html($('#newname').attr("value"));
						$('#realm'+i).attr("value", $('#newname').attr("value"));
						dialog(i);
					}
				}
			]);
			
			$( "#dialog-modal" ).html(picknameContents);
			
			$('#newname').change(function() {
 				validateName($('#newname').attr("value"));
			});
			
			validateName("");
		}
	
		function validateName (name) {
			$.ajax({
				url: "utilities/browser.php?cn=t&n="+name
			}).done(function(data) {
				if(data == "ok") {
					$( "#name-error" ).html("");
					$(":button:contains('Next')").prop("disabled", false).removeClass("ui-state-disabled");
				} else {
					$( "#name-error" ).html(data);
					$(":button:contains('Next')").prop("disabled", true).addClass("ui-state-disabled");
				}
			});
		}
		
		function dialog(i) {
			$( "#dialog-modal" ).dialog({
				height: 450,
				width: 600,
				modal: true
			});
			$( "#dialog-modal" ).dialog(
			"option", "buttons", 
			[ 
				{ text: "Cancel", click: function() { $( this ).dialog( "close" ); } },
				{ text: "Share", click: function() { 
						$('#displaypath'+i).html($('#sharepath').html());
						$('#path'+i).attr("value", $('#sharepath').html());
						$('#shareform').submit();
						$( "#dialog-modal" ).dialog({
							title: "please wait...",
							height: 150,
							width: 600,
							modal: true
						});
						$( "#dialog-modal" ).dialog( "option", "buttons",  [ ]); 
						$( "#dialog-modal" ).html($("#loadingbar").html());
					}
				}
			]);
			
			//alert("utilities/browser.php?p="+$( "#path"+i ).attr("value"));
			var p = $( "#path"+i ).attr("value");
			if(p == "") p = "/"
			
			loadContentsBasedOnPath(p);
		}
		
		function loadContentsBasedOnPath(path) {
			$( "#dialog-modal" ).html($("#loadingbar").html());
		
			$.ajax({
				url: "utilities/browser.php?p="+path,
				context: $( "#dialog-modal" )
			}).done(function(data) {
				$( "#dialog-modal" ).html(data);
				initBrowser();
				if(data.indexOf("<!--failure-->") == -1) {
					clearInterval(doubleClickInterval);
					//alert("clearInterval");
				} else {
					startTryingDoubleClick($('#dialogpath').html());
				}
			});
		}
		
		function selectDir (tdElement) {
			$.ajax({
				url: "utilities/browser.php?c="+$('#dialogpath').html()+'/'+tdElement.contents("span").html(),
				context: tdElement
			}).done(function(data) {
				if(data == "ok") {
					dirSelectedOk($(this));
				} else {
					dirSelectedError($(this));
				}
			});
		}
			
		function dirSelectedOk(tdElement) {
			$('#shareholder').css('display', 'block');
			$('#sharepath').html($('#dialogpath').html()+'/'+tdElement.contents("span").html());
			$('#shareinfo').html(" ");
			
			// reset all dirs to default color
			$("td.dir").css("background", "#ffffff");
			$("td.dir").css("color", "#000000");
			
			// highlight the dir they clicked on
			tdElement.css("background", "#1155ff");
			tdElement.css("color", "#ffffff");
			
			clearInterval(permsInterval);
			
			// enable share button
			$(":button:contains('Share')").prop("disabled", false).removeClass("ui-state-disabled");
		}
		
		function dirSelectedError(tdElement) {
			$('#shareholder').css('display', 'block');
			$('#sharepath').html('');
			$('#shareinfo').html('"'+tdElement.contents("span").html()+'" is not accessible by apache.<br>'
									+'you can <a href="belnetchmod://com.belnet.AppleScript.PermissionsTool?path='
									+$('#dialogpath').html()+'/'+tdElement.contents("span").html()+'" style="color:#1155ff;">'
									+'<img src="$docRoot/img/type_shell.png" style="position:relative; top:3px;"></img> launch a command line tool to fix it</a>');
			
			// reset all dirs to default color
			$("td.dir").css("background", "#ffffff");
			$("td.dir").css("color", "#000000");
			
			// highlight the dir they clicked on
			tdElement.css("background", "#ff5511");
			tdElement.css("color", "#ffffff");
			
			// disable share button
			$(":button:contains('Share')").prop("disabled", true).addClass("ui-state-disabled");
			
			clearInterval(permsInterval);
			permsToCheck = tdElement;
			permsInterval = setInterval(function(){recheckPerms()},1000);

		}
		
		function startTryingDoubleClick(path) {
			//alert("startTryingDoubleClick");
			clearInterval(doubleClickInterval);
			folderToDoubleClick = path;
			doubleClickInterval = setInterval(function(){recheckDoubleClick()},3000);
		}
		
		function recheckDoubleClick() {
			loadContentsBasedOnPath(folderToDoubleClick);
			//alert(folderToDoubleClick);
		}
		
		function recheckPerms() {
			selectDir(permsToCheck);
		}
		
		function initBrowser () {
			// disable share button
			$(":button:contains('Share')").prop("disabled", true).addClass("ui-state-disabled");
			$('#shareinfo').html(" ");
		
			// for every directory
			$("td.dir").css( 'cursor', 'pointer' );
			$("td.dir").click( function () { selectDir($(this)); });
			$("td.dir").dblclick( function () { loadContentsBasedOnPath($('#dialogpath').html()+'/'+$(this).contents("span").html()); });
		}
	</script>

	<div style="margin:15px; padding: 10px; background:#ffffff; ">
	<a style="float:right; margin:5px; padding: 3px 0px 0px 2px; border:2px dashed #dddddd;" href="?t=fs"><img border="0" src="img/close.png"></img></a>
	<form name="sharepath" id='shareform' enctype="multipart/form-data" action="$getstr" method="POST">
	<table cellspacing="0">
	<tr><td></td><td colspan="2" style="padding:7px;"><b>edit shared folders</b></td></tr>
	<tr><td></td><td class="leftcell">name</td>
	<td class="rightcell">path to folder</td>
END;

$i = 0;
foreach($realm_array as $rname => $rpath) {
	// $rname == $rpath means this this node does not yet definition for this realm.
	if($rname == $rpath) $rpath = "";
	$shareHTML .= <<<END
		<tr><td></td><td  class="leftcell"><input type="hidden" name="realm$i" value="$rname" />$rname</td>
		<td class="rightcell">
			
		<a href="#" id="button$i" onclick="dialog($i);" style="color:#444444;">
		<div style='padding:3px; width:550px; background:#eeeeee;'><div id="displaypath$i">$rpath
		<div style='padding:3px; border:1px solid #555555; border-top:0px; padding-top:2px; float:right;  background:#ffffff;'>Browse...</div>
		</div>
		<input type="hidden" id="path$i" name="path$i" value="$rpath" />
		</div> 
		</a>
				
		</td></tr>
END;
	
	$i++;
}

$shareHTML .= <<<END

	<tr><td style="padding:7px;"> add:<br>&nbsp;</td>
	<td  class="leftcell"><input type="hidden" name="realm$i" value="" />&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td class="rightcell">
			
		<a href="#" id="button$i" onclick="dialog_choseName($i);" style="color:#444444;">
		<div style='padding:3px; width:550px; background:#eeeeee;'><div id="displaypath$i">&nbsp;&nbsp;&nbsp;
		<div style='padding:3px; border:1px solid #555555; border-top:0px; padding-top:2px; float:right;  background:#ffffff;'>Browse...</div>
		</div>
		<input type="hidden" id="path$i" name="path$i" value="" />
		</div> 
		</a>
				
		</td></tr>
	
	<tr><td></td><td colspan="2" style="padding:7px; text-align:right;"><input type="submit" value="Update Shared Folders" /></td></tr>
	</table>
	</form>
	</div>
END;



?>