@echo off
echo Starting Birthday Message Scheduler...
echo Press Ctrl+C to stop.

:loop
echo [%TIME%] Checking for scheduled messages...
c:\xampp-new\php\php.exe c:\xampp-new\htdocs\BirthdayM\cron_runner.php
:: Wait for 60 seconds
timeout /t 60 /nobreak >nul
goto loop
