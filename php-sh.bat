@echo off
setlocal enabledelayedexpansion

set "HANDLER_PATH=%CD%\server_handler.php"

set "args=%*"

php "%HANDLER_PATH%" !args!