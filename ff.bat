@echo off
SET ffpath=D:\GithubProjects\phparsenal\fast-forward\
call php %ffpath%cli-launch.php %*
call %ffpath%cli-launch.temp.bat
