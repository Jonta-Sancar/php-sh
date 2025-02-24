@echo off
setlocal enabledelayedexpansion

set "BAT_DIR=%~dp0"
set "BAT_DIR=%BAT_DIR:~0,-1%"

set "HANDLER_PATH=%BAT_DIR%\server_handler.php"

set "args=%*"

php "%HANDLER_PATH%" "-d" "%CD%" !args!