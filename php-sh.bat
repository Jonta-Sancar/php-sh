@echo off
setlocal enabledelayedexpansion

set "HANDLER_PATH=%CD%\server_handler.php"

php "%HANDLER_PATH%" !args!