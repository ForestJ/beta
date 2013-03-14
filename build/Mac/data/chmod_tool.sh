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
echo "                           CHMOD (permissions) tool			"
echo "          "
echo "       this tool assigns the folder you selected to the www (apache) group  "
echo "                 it then makes the folder readable by that group"
echo "          "

echo "  folder to change: $1 "

echo "          "
echo "           enter admin password. (this is your OSX login password)"
echo "          "

sudo chgrp www "$1"
sudo chmod 750 "$1"
sudo chmod g+s "$1"

echo "Success!"