@echo off
set base_path=%cd%
set nginx_path=%base_path%\nginx-1.14.0
echo Stopping nginx...
taskkill /F /IM nginx.exe > nul
echo Starting nginx...
RunHiddenConsole %nginx_path%\nginx.exe -c %nginx_path%\conf\nginx.conf
echo Start nginx success! 3 seconds later will be close!
ping -n 6 127.0.0.1 > nul
exit