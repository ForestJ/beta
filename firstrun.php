<?php

$q = "UPDATE `bfs`.`user` SET `updated` = 'false';";
mysql_query($q) or die("Query2 failed. " . mysql_error());

$firstrun = <<<END

<div style="margin:15px; padding: 10px; background:#ffffff; text-align:center;">

<p><b> Congrats, you have successfully installed your node! Here's what went down:</b></p>

<div style="text-align:left;">
<ul>
<li>you downloaded the belnet distrobution package</li><br>

<li>you ran a setup script which will did the following for you: </li><br>
<ul>
<li>install xampp, that is, cross-platform apache, mysql, php, and phpmyadmin. xampp is an easy to use server software bundle that we use. (it was created by <a href="http://www.apachefriends.org/en/index.html">apache friends</a>).</li><br>

<li>configure xampp and set passwords to make sure that it is secure (no one can exploit it to get into your computer)</li><br>

<li>enable xampp's usage of SSL and  cURL which are needed for belnet</li><br>

<li>put the belnet php pages inside your xampp, and configure the mysql connection with your new password</li><br>

<li>install an autostarter script that will run every time you log in</li><br>

<li>popped up a browser window where you set up your new node and configured your router as well if needed</li><br>

</ul>

<li>for your node to work, you will have to leave your computer on and connected to the campus network as much as possible.</li><br>
</ul>


<p>Here are some next steps:</p>

<p>
<ul>
<li><a href="$getstr share=t">share your files</a></li>
<li>check out some of the files we already have</li>
<li>tell your friends!</li>
</ul>
</p>

</div>
<p><img src="img/dudes.jpg"></img></p>

</div>

END;


?>