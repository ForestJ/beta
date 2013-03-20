@echo off

SET OLDPATH=%PATH%
SET PATH=%PATH%;%CD%\data;C:\xampp;

echo this is your PATH: 
echo %PATH%
echo running from:
echo %CD%
echo .
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
echo ^|   
echo ^|   

echo .

cscript /nologo "%CD%\data\escape.vbs" "%CD%" > temp_escape.txt
set PREVDIR="%CD%"
set /p ESCAPEDDIR= < temp_escape.txt
DEL "temp_escape.txt"

cd C:\

if errorlevel 1 goto NOCDRIVE
goto HASCDRIVE
:NOCDRIVE

echo There is no drive named C:\. Right now this is not supported by this installer. 
echo It assumes that C:\ is your boot drive. Sorry.
exit

:HASCDRIVE

echo enter a new password for xampp to use internally. 
echo this is required so that no one can exploit xampp to get into your computer.
echo .
echo after you enter this password, the script will install xampp and set up belnet.
echo .
echo at the end, you will be prompted to complete setup in your web-browser.
echo .
echo WARNING: typing the password will be displayed in plain text!! 
echo .

SET /p PASSWD=new password:

::this does not work for some reason
::editv32 -m  PASSWD

echo .
echo checking for existing xampp installation.

cd C:\xampp

SET CLEAREDPORTS=N

if errorlevel 1 goto NOXAMPP

echo Found xampp. Stopping it...
cd C:\
xampp_stop > nul

ping -n 1 127.0.0.1 > nul

if %CLEAREDPORTS% == N CALL :CLEARPORTS

SET CLEAREDPORTS=Y

echo uninstalling xampp..

RMDIR /S /Q "C:\xampp"

:NOXAMPP

if %CLEAREDPORTS% == N CALL :CLEARPORTS

echo Installing xampp...

ping -n 1 127.0.0.1 > nul

cd C:\

7z x %PREVDIR%\data\xampp.7z

cd %PREVDIR%

echo Starting xampp. this might take a minute...

xampp_start > nul


:: ********  CURL MYSQL PASSWORT **********

echo .
echo Configuring xampp (securing mysql)...
echo curl http://localhost/security/xamppsecurity.php
echo "changing=t&mypasswd=$PASSWD&mypasswdrepeat=$PASSWD&authphpmyadmin=cookie&mysqlpfile=yes"

curl -d "changing=t&mypasswd=%PASSWD%&mypasswdrepeat=%PASSWD%&authphpmyadmin=cookie&mysqlpfile=yes" http://localhost/security/xamppsecurity.php > output.html

FINDSTR "erfolgreich" output.html > tempfindoutput.txt
set /p FINDOUTPUT= < tempfindoutput.txt
echo .
IF "%FINDOUTPUT%"=="" (
	echo Failure! 
	echo MySQL root password was not set!
	echo PhpMyAdmin is not secure!
)
IF NOT "%FINDOUTPUT%"=="" (
	echo Success!
)

DEL output.html
DEL tempfindoutput.txt

:: ********  CURL XAMPP PASSWORT **********

echo .
echo Configuring xampp (securing admin page)...
echo curl http://localhost/security/xamppsecurity.php
echo "xamppaccess=t&xamppuser=xampp&xampppasswd=$PASSWD&xapfile=yes"

curl -d "xamppaccess=t&xamppuser=xampp&xampppasswd=%PASSWD%&xapfile=yes" http://localhost/security/xamppsecurity.php > output2.html

FINDSTR "ERFOLG" output2.html > tempfindoutput2.txt
set /p FINDOUTPUT2= < tempfindoutput2.txt
echo .
IF "%FINDOUTPUT2%"=="" (
	echo Failure! 
	echo xampp security pages were not protected!
	echo xampp is not secure!
)
IF NOT "%FINDOUTPUT2%"=="" (
	echo Success!
)

DEL output2.html
DEL tempfindoutput2.txt

:: ******** INSTALL ALL **********

echo .
echo Copying files...

ren "C:\xampp\htdocs" "htdocs_old"

CALL :RESOLVE "%CD%\..\common" RESULT
xcopy "%RESULT%" "C:\xampp\" /s /e /c /h > nul

CALL :RESOLVE "%CD%\..\Mac" RESULT
xcopy "%RESULT%" "C:\xampp\htdocs\build\Mac\" /s /e /c /h > nul

CALL :RESOLVE "%CD%\..\Windows" RESULT
xcopy "%RESULT%" "C:\xampp\htdocs\build\Windows\" /s /e /c /h > nul

echo Installing autostarter...

copy "%CD%\data\xamppstart.bat" "C:\Documents and Settings\All Users\Start Menu\Programs\Startup\" > nul

echo .
echo Done!

:: ********  REPLACE: MYSQL PASSWORT **********


echo .
echo Configuring belnet with new mysql password...
echo .
echo cscript replace.vbs "CHANGEME" "$PASSWD" "nodePassword.php"
cscript /nologo data\replace.vbs "CHANGEME" "%PASSWD%" "C:\xampp\htdocs\includes\nodePassword.php" > C:\xampp\htdocs\includes\nodePassword2.php
echo .
FINDSTR "nodePassword" "C:\xampp\htdocs\includes\nodePassword2.php" > tempfindoutput3.txt
set /p FINDOUTPUT3= < tempfindoutput3.txt

IF "%FINDOUTPUT3%"=="" (
	echo Failure! 
	echo MySQL login not set!
	echo belnet will not work!
	del "C:\xampp\htdocs\includes\nodePassword2.php"
)
IF NOT "%FINDOUTPUT3%"=="" (
	echo Success!
	del "C:\xampp\htdocs\includes\nodePassword.php"
	ren "C:\xampp\htdocs\includes\nodePassword2.php" "nodePassword.php"
)

DEL tempfindoutput3.txt

:: ********  REPLACE: PHP.INI CURL EXTENSION **********

echo .
echo Configuring xampp (enabling curl)...
echo .
echo cscript replace.vbs ";extension=php_curl.dll" "extension=php_curl.dll" "php.ini"
cscript /nologo data\replace.vbs ";extension=php_curl.dll" "extension=php_curl.dll" "C:\xampp\php\php.ini" > C:\xampp\php\php2.ini
echo .
FINDSTR "; Windows Extensions" "C:\xampp\php\php2.ini" > tempfindoutput4.txt
set /p FINDOUTPUT4= < tempfindoutput4.txt

IF "%FINDOUTPUT4%"=="" (
	echo Failure! 
	echo Unable to enable curl inside php.ini!
	echo belnet will not work!
	del "C:\xampp\php\php2.ini"
)
IF NOT "%FINDOUTPUT4%"=="" (
	echo Success!
	del "C:\xampp\php\php.ini"
	ren "C:\xampp\php\php2.ini" "php.ini"
)

DEL tempfindoutput4.txt

:: ********  REPLACE: PHP.INI MEM LIMIT **********

echo .
echo Configuring xampp (setting memory limit)...
echo .
echo cscript replace.vbs "memory_limit = 128M" "memory_limit = 1024M" "php.ini"
cscript /nologo data\replace.vbs "memory_limit = 128M" "memory_limit = 1024M" "C:\xampp\php\php.ini" > C:\xampp\php\php2.ini
echo .
FINDSTR "; Windows Extensions" "C:\xampp\php\php2.ini" > tempfindoutput4.txt
set /p FINDOUTPUT4= < tempfindoutput4.txt

IF "%FINDOUTPUT4%"=="" (
	echo Failure! 
	echo Unable to set memory limit inside php.ini!
	echo belnet will not work!
	del "C:\xampp\php\php2.ini"
)
IF NOT "%FINDOUTPUT4%"=="" (
	echo Success!
	del "C:\xampp\php\php.ini"
	ren "C:\xampp\php\php2.ini" "php.ini"
)

DEL tempfindoutput4.txt

:: ********  REPLACE: PHP.INI ERROR LEVEL **********

echo .
echo Configuring xampp (Setting PHP error reporting level)...
echo .
echo cscript replace.vbs "error_reporting = E_ALL | E_STRICT" "error_reporting = E_ALL & ~E_NOTICE" "php.ini"
cscript /nologo data\replace.vbs "error_reporting = E_ALL | E_STRICT" "error_reporting = E_ALL & ~E_NOTICE" "C:\xampp\php\php.ini" > C:\xampp\php\php2.ini
echo .
FINDSTR "; Windows Extensions" "C:\xampp\php\php2.ini" > tempfindoutput4.txt
set /p FINDOUTPUT4= < tempfindoutput4.txt

IF "%FINDOUTPUT4%"=="" (
	echo Failure! 
	echo Unable to set error reporting inside php.ini!
	echo this is a minor issue, but should never happen...
	del "C:\xampp\php\php2.ini"
)
IF NOT "%FINDOUTPUT4%"=="" (
	echo Success!
	del "C:\xampp\php\php.ini"
	ren "C:\xampp\php\php2.ini" "php.ini"
)

DEL tempfindoutput4.txt

:: ******** RESTART **********

echo .
echo Restarting xampp... one moment..

xampp_stop > nul

cd C:\xampp\
start /DC:\xampp\ /min "belnet" xampp.bat

echo .
echo .
echo .
echo SUCCESS! belnet should now be running.
echo .
ping -n 2 127.0.0.1 > nul
echo .
echo .
echo You will be forwarded to https://localhost/ to set up your node.
echo .
echo The browser will complain about the security certificate. You have to make an exception as usual.
echo .
echo It will prompt you to enter a password. Use the same login: user ^& turtyeah
echo .

echo .

set /p answer=Do you want to go there now? (Y/N):

if %answer% == Y goto mycontin
if %answer% == y goto mycontin
if %answer% == N goto H
if %answer% == n goto H

:H
echo start xampp manually (C:\xampp\xamppstart.bat) and proceed to https://localhost/ at any time to complete setup.
ping -n 200 127.0.0.1 > nul
goto :EOF

:mycontin

echo Opening browser...

start https://localhost/

SET PATH=%OLDPATH%;C:\xampp\htdocs\build\Windows\;

ping -n 200 127.0.0.1 > nul

GOTO :EOF

:CLEARPORTS

echo Checking ports...

netstat -a -o -n > %ESCAPEDDIR%\netstat.txt

tasklist > %ESCAPEDDIR%\tasklist.txt

cscript /nologo "%PREVDIR%\data\processFinder.vbs" "(?=.*LISTENING)(?=.*(:80|:443))" "(\d{1,5})$" "(^[a-z0-9\.]*) *(\d*)" %ESCAPEDDIR%\netstat.txt %ESCAPEDDIR%\tasklist.txt > %ESCAPEDDIR%\optionaltaskkill.bat

cd %ESCAPEDDIR%

call optionaltaskkill.bat

DEL %ESCAPEDDIR%\netstat.txt
DEL %ESCAPEDDIR%\tasklist.txt
DEL %ESCAPEDDIR%\optionaltaskkill.bat

GOTO :EOF

:RESOLVE
SET %2=%~f1
GOTO :EOF



