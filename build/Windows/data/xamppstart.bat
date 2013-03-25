@echo off

cd C:\xampp\

SET PATH=%PATH%;%CD%\htdocs\build\Windows\data
echo this is your PATH: 
echo %PATH%

echo ^|     	 
echo ^|        
echo ^|  
echo ^|            ^*MM                `7MM                       mm   
echo ^|             MM                  MM                       MM   
echo ^|             MM,dMMb.   .gP"Ya   MM  `7MMpMMMb.  .gP"Ya mmMMmm 
echo ^|             MM    `Mb ,M'   Yb  MM    MM    MM ,M'   Yb  MM   
echo ^|             MM     M8 8M""""""  MM    MM    MM 8M""""""  MM   
echo ^|             MM.   ,M9 YM.    ,  MM    MM    MM YM.    ,  MM   
echo ^|             P^^YbmdP'   `Mbmmd'.JMML..JMML  JMML.`Mbmmd'  `Mbmo
echo ^|    	 
echo ^|    
echo ^|                          .          .                        
echo ^|                      ,-. ^|- ,-. ,-. ^|- . ,-. ,-.             
echo ^|                      `-. ^|  ,-^| ^|   ^|  ^| ^| ^| ^| ^|             
echo ^|         :;  :;  :;   `-' `' `-^^ '   `' ' ' ' `-^|   :;  :;  :;
echo ^|                                               ,^|             
echo ^|  

netstat -a -o -n > C:\xampp\netstat.txt

tasklist > C:\xampp\tasklist.txt

cscript /nologo htdocs\build\Windows\data\processFinder.vbs "(?=.*LISTENING)(?=.*(:80|:443))" "(\d{1,5})$" "(^[a-z0-9\.]*) *(\d*)" "C:\xampp\netstat.txt" "C:\xampp\tasklist.txt" "force" > C:\xampp\optionaltaskkill.bat

call C:\xampp\optionaltaskkill.bat

DEL C:\xampp\netstat.txt
DEL C:\xampp\tasklist.txt
DEL C:\xampp\optionaltaskkill.bat


start /DC:\xampp\htdocs\build\Windows\data\ /min "belnet" xampp.bat

ping -n 4 127.0.0.1 > nul






