<?php
$pageContents = <<< EOPAGE
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html lang="en" xml:lang="en">
<head>
<title>$pgTitle</title>

<script type="text/javascript" src="modernizer.js"></script>

$pgHead
</head>
<body>
$pgBody

$pgFoot
</body>
</html>
EOPAGE;
echo $pageContents;
?>