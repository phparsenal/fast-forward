@echo off
SET ffpath=C:\wnmap\public\fast-forward\
call php %ffpath%cli-launch.php %*
call %ffpath%cli-launch.temp.bat
