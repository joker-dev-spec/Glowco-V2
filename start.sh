#!/usr/bin/env bash
# start.sh — run the PHP built-in web server for Railway / Render.
# Serve the project root; `php -S` handles .php routing and static files.
set -e

PORT="${PORT:-8000}"
HOST="${HOST:-0.0.0.0}"

echo "Starting Glow Co. on http://${HOST}:${PORT}"
exec php -S "${HOST}:${PORT}" -t "$(dirname "$0")" "$(dirname "$0")/router.php"
