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


echo "          "
echo "           enter admin password. (this is your OSX login password)"
echo "          "
sudo echo "          "

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
		sudo kill `sudo lsof -i -n -P | grep LISTEN | egrep ':80|:443' | sed 's:[a-zA-Z][0-9a-zA-Z-]* *\([0-9]*\).*: \1:'`

		echo "Success! the ports were opened. Apache will start."
		exit
	else
		echo "apache could not be started. try re-running this script?"
		exit
	fi
fi