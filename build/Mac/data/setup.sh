#!/bin/sh

#find the directory we are running this script from
DIR=`dirname "$0"`

echo "running from: $DIR"

echo "      	 "
echo "             *MM                \`7MM                       mm   "
echo "              MM                  MM                       MM   "
echo "              MM,dMMb.   .gP\"Ya   MM  \`7MMpMMMb.  .gP\"Ya mmMMmm" 
echo "              MM    \`Mb ,M'   Yb  MM    MM    MM ,M'   Yb  MM   "
echo "              MM     M8 8M\"\"\"\"\"\"  MM    MM    MM 8M\"\"\"\"\"\"  MM   "
echo "              MM.   ,M9 YM.    ,  MM    MM    MM YM.    ,  MM   "
echo "              P^YbmdP'   \`Mbmmd'.JMML..JMML  JMML.\`Mbmmd'  \`Mbmo"
echo "     	 "


echo "          "
echo "           enter admin password. (this is your OSX login password)"
echo "          "
sudo echo "          "

echo ""
echo "please enter a new password to be used internally by xampp."
echo "this is required so that no one can exploit xampp to get into your computer."
echo ""
echo "after you enter this password, the script will install xampp and set up belnet."
echo ""
echo "at the end, you will be prompted to complete setup in your web-browser."
echo ""
printf "new password:"

read -s newPW
echo ""
echo ""

XAMPP="/Applications/XAMPP"

sudo echo "removing any previous installation at $XAMPP";

if [ -d $XAMPP ]
then
	sudo /Applications/XAMPP/xamppfiles/xampp stop
	sleep 1
	sudo rm -rf -- $XAMPP
	echo "Done."
else
	echo "Nothing to remove."
fi

echo "installing xampp into $XAMPP...";

unzip -d "/Applications/" "$DIR/XAMPP.zip"


echo "checking ports..."
COUNT=`sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | wc -l`;

if [ $COUNT -ne 0 ]
then
	osascript -e "tell application \"Terminal\" to do script \"$DIR/clear_ports.sh\""
	
	COUNT=`sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | wc -l`;
		
	while [ $COUNT -ne 0 ]
	do
		sleep 2
		COUNT=`sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | wc -l`;
	done
fi
sudo /Applications/XAMPP/xamppfiles/xampp enablessl
sudo /Applications/XAMPP/xamppfiles/xampp startapache
sudo /Applications/XAMPP/xamppfiles/xampp startmysql

echo ""
echo ""
echo "Configuring..."

VAR=$(expect -c "

set timeout 10

spawn sudo /Applications/XAMPP/xamppfiles/xampp security

expect {
	-ex {password? [ja]}		{ send \"y\\r\" }	
}
expect {
	-ex {Password:}				{ send \"$newPW \\r\" }
}
expect {
	-ex {(again):}				{ send \"$newPW \\r\" }	
}
expect {
	-ex {[ja]}				{ send \"n\\r\" }	
}
expect {
	-ex {Do [ja]}				{ send \"y\\r\" }	
}
expect {
	-ex {Password:} 			{ send \"$newPW \\r\" }	
}
expect {
	-ex {(again):} 				{ send \"$newPW \\r\" }
}
expect {
	-ex {Do [ja]}				{ send \"y\\r\" }	
}
expect {
	-ex {Password:} 			{ send \"$newPW \\r\" }
}
expect {
	-ex {(again):} 				{ send \"$newPW \\r\" }

}
expect {
	-ex {anyway? [nein]}		{ send \"n\\r\" }	
}

puts \"\\n\\n First run OK.  \\n\"

spawn sudo /Applications/XAMPP/xamppfiles/xampp security

expect {
	-ex {anyway? [nein]} 	{ send \"n\\r\" }
}
expect {
	-ex {[ja]} 			{ send \"y\\r\" }
}
expect {
	-ex {anyway? [nein]} 	{ send \"n\\r\" }
}

puts \"\\n\\nFinished OK.  \\n\"
")

echo "==============="
echo "$VAR"


echo " "
echo "setting language to english..."
curl -sk --user xampp:$newPW https://localhost/xampp/lang.php?en > "$DIR/lang.html"

echo " "
echo "ensuring that security program succeeded..."
curl -sk --user xampp:$newPW https://localhost/xampp/security.php > "$DIR/security.html"

SECURITY=`cat $DIR/security.html | tr ' ' '\n' | grep -c xampp`

UNSECURERESULTS=`cat $DIR/security.html | tr ' ' '\n' | grep -c UNSECURE`

SECURERESULTS=$((5-$UNSECURERESULTS))

if [ $SECURITY -lt 1 ]
then
	echo $SECURITY
	SECURERESULTS="0"
else
	sudo rm "$DIR/lang.html"
	sudo rm "$DIR/security.html"
fi

echo " "
echo "security result: $SECURERESULTS/5"

if [ $SECURERESULTS -eq 5 ]
then
	echo " "
	echo "XAMPP was successfully secured!"
	echo " "
	
	echo "copying..."

	sudo cp -R "$DIR/../../common/htdocs" "/Applications/XAMPP/xamppfiles"
	sudo cp -R "$DIR/../../Mac" "/Applications/XAMPP/xamppfiles/htdocs/build"
	sudo cp -R "$DIR/../../Windows" "/Applications/XAMPP/xamppfiles/htdocs/build"
	
	echo "installing autostarter..."
	
	sudo cp "$DIR/com.belnet.autostart.plist" "/Library/LaunchDaemons"
	sudo chown root:wheel /Library/LaunchDaemons/com.belnet.autostart.plist
	sudo chmod 644 /Library/LaunchDaemons/com.belnet.autostart.plist
	sudo launchctl load -w /Library/LaunchDaemons/com.belnet.autostart.plist

	echo "additional configurations to apacheÉ"

	PERMUSER=`whoami`

	sudo cat "/Applications/XAMPP/xamppfiles/etc/httpd.conf" > "$DIR/httpd3.conf"
	sudo cat "$DIR/httpd3.conf" | sed 's/User daemon/User www/g' | sed 's/User nobody/User www/g'		> "$DIR/httpd2.conf"
	sudo cat "$DIR/httpd2.conf" | sed 's/Group daemon/Group www/g' | sed 's/Group nogroup/Group www/g'	> "$DIR/httpd.conf"
	sudo chown $PERMUSER:www "$DIR/httpd.conf"

	sudo cp "$DIR/httpd.conf" "/Applications/XAMPP/xamppfiles/etc"

	sudo rm "$DIR/httpd3.conf"
	sudo rm "$DIR/httpd2.conf"
	sudo rm "$DIR/httpd.conf"


	sudo cat "/Applications/XAMPP/xamppfiles/etc/php.ini" > "$DIR/php.ini4"
	sudo cat "$DIR/php.ini4" | sed 's/post_max_size = 128M/post_max_size = 1024M/g' 	> "$DIR/php.ini3"
	sudo cat "$DIR/php.ini3" | sed 's/memory_limit = 128M/memory_limit = 1024M/g' 	 	> "$DIR/php.ini2"
	sudo cat "$DIR/php.ini2" | sed 's/max_execution_time = 30/max_execution_time = 200/g'	> "$DIR/php.ini"
	sudo chown $PERMUSER:www "$DIR/php.ini"

	sudo cp "$DIR/php.ini" "/Applications/XAMPP/xamppfiles/etc"

	sudo rm "$DIR/php.ini4"
	sudo rm "$DIR/php.ini3"
	sudo rm "$DIR/php.ini2"
	sudo rm "$DIR/php.ini"


	echo "setting up belnet mysql connect..."
	
	sudo sed "s/CHANGEME/$newPW/" < "/Applications/XAMPP/xamppfiles/htdocs/includes/nodePassword.php" > "$DIR/nodePassword.php"
	sudo cp "$DIR/nodePassword.php" "/Applications/XAMPP/xamppfiles/htdocs/includes"
	sudo rm "$DIR/nodePassword.php"

	echo "giving www permissions..."

	sudo chown -R $PERMUSER:www "/Applications/XAMPP/xamppfiles/htdocs/"
	sudo chmod -R 770 "/Applications/XAMPP/xamppfiles/htdocs/"

	sudo chgrp www "/Applications/XAMPP/xamppfiles/phpmyadmin/config.inc.php"
	sudo chmod g+r "/Applications/XAMPP/xamppfiles/phpmyadmin/config.inc.php"

	MYSQLRESULTS=`curl -sk --user user:turtyeah https://localhost/ | grep -c MySQL`
	
	echo "stopping xampp..."
	
	sudo /Applications/XAMPP/xamppfiles/xampp stop
	
	if [ $MYSQLRESULTS -eq 0 ]
	then
		echo " "
		echo "belnet has been successfully set up! "
		echo " "
		echo " "
		echo "You will be forwarded to https://localhost/ to set up your node."
		echo " "
		echo "The browser will complain about the security certificate. You have to make an exception as usual."
		echo " "
		echo "It will prompt you to enter a password. Use the same login: user & turtyeah"
		echo " "
		printf "Do you want to go there now? (Y/N): "
		read yn
		echo " "
		
		if [ $yn = Y ] || [ $yn = y ] 
		then
			echo "restarting xampp..."
			
			osascript -e 'tell application "Terminal" to do script "sudo /Applications/XAMPP/xamppfiles/htdocs/build/Mac/data/belnet_autostart.sh"'
			sleep 5
		
			echo "Opening browser..."
			open "https://localhost/"
		else
			echo "start xampp manually (Applications/XAMPP/XAMPP Control) and proceed to https://localhost/ at any time to complete setup."
			exit
		fi
		
	else
		echo " "
		echo "WARNING: belnet mysql connection setup failed. "
		echo "open /Applications/XAMPP/xamppfiles/htdocs/includes/nodePassword.php in a text editor and "
		echo "replace CHANGEME with your the password you just entered, "
		echo "then try proceeding to https://localhost/ in your web browser."
		echo " "
	fi
else
	
	sudo /Applications/XAMPP/xamppfiles/xampp stop
 
	echo " "
	echo "XAMPP has not been secured! "
	echo "You must delete the entire /Applications/XAMPP folder"
	echo "before trying again!"
	echo " "
	echo "leaving XAMPP unsecured is not a good idea..."
	echo " "
	
fi

echo "you are welcome to close this window."
echo " "
