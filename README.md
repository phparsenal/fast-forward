# fast-forward

Just throwing out some code, feel free to gut this.

On Windows I put this in a file `ff.bat` in a global path:

```
@echo off
call php C:\wnmap\public\cli-launch\cli-launch.php %*
call C:\wnmap\public\cli-launch\cli-launch.temp.bat
```

The PHP script will write the command you want to run in the batch file and afterwards it will be run.  
I could not find another way to `cd anotherdir` within PHP.

Also note the database schema setup is not written yet. Run assets/model.sql yourself or fix it :)
