@ECHO OFF

cd C:\xampp\

SET PATH=%PATH%;%CD%\htdocs\build\Windows\data
echo this is your PATH: 
echo %PATH%


echo ^|      	 
echo ^|         
echo ^|   
echo ^|             ^*MM                `7MM                       mm   
echo ^|              MM                  MM                       MM   
echo ^|              MM,dMMb.   .gP"Ya   MM  `7MMpMMMb.  .gP"Ya mmMMmm 
echo ^|              MM    `Mb ,M'   Yb  MM    MM    MM ,M'   Yb  MM   
echo ^|              MM     M8 8M""""""  MM    MM    MM 8M""""""  MM   
echo ^|              MM.   ,M9 YM.    ,  MM    MM    MM YM.    ,  MM   
echo ^|              P^^YbmdP'   `Mbmmd'.JMML..JMML  JMML.`Mbmmd'  `Mbmo
echo ^|     	 


netstat -a -o -n > C:\xampp\netstat.txt

tasklist > C:\xampp\tasklist.txt

cscript /nologo htdocs\build\Windows\data\processFinder.vbs "(?=.*LISTENING)(?=.*(:80|:443))" "(\d{1,5})$" "(^[a-z0-9\.]*) *(\d*)" "C:\xampp\netstat.txt" "C:\xampp\tasklist.txt" > C:\xampp\optionaltaskkill.bat

call C:\xampp\optionaltaskkill.bat

DEL C:\xampp\netstat.txt
DEL C:\xampp\tasklist.txt
DEL C:\xampp\optionaltaskkill.bat

ping -n 2 127.0.0.1 > nul

echo ^|             .                    .                                    
echo ^|         ,-. ^|  ,-. ,-. ,-. ,-.   ^|  ,-. ,-. .  , ,-.   ,-. ,-. ,-. ,-.
echo ^|         ^| ^| ^|  ^|-' ,-^| `-. ^|-'   ^|  ^|-' ,-^| ^| /  ^|-'   ^| ^| ^| ^| ^|-' ^| ^|
echo ^|         ^|-' `' `-' `-^^ `-' `-'   `' `-' `-^^ `'   `-'   `-' ^|-' `-' ' '
echo ^|         ^|                                                  ^|          
echo ^|         '                                                  '          
echo ^|         


cd C:\xampp\
start /DC:\xampp\ /b "" apache_start.bat > nul
start /DC:\xampp\ /b "" mysql_start.bat > nul

ping -n 3 127.0.0.1 > nul

:loopstart
	echo Updating Node...
	curl -sk --user user:turtyeah --digest https://localhost/?netupdate=1  > C:\xampp\output.html
	
	cscript /nologo htdocs\build\Windows\data\regex.vbs "\[(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\]" "C:\xampp\output.html" > C:\xampp\output2.txt
	set /p FOUNDIP= < C:\xampp\output2.txt
	
	cscript /nologo htdocs\build\Windows\data\regex.vbs "(belnet)" "C:\xampp\output.html" > C:\xampp\output3.txt
	set /p FOUNDBELNET= < C:\xampp\output3.txt
	
	IF NOT "%FOUNDBELNET%"=="" (		
		IF "%FOUNDIP%"=="" (		
			echo Update Successful!
		)
		IF NOT "%FOUNDIP%"=="" (
			echo Found a new patch at %FOUNDIP%, attempting to patch us...
			curl -k --user user:turtyeah --digest https://localhost/utilitiesperformUpdate.php?ip=%FOUNDIP%
		)
	)
	IF "%FOUNDBELNET%"=="" (		
		echo Update Failed! Please report this as a bug!
	)
	

	DEL C:\xampp\output.html
	DEL C:\xampp\output2.txt
	DEL C:\xampp\output3.txt
	
	ping -n 600 127.0.0.1 > nul
GOTO loopstart