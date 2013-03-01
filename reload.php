<?php
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
<script type="text/javascript">
function Reload() {
	window.location = "?reload=2";
}

function Start() {
	setTimeout(Reload, 100);
}

window.onload = Start;
</script>


END;
$pgBody .= <<<END

<div id="outer">
	<div id="middle">
		<div id="inner">
			<div style="font-family:sans-serif; font-size:25px; padding-bottom:5px;">updating network...</div>
		</div>
	</div>
</div>
END;
include($_SERVER['DOCUMENT_ROOT'].'/compile.php');
exit(0);
?>