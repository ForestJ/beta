#!/bin/sh


COUNT=`sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | wc -l`;

if [ $COUNT -ne 0 ]
then
	osascript -e 'tell application "Terminal" to do script "/Applications/XAMPP/xamppfiles/htdocs/build/Mac/data/clear_ports.sh"'
	
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
echo "            "
echo "         "

sleep 3

cd /Applications/XAMPP

while [ 1 -eq 1 ]
do
	echo "Updating node..."

	sudo curl -sk --user user:turtyeah --digest https://localhost/?netupdate=1 > output.html

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
