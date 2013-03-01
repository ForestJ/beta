#!/bin/sh

echo "      	 "
echo "             *MM                \`7MM                       mm   "
echo "              MM                  MM                       MM   "
echo "              MM,dMMb.   .gP\"Ya   MM  \`7MMpMMMb.  .gP\"Ya mmMMmm" 
echo "              MM    \`Mb ,M'   Yb  MM    MM    MM ,M'   Yb  MM   "
echo "              MM     M8 8M\"\"\"\"\"\"  MM    MM    MM 8M\"\"\"\"\"\"  MM   "
echo "              MM.   ,M9 YM.    ,  MM    MM    MM YM.    ,  MM   "
echo "              P^YbmdP'   \`Mbmmd'.JMML..JMML  JMML.\`Mbmmd'  \`Mbmo"
echo "     	 "


echo "          .                  .                                               ."
echo "  ,-. ,-. |- ,-. ,-.   ,-. ,-| ,-,-. . ,-.   ,-. ,-. ,-. ,-. . , , ,-. ,-. ,-|"
echo "  |-' | | |  |-' |     ,-| | | | | | | | |   | | ,-| \`-. \`-. |/|/  | | |   | |"
echo "  \`-' ' ' \`' \`-' '     \`-^ \`-^ ' ' ' ' ' '   |-' \`-^ \`-' \`-' ' '   \`-' '   \`-^"
echo "                                             |                                 "
echo "                                             '                                 "
echo "          "
echo "   (this is your OSX login password, not the one you assigned to mysql/xampp)"

COUNT=`sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | wc -l`;

if [ $COUNT -ne 0 ]
then
	echo "The following processes are listening on either port 80 or 443 and need to be force-quitted before apache can start."
	sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | sed 's:\([a-zA-Z][0-9a-zA-Z-]*\) *\([0-9]*\).*: - \1 :'
	echo "  "
	printf "Do you want to force quit them now? (Y/N): "
	read yn
	FOUND=
	if [ $yn = Y ] || [ $yn = y ] 
	then
		sudo `sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | sed 's:[a-zA-Z][0-9a-zA-Z-]* *\([0-9]*\).*:kill \1 :'`
		sleep 1
		COUNT=`sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | wc -l`;
		if [ $COUNT -eq 0 ]
		then
			echo "Success! the ports were opened. Apache will start:"
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
	sudo /Applications/XAMPP/xamppfiles/xampp startapache
	sudo /Applications/XAMPP/xamppfiles/xampp startmysql
fi

echo "      	 "
echo "         "
echo "   "
echo "      	 "
echo "         "
echo "   "
echo "      	 "
echo "         "
echo "   "
echo "             *MM                \`7MM                       mm   "
echo "              MM                  MM                       MM   "
echo "              MM,dMMb.   .gP\"Ya   MM  \`7MMpMMMb.  .gP\"Ya mmMMmm" 
echo "              MM    \`Mb ,M'   Yb  MM    MM    MM ,M'   Yb  MM   "
echo "              MM     M8 8M\"\"\"\"\"\"  MM    MM    MM 8M\"\"\"\"\"\"  MM   "
echo "              MM.   ,M9 YM.    ,  MM    MM    MM YM.    ,  MM   "
echo "              P^YbmdP'   \`Mbmmd'.JMML..JMML  JMML.\`Mbmmd'  \`Mbmo"
echo "     	 "

echo "             .                    .                                       "
echo "         ,-. |  ,-. ,-. ,-. ,-.   |  ,-. ,-. .  , ,-.   ,-. ,-. ,-. ,-.   "
echo "         | | |  |-' ,-| \`-. |-'   |  |-' ,-| | /  |-'   | | | | |-' | |"
echo "         |-' \`' \`-' \`-^ \`-' \`-'   \`' \`-' \`-^ \`'   \`-'   \`-' |-' \`-' ' '"
echo "         |                                                  |            "
echo "         '                                                  '      "
echo "                                                             "

sleep 3

cd /Applications/XAMPP

while [ 1 -eq 1 ]
do
	echo "Updating node..."

	curl -sk --user user:turtyeah --digest https://localhost/?netupdate=1 > output.html

	HASBELNET=`cat output.html | grep "belnet" | awk '{ print length }'`

	UPDATEREQUEST=`cat output.html | grep "update is availiable from" | sed "s:.* \[\([0-9.]*\)\].*:curl -sk --user user\:turtyeah --digest https\://localhost/utilities/performUpdate.php?ip=\1:"`
	
	STRLEN=`echo "$UPDATEREQUEST" | awk '{ print length }'`
	
	if [ "$HASBELNET" != 0 ]
	then
		if [ "$STRLEN" != 0 ]
		then
			echo "Found a new patch, attempting to patch us..."
			echo "$UPDATEREQUEST"
			
			UPDATERESULT=`$UPDATEREQUEST`
			
			echo "RESULT: $UPDATERESULT"
		else 
			echo "Node is up to date!"
		fi
	else 
		echo "Update Failed! Please report this as a bug!"
	fi

	rm "output.html"
	
	sleep 600
done
