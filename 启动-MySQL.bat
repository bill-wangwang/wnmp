@echo off
set base_path=%cd%
set mysql_path=%base_path%\mysql-5.6.40-winx64
echo Starting MySql...
RunHiddenConsole %mysql_path%\bin\mysqld --init-file=%mysql_path%\my.ini
echo Start all success! 3 seconds later will be close!
ping -n 3 127.0.0.1 > nul
exit