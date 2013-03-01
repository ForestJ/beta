<?php
$pgHead .= <<<END
<style type="text/css">
	.head {
		font-family:sans-serif;
		font-weight:bold;
		font-size:27px;
	}
	.c1 {
		width:100%;
		text-align: center;
	}
	.b1 {
		display:inline-block;
		padding: 5px;
	}
	.cap {
		font-family:sans-serif;
		font-size:12px;
		color:gray;
	}
</style>
END;
$mainContent .= <<<END
	<div style="color:#555555; padding-left:80px; padding-right:80px;">
	<br><br>
	<p>
	<div class="c1"><span style="font-size:30px;">welcome to beloit's very own underground network!</span><br></div>
	</p>
	<p>
	<div class="c1"><span style="font-size:15px;">our goal is to <b>provide a safe place to share files</b>, and also <b>reduce the load on our poor internet connection</b></span></div>
	</p>
	<div class="c1"><div class="b1">
	<img src="img/diagram1.jpg"></img>
	<p class="cap">the bottleneck that is slowing everything down is the connection between beloit and the rest of the internet</p>
	</div></div>
	<p>
	the idea is to make all kinds of files, music, and video availiable right here on campus. that way, we can increase traffic between computers locally and decrease the amount of traffic clogging up our internet connection. also, on-campus connections will be <b>lightning fast!</b>
	</p>
	<div class="c1"><div class="b1">
	<img src="img/diagram2.jpg"></img>
	<p class="cap">traffic between computers at beloit could relieve stress from our limited internet connection</p>
	</div></div>
	<p>
	things like this exist at other schools. they are great because they replace network-intensive stuff like torrents and netflix, which means
	better internet for everyone, as well as loads of content availiable all the time. 
	</p>
	<p>
	a local, secure system such as this is invisible to the outside world. this classifies it as part of the <b>deep web</b>, which makes it <b>safe for users</b>.
	</p>
	<p>
	during peak access times (after dinner), connections through this system should be <b>up to ten times faster</b> than normal internet connections. here some things you can do at warp speed:
	</p>
	
	<ul style="font-size:16px;">
		<li><a href="?t=about&m=upload">share files by uploading them to a node</a></li>
		<li><a href="?t=about&m=search">search the network for the stuff you want</a></li>
		<li><a href="?t=about&m=share">tell your friends!</a></li>
		<li><a href="?t=about&m=install">become a node and share files by installing software on your computer</a></li>
	</ul>
	<br>
	<div class="c1">
	<img src="img/dudes.jpg"></img>
	</div>
	<br>
	</div>
END;
?>