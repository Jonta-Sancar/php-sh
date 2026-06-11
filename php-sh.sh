#!/bin/bash

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
HANDLER_PATH="$DIR/server_handler.php"

php "$HANDLER_PATH" "-d" "$(pwd)" "$@"

