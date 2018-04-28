@echo off
set base_path=%cd%
set nginx_path=%base_path%\nginx-1.14.0
set php_path=%base_path%\php-7.1.17
set mysql_path=%base_path%\mysql-5.6.40-winx64

echo Starting PHP FastCGI...
RunHiddenConsole %php_path%\php-cgi.exe -b 127.0.0.1:9000 -c %php_path%\php.ini
echo Starting nginx...
RunHiddenConsole %nginx_path%\nginx.exe -c %nginx_path%\conf\nginx.conf
echo Starting MySql...
RunHiddenConsole %mysql_path%\bin\mysqld 
echo Start all success! 3 seconds later will be close!
ping -n 6 127.0.0.1 > nul
exit