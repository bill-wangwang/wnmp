@echo off
set base_path=%cd%
set nginx_path=%base_path%\nginx-1.14.0
set php_path=%base_path%\php-7.1.17
set php72_path=%base_path%\php-7.2.13
set php73_path=%base_path%\php-7.3.0
set php5_path=%base_path%\php-5.6.36
set mysql_path=%base_path%\mysql-5.6.40-winx64

echo Starting PHP FastCGI...
RunHiddenConsole %php_path%\php-cgi.exe -b 127.0.0.1:9000 -c %php_path%\php.ini
rem echo Starting PHP72 FastCGI...
rem RunHiddenConsole %php72_path%\php73-cgi.exe -b 127.0.0.1:9002 -c %php72_path%\php.ini
rem echo Starting PHP73 FastCGI...
rem RunHiddenConsole %php73_path%\php73-cgi.exe -b 127.0.0.1:9003 -c %php73_path%\php.ini
echo Starting PHP5 FastCGI...
RunHiddenConsole %php5_path%\php5-cgi.exe -b 127.0.0.1:9001 -c %php5_path%\php.ini

echo Starting nginx...
RunHiddenConsole %nginx_path%\nginx.exe -c %nginx_path%\conf\nginx.conf
echo Starting MySql...
RunHiddenConsole %mysql_path%\bin\mysqld --init-file=%mysql_path%\my.ini
echo Start all success! 3 seconds later will be close!
ping -n 3 127.0.0.1 > nul
exit