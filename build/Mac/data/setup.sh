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
	echo "The following processes are listening on either port 80 or 443 and need to be force-quitted before apache can start."
	sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | sed 's:\([a-zA-Z][0-9a-zA-Z-]*\) *\([0-9]*\).*: - \1 :'
	echo "  "
	printf "Do you want to force quit them now? (Y/N): "
	read yn
	
	if [ $yn = Y ] || [ $yn = y ] 
	then
		sudo `sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | sed 's:[a-zA-Z][0-9a-zA-Z-]* *\([0-9]*\).*:kill \1 :'`
		sleep 1
		COUNT=`sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | wc -l`;
		if [ $COUNT -eq 0 ]
		then
			echo "Success! the ports were opened. Apache will start:"
			sudo /Applications/XAMPP/xamppfiles/xampp enablessl
			sudo /Applications/XAMPP/xamppfiles/xampp startapache
			sudo /Applications/XAMPP/xamppfiles/xampp startmysql
		else
			echo "apache could not be started. try re-running this script?"
			exit
		fi
	else
		echo "apache could not be started. try re-running this script?"
		exit
	fi
else
	sudo /Applications/XAMPP/xamppfiles/xampp enablessl
	sudo /Applications/XAMPP/xamppfiles/xampp startapache
	sudo /Applications/XAMPP/xamppfiles/xampp startmysql
fi

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
	-ex {[ja]}					{ send \"n\\r\" }	
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

puts \"\\n\\n First run OK. Password used: $newPW \\n\"

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

puts \"\\n\\nFinished OK. Password used: $newPW \\n\"
")

echo "==============="
echo "$VAR"


echo " "
echo "setting language to english..."
curl -sk --user xampp:$newPW https://localhost/xampp/lang.php?en 

echo " "
echo "ensuring that security program succeeded..."
UNSECURERESULTS=`curl -sk --user xampp:$newPW https://localhost/xampp/security.php | tr ' ' '\n' | grep -c UNSECURE`

SECURERESULTS=$((5-$UNSECURERESULTS))

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
	
	sudo chgrp -R www "/Applications/XAMPP/xamppfiles/htdocs/"
	sudo chmod -R 777 "/Applications/XAMPP/xamppfiles/htdocs/"
	
	echo "installing autostarter..."
	
	osascript -e 'tell application "System Events" to make login item at end with properties {path:"/Applications/XAMPP/xamppfiles/htdocs/build/Mac/data/belnet_autostart.app", hidden:false}'
	
	echo "setting up belnet mysql connect..."
	
	sudo sed "s/CHANGEME/$newPW/" < "/Applications/XAMPP/xamppfiles/htdocs/includes/nodePassword.php" > "$DIR/nodePassword.php"
	
	sudo cp "$DIR/nodePassword.php" "/Applications/XAMPP/xamppfiles/htdocs/includes"
	
	sudo rm "$DIR/nodePassword.php"
	
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