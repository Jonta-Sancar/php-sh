@echo off
setlocal enabledelayedexpansion

set "HANDLER_PATH=D:/php_server/server_handler.php"

set "args=%*"
set "args=!args:--server-handler=!"
set "args=!args:-SH=!"

php %HANDLER_PATH% !args!