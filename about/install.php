<?php
$pgHead .= <<<END
<style type="text/css">
	.head {
		font-family:sans-serif;
		font-weight:bold;
		font-size:19px;
		padding-bottom:6px;
	}
	.item {
		font-family:sans-serif;
		font-size:15px;
		display:inline;
	}
	
	.hotitem {
		font-family:sans-serif;
		font-size:13px;
		font-weight:bold;
		color:red;
	}
</style>
END;



$neutralContent = <<<END
<div style="border:1px solid #ff3333; background:#ff9966; height:60px; padding:10px; color:#ffffff;">
		<img style="float:left; margin-right:50px; " src="img/nerd.png"></img>
		<div class="head">nerd alert!</div>
		you are about to install server software. it is a finnicky process, so slow and methodical is the way to go.
	</div><br><br>
	
<center><div class="head">pick one:</div></center><br>
<table cellspacing="0" style="color:#555555; width:100%;">
<tr>
<td style="border-right:1px solid #888888; width:50%;"><center><div class="head"><a href="$getstr os=win">WINDOWS</a></div></center></td>
<td style="width:50%;"><center><div class="head"><a href="$getstr os=mac">MACINTOSH</a></div></center></td>
</tr></table><br><br><br>
END;

$winContentSimple = <<<END
<br>
<center><div class="head">WINDOWS</div></center>
<br>

<p><span class="hotitem">the setup script is very basic, so you have to follow these instructions to the letter for it to work</span></p><br>

<ul>

<li><span class="hotitem">#1 </span><div class="item"><a href="downloadBuild.php"> go get the distrobution</a></div>
	<ul>
	<li><p>Extract the whole zip file, e.g., to a folder on your desktop</p></li>
	<li><p>open the Windows folder</p></li>
	</ul>
</li>

<li><p><span class="hotitem">#3 after it installs, run "setup.bat"</span></p>

	<ul>
	<p>if setup.bat runs into problems, email belnetstatus@gmail.com with a screenshot of the console window</p>
	</ul>

</li>
</ul>

<p> otherwise, you will be guided through the rest of the setup.</p>
END;



$macContentSimple = <<<END
<br>
<center><div class="head">MACINTOSH</div></center>
<br>

<P><span class="hotitem">the setup script is very basic, so you have to follow these instructions to the letter for it to work</span></p><br>

<ul>
<li><span class="hotitem">#1 </span><div class="item"><a href="downloadBuild.php"> go get the distrobution</a></div>
<ul>
<li><p>Extract the whole zip file, e.g., to a folder on your desktop</p></li>
<li><p>open the Mac folder</p></li>
</ul>
</li>

<li><p><span class="hotitem">#3 run the Setup application</p></span>
	
	<ul><li><p>if Setup runs into problems, email belnetstatus@gmail.com with a screenshot of the terminal window</p></li></ul>

</li>

</ul>

<p> otherwise, you will be guided through the rest of the setup.</p>
END;



$osContent = (isset($_GET['os']) ? (($_GET['os'] == "win") ? $winContentSimple : $macContentSimple) : $neutralContent);

if(isset($_GET['os'])) $osContent .= <<<END

<div style="border-bottom:1px solid #999999; width:90%;">&nbsp;</div><br><br>



END;

$mainContent .= <<<END
	<div style="color:#555555; padding-left:80px; padding-right:80px;">
	
	
	
	$osContent
	
	</div>
END;




/*
$winContentFull = <<<END
<br>
<center><div class="head">WINDOWS MANUAL</div></center>
<br>
<p>I'm assuming you already downloaded the distrobution and opened it. If not, get it <a href="img/belnet_alpha.zip">here</a></p>

<p>I'm also assuming that if you already tried to install, you deleted the entire <b>C:\\xampp</b> folder</p>

<p>run "xampp-win32-1.7.7-usb-lite.exe" (thanks <a href="http://www.apachefriends.org/en/index.html">apache friends</a>)</p>

<p>install in the default location (<b>C:\\xampp</b>)</p>

<p>ignore any warnings</p>

<p>after it installs, open <b>C:\\xampp\\xampp-control.exe</b> and press both start buttons</p>

<p>open a new browser tab and type "localhost" into the address bar. choose english, then click on the "security" link on the left hand side.</p>

<p>click the link marked "Make XAMPP more safety then use this =>".</p>

<p>enter a password in the top fields, then press the "password changing" button.</p>

<p>then enter a name and password in the bottom two fields and press the "Make safe the XAMPP directory" button.</p>

<p>close browser. navigate to the folder <b>C:\\xampp</b> in windows. you should see a folder called <b>htdocs</b>. delete it and replace it with the <b>htdocs</b> folder in the distrobution.</p>

<p>open the file <b>C:\\xampp\\htdocs\\includes/databaseConnection.php</b> in a text editor. On the first line, replace "CHANGEME" with whatever password you entered earlier. save the file.</p>

<p>open the file <b>C:\\xampp\\php\\php.ini</b>, search for the line ";extension=php_curl.dll". remove the semicolon. replace "memory_limit = 128M" with "memory_limit = 1024M". replace "error_reporting = E_ALL | E_STRICT" with "error_reporting = E_ALL & ~E_NOTICE" and save the file.</p>

<p>open the xampp control panel application again and stop, then start apache.</p>

<p>open the browser again, this time enter "https://localhost" in the address bar. the http<b>S</b>:// is important. you will be guided through the rest of the setup.</p>

<!--<p>move the <b>belnet_alpha.zip</b> file you downloaded to <b>C:\\xampp\\htdocs\\site\\</b></p>-->

<p>copy or move any files or folders you want to share to <b>C:\\xampp\\htdocs\\shared\\</b></p>

<p>copy <b>data\\xampp.bat</b> from the distrobution to <b>C:\\xampp\\</b></p>

<p>copy <b>data\\xamppstart.bat</b> from the distrobution to <b>C:\\Documents and Settings\\All Users\\Start Menu\\Programs\\Startup\\</b></p>
END;

$macContentFull = <<<END
<br>
<center><div class="head">MACINTOSH MANUAL</div></center>
<br>
<p>I'm assuming you already downloaded the distrobution and opened it. If not, get it <a href="img/belnet_alpha.zip">here</a></p>

<p>I'm also assuming that if you already tried to install, you stopped the xampp processes deleted the entire <b>/Applications/XAMPP</b> folder.</p>

<p>mount and open "xampp-macosx-1.7.3.dmg" (thanks <a href="http://www.apachefriends.org/en/index.html">apache friends</a>)</p>

<p>install xampp by dragging it to the applications folder alias given inside the dmg</p>

<p>open the Terminal (<b>/Applications/Utilities/Terminal</b>)</p>

<p>enter the command </p>

<p><pre>sudo /Applications/XAMPP/xamppfiles/xampp enablessl</pre></p>

<p>(you can paste copied text into the terminal by right-clicking it)</p>

<p>you will have to enter your user account password to execute this command.</p>

<p>now open <b>/Applications/XAMPP/XAMPP Control</b> and click the start button for both Apache and MySQL</p>

<p>open a web browser and type 'localhost' into the address bar. choose english, then click on the "security" link on the lefthand side</p>

<p><pre>sudo /Applications/XAMPP/xamppfiles/xampp security</pre></p>

<p>you will be prompted to answer either yes (y) or no (n), and to enter new passwords so you can secure the services xampp uses. </p>

<p>for simplicity i would recommend entering the same password every time you're prompted. just make sure its different from your other passwords, and its not short or something anyone could guess.</p>

<ul>
<li><b>y</b> to "Your XAMPP pages are NOT secured ... set a password? [ja]"</li>
<li>enter password</li>
<li><b>n</b> to "MySQL is accessable via network ... Normaly [ja]"</li>
<li><b>y</b> to "MySQL/phpMyAdmin user pma ... Do [ja]"</li>
<li>enter password</li>
<li><b>y</b> to "MySQL has no root passwort set ... Do [ja]"</li>
<li>enter password</li>
<li><b>n</b> to "ProFTPD has a new FTP password ... change anyway? [nein] "</li>
</ul>

<p>when it finishes, execute the same command again.</p>

<p><pre>sudo /Applications/XAMPP/xamppfiles/xampp security</pre></p>

<p>(you can press the up arrow to reload the last command)</p>

<p>this time answer:</p>

<ul>
<li><b>n</b> to "Your XAMPP pages are secured ... set a password? [nein]"</li>
<li>enter password</li>
<li><b>y</b> to "MySQL is accessable via network ... Normaly [ja]"</li>
<li><b>n</b> to "ProFTPD has a new FTP password ... change anyway? [nein] "</li>
</ul>

<p>go back to your web browser and reload the security page. right away you should be prompted to enter the password you just entered. everything should be marked "secured"</p>

<p>now navigate to <b>/Applications/XAMPP/xamppfiles/</b> in the finder. you should see a folder called <b>htdocs</b>. move it to the trash can, and replace it with the <b>htdocs</b> folder from the distrobution.</p>

<p>open the file <b>/Applications/XAMPP/xamppfiles/htdocs/includes/databaseConnection.php</b> in a text editor. On the first line, replace "CHANGEME" with whatever password you entered earlier. save the file.</p>

<p>go back to your web browser. this time type "https://localhost" in the address bar. the http<b>S</b>:// is important. you will be guided through the rest of the setup.</p>

<!--<p>move the <b>belnet_alpha.zip</b> file you downloaded to <b>/Applications/XAMPP/xamppfiles/htdocs/img/</b></p>-->

<p>copy or move any files or folders you want to share to <b>/Applications/XAMPP/xamppfiles/htdocs/shared/</b></p>

<p>copy <b>data/belnet_autostart</b> and <b>data/belnet_autostart.sh</b> to <b>/Applications/XAMPP/xamppfiles/</b></p>

<p>open System Preferences, choose the Users section, chose your user account, and click on the Login Items tab. click on the [+] button and select <b>/Applications/XAMPP/xamppfiles/belnet_autostart</b></p>

END;
*/
?>