#!/bin/bash
# Maki Space - Auto Deploy Script via FTP
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
python3 "$DIR/deploy.py" "$@"
