<?php

$validating = isset($_GET['v']) ? $_GET['v'] : "";

$pgHead = <<<END
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

$pgBody = <<<END
$lanIPWarning 
<div id="outer">
	<div id="middle">
		<div id="inner">
			<div style="font-family:sans-serif; font-size:25px; padding-bottom:5px;">building...</div>
			<div style="font-family:sans-serif; font-size:11px; padding-top:5px;">(please wait while the server zips a big file)</div>
			<script type="text/javascript">
			window.location = "/utilities/build.php?v=$validating"
			</script>
			
		</div>
	</div>
</div>
END;

include($_SERVER['DOCUMENT_ROOT'].'/compile.php');


//header( "Location: /utilities/build.php" ) ;

?>