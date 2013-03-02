<?php

include_once ($_SERVER['DOCUMENT_ROOT'].'/includes/includedFunctions.php');

$fmode = isset($_GET['m']) ? $_GET['m'] : 'path';
$path = isset($_GET['p']) ? $_GET['p'] : '';

$verbose = true;

$pathsArr = array();
$files = array();
foreach($filecache as $hash => $arr) {
	foreach($arr['hosts'] as $host => $realmAndFName) {
		$fStr = "/".$realmAndFName['realm'].$realmAndFName['fname'];
		if($verbose)  $verbage .= $hash . " " . $nodes[$host]['online'] . " " . $host . " " . $fStr. "<BR>";
		if($nodes[$host]['online'] == 1) {
			$files[$fStr]['onlinehosts'][] = $host; 
		}
		//echo "$fStr <br>";
		$files[$fStr]['hosts'][] = $host; 
		$files[$fStr]['hash'] = $hash;
		$files[$fStr]['size'] = $arr['size'];
		$pathsArr[$fStr] = $fStr;
	}
}

$error_message[0] = "Probably malformed path or permissions issue.";
$error_message[1] = "Uploaded file too large (load_max_filesize).";
$error_message[2] = "Uploaded file too large (MAX_FILE_SIZE).";
$error_message[3] = "File was only partially uploaded.";
$error_message[4] = "Choose a file to upload.";

//var_dump($_FILES);

$displayForm = "";
$hasUploaded = false;
$uploadError = "";
$cwd = my_get_cwd();

if(isset($_FILES['uploads']['name'])) {
	$num_files = count($_FILES['uploads']['name']);
	$upload_dir = $cwd."/shared".$path;

	for ($i=0; $i < $num_files; $i++) {
		$upload_file = $upload_dir . "/". basename($_FILES['uploads']['name'][$i]);
		if (@is_uploaded_file($_FILES['uploads']['tmp_name'][$i])) {
			if (@move_uploaded_file($_FILES['uploads']['tmp_name'][$i], $upload_file)) {
				$hasUploaded = true;
			} else {
				$uploadError.= "Unable to move file $upload_file:" . $error_message[$_FILES['uploads']['error'][$i]];
			}
		} else {
			$uploadError.= "$upload_file is invalid:" . $error_message[$_FILES['uploads']['error'][$i]];
		}   
	}

	if($hasUploaded) {
		header( "Location: ?t=fs&m=$fmode&p=$path& netupdate=t" ) ;
	}
}

$dirs = explodeTree($pathsArr, '/');

$fcontent = '';

$breadcrumb = 'belnet / <a href="?t=fs&m='.$fmode.'">shared</a> / ';
$pArr = explode('/', $path);
$cur = $dirs;
$pSum = '';
$parentDir = $pSum;
$thisDir = "shared";

foreach($pArr as $element) {
	if($element == "") continue;
	if(array_key_exists($element, $cur)) {
		$cur = $cur[$element];
	} else {
		break;
	}
	$parentDir = $pSum;
	$pSum .= '/' . $element;
	$breadcrumb .= '<a href="?m='.$fmode.'&p='.$pSum.'">'.$element.'</a> / ';
	$thisDir = $element;
}

$fcontent .= '<div style=" color:#555555; font-weight:bold;">'.$breadcrumb.'</div>';

$pSum .= '/'; $parentDir .= '/';
$absPath = $cwd.'/shared'.$pSum;
$displayFiles = array();
$displayFolders = array();
if($cur && count($cur) > 0) {
	foreach($cur as $key => $value) {
		if(!is_array($value)) {
			$displayFiles[$key] = $files[$pSum.$key];
			//echo "displayFiles[$key]" . "files[$pSum.$key] <br>";
		} else {
			$info = getFolderInfo($pSum.$key, $value);
			$displayFolders[$key] = array('path' => $pSum.$key, 'hosts' => $info['hosts'], 'size' => $info['size'], 'availiable' => $info['availiable'], 'total' => $info['total']);
		}
	}
}

$displayItems = array();

foreach($displayFolders as $key => $value) {
	$status = ($value['availiable'] > 0 ? ($value['availiable'] == $value['total'] ? "active" : "semi-active") : "inactive");
	$link = "?t=fs&m=".$fmode."&p=".$value['path'];
	$displayItems[] = array('status' => $status, 'name' => $key, 'link' => $link, 'popup' => "", 'download' => "", 'size' => $value['size'], 'type' => "", 'hosts' => $value['hosts']);
}

foreach($displayFiles as $key => $value) {
	$ct = (array_key_exists('onlinehosts', $value) ? count($value['onlinehosts']) : 0)-1;
	
	$randomHost = $ct > -1 ? mt_rand(0, $ct) : 0;
	$randIP = $ct > -1 ? $nodes[$value['onlinehosts'][$randomHost]]['ip'] : "";
	$status = $ct > -1 ? "active" : "inactive";
	$path1 = $pSum . rawurlencode($key);
	
	$force_dl = preg_match("/^.*\.(mp3|wav|aiff|mp4|m4a|ogg|flac|wma|mov|flv|m4v|wma|mpg|avi|jpg|jpeg|png|gif|pdf|txt|html|htm)$/i", $key) ? "" : "&dl=t";
	$baseLink = "https://".$randIP."/file.php?name=".rawurlencode($key)."&path=".$path1;
	
	$link = $ct > -1 ? ($baseLink.$force_dl) : "";
	$popup = $ct > -1 ? ($baseLink.$force_dl) : "";
	$download = $ct > -1 ? ($baseLink."&dl=t") : "";
	$displayItems[] = array('status' => $status, 'name' => $key, 'link' => $link, 'popup' => $popup, 'download' => $download, 'size' => $value['size'], 'type' => "", 'hosts' => $value['hosts']);
}

$uploadInput = <<<END
<input name='uploads[]' type=file multiple>
&nbsp;&nbsp;<input type="submit" value="Upload" />
&nbsp;&nbsp;&nbsp;&nbsp;<b>Note:</b> Please chose the correct folder before uploading.
END;

if($uploadError != "") {
	$uploadInput = $uploadError;
}

$displayForm .= <<< END
<tr> <td class='lastRow' colspan=3>
<div style="margin:3px; valign:top;" id="uploadform">

<form name="upload" enctype="multipart/form-data" action="?t=fs&m=$fmode&p=$path" method="POST">
<input type="hidden" name="MAX_FILE_SIZE" value="1073741824" />
<input type="hidden" name="uploading" value="1" />
<img src="img/upload.png"></img>
$uploadInput
</form>

</div>
</td>

<script type="text/javascript">
if(!Modernizr.input['multiple']) {
	document.getElementById('uploadform').innerHTML = "This upload form requires HTML 5. Go download a modern browser.";	
}
</script>

</tr>
END;

$hasUploaded = false;
$uploadError = "";

$filesHTML = '<table cellspacing="0" width="100%">';

if($pSum != '/') {
	$filesHTML = '<img src="img/parent.gif"></img> <a href="?t=fs&m='.$fmode.'&p='.$parentDir.'">parent folder</a><br><br>';
}

//$even = false;
$length = count($displayItems);
$i = 0;
foreach($displayItems as $item) {
	//$evenStr = ($even ? "Even" : "Odd");
	$rowStyle = ($i+1 < $length || true ? "row" : "lastRow");
	$filesHTML .= "<tr><td class='$rowStyle'><div class='rowInnerLeft'><img class='icon1' src='img/" . $item['status'] . ".png'/>";
	
	$typeIcon = getTypeIconByFileName($item['name']);
	
	if($typeIcon == "type_unknown.png") $typeIcon = ($item['download'] == "" ? "type_folder.png" : "type_unknown.png");
	
	$filesHTML .= "&nbsp;&nbsp;<img class='icon1' src='img/$typeIcon'>";
	
	if($item['link'] == "") {
		$filesHTML .= "&nbsp;&nbsp;" . $item['name'] . "</div></td><td class='$rowStyle" . $evenStr . "'><div class='rowInner'>";
	} else {
		$filesHTML .= "&nbsp;&nbsp;<a href='" . $item['link'] . "'>".$item['name']."</a></td><td class='$rowStyle'><div class='rowInner'>";
		if($item['download'] != "") {
			$filesHTML .= ' <a target="_blank" href="' . $item['download'] . '"><img class="icon" src="img/download.png"/></a>';
		}
		if($item['popup'] != "") {
			$filesHTML .= ' <a target="_blank" href="' . $item['popup'] . '"><img class="icon" src="img/popup.png"/></a>';
		}
	}
	
	$first = true;
	$filesHTML .= "</div></td><td class='$rowStyle'> <div class='rowInnerRight'>";
	foreach($item['hosts'] as $hosty) {
		if($nodes[$hosty]['online'] == 1)
			$filesHTML  .= ($first ? '' : ', ') . '<a href="https://'.$nodes[$hosty]['ip'].'/">'.$nodes[$hosty]['name'].'</a>';
		else
			$filesHTML  .= ($first ? '' : ', ') . $nodes[$hosty]['name'];
		$first = false;
	}
	$filesHTML .= '</div></td></tr>';
	//$even = !$even;
	$i ++;
}

$filesHTML .= $displayForm;
$filesHTML .= '</table>';

$fTab1Style = "tab1Off";
$fTab2Style = "tab1Off";
$fTab3Style = "tab1Off";

if($fmode == "path") {
	$fTab1Style = "tab1On";
} else if($fmode == "tag") {
	$fTab2Style = "tab1On";
} else {
	$fTab3Style = "tab1On";
}

$fcontent .= <<<END
<table border="0" cellspacing="0" style="width:100%"><tr>
<td valign="top" colspan="2" style="padding:10px; width:100%">
<br>
<!--
<div>

	<div style="float:left; width:60%; padding-bottom:10px;">
		<span style="color:#555555; font-size:14px; font-weight:bold;">folders in / $thisDir / &nbsp;</span>
	</div>
	<div style="float:right; display:block; width:40%; height:80px; text-align:right;">
	&nbsp;
	
	<a href="?t=fs&m=$fmode&p=$path&zipdownload=t">
			<img style="display:inline; position:relative; top:10px;" src="img/zipicon.png"></img>
			zip and download '$thisDir'
	</a>
	
	</div>
	
</div>

<div style="border-bottom:1px solid #bbbbbb; padding-bottom:15px;">
<div style="float:left; display:inline; color:#555555; font-size:14px; font-weight:bold; background:#ffffff; position:relative; top:2px;">
	files in / $thisDir / &nbsp;
</div>
</div>
-->
</td>
</tr>
<tr>
<td valign="top" style="padding-left:10px; width:45%" colspan="2">
$filesHTML
</td>
</tr></table>
END;



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
.icon {
	border:0px;
	position:relative;
	top:4px;
}
.icon1 {
	border:0px;
	position:relative;
	top:2px;
}
.row {
	background:#ffffff; 
	padding-top:3px; 
	padding-bottom:3px; 
	border-top:1px solid #aacacc;
}
.lastRow {
	background:#ffffff; 
	padding-top:3px; 
	padding-bottom:3px; 
	border-top:1px solid #aacacc;
	border-bottom:1px solid #aacacc;
}
.rowInner {
	padding-top:3px; 
	padding-bottom:3px; 
}
.rowInnerRight {
	margin-right:10px;
	text-align:right;
	padding-top:3px; 
	padding-bottom:3px; 
}
.rowInnerLeft {
	margin-left:10px; 
	padding-top:3px; 
	padding-bottom:3px; 
}

</style>
END;
$mainContent .= <<<END
	<table border="0" cellspacing="0" style="width:100%"><tr>
	<td class="$fTab1Style" style="border-left:1px solid #bbbbbb;"><a href="?t=fs&m=path">browse files</a></td>
	<td class="$fTab2Style"><!--<a href="?t=fs&m=tag">browse by tag</a>-->browse by tag (soon)</td>
	<td class="$fTab3Style"><!--<a href="?t=fs&m=tag">browse by tag</a>-->preview (soon)</td>
	<td style="border-bottom:1px solid #bbbbbb;">&nbsp;</td>
	</tr></table>
	<div style="border-left:1px solid #bbbbbb; border-bottom:1px solid #bbbbbb; border-right:1px solid #bbbbbb; padding:10px;">
		$fcontent
	</div>
END;

/*
<div style="color:#555555; font-size:16px;  font-weight:bold; padding:10px; float:left;">
	FILE SYSTEM
	</div>
	<div style="color:#555555; font-size:12px;  font-weight:bold; padding:10px; float:right;">
	<form action="">
	
	</form>
	</div>
*/

function explodeTree($array, $delimiter = '_', $baseval = false)
{
  if(!is_array($array)) return false;
  $splitRE   = '/' . preg_quote($delimiter, '/') . '/';
  $returnArr = array();
  foreach ($array as $key => $val) {
    // Get parent parts and the current leaf
    $parts  = preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
    $leafPart = array_pop($parts);
 
    // Build parent structure
    // Might be slow for really deep and large structures
    $parentArr = &$returnArr;
    foreach ($parts as $part) {
      if (!isset($parentArr[$part])) {
        $parentArr[$part] = array();
      } elseif (!is_array($parentArr[$part])) {
        if ($baseval) {
          $parentArr[$part] = array('__base_val' => $parentArr[$part]);
        } else {
          $parentArr[$part] = array();
        }
      }
      $parentArr = &$parentArr[$part];
    }
 
    // Add the final part to the structure
    if (empty($parentArr[$leafPart])) {
      $parentArr[$leafPart] = $val;
    } elseif ($baseval && is_array($parentArr[$leafPart])) {
      $parentArr[$leafPart]['__base_val'] = $val;
    }
  }
  return $returnArr;
}

function getFolderInfo ($path, $array) {
	global $nodes; global $files;
	$result = array('hosts' => array(), 'availiable' => 0, 'total' => 0, 'size' => 0);
	if($array && count($array) > 0) {
		foreach($array as $key => $value) {
			if(!is_array($value)) {
				$file = $files[$path."/".$key];
				$found = false;
				if($file['hosts']) {
					foreach($file['hosts'] as $hosty) {
						$result['hosts'][$hosty] = $hosty;
						if($nodes[$hosty]['online'] == 1) {
							$found = true;
						}
					}
				}
				$result['size'] += $file['size'];
				$result['total'] += 1;
				if($found) $result['availiable'] += 1;
				
			} else {
				$r1 = getFolderInfo($path."/".$key, $value);
				foreach($r1['hosts'] as $h) {
					$result['hosts'][$h] = $h;
				}
				$result['size'] += $r1['size'];
				$result['total'] += $r1['total'];
				$result['availiable'] += $r1['availiable'];
			}
		}
	}
	return $result;
}
?>

