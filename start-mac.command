#!/bin/bash
# Double-click to run the Enoma website on this Mac (or run ./start-mac.command).
cd "$(dirname "$0")" || exit 1

PHP_BIN="$(command -v php || true)"
for candidate in /opt/homebrew/bin/php /usr/local/bin/php /Applications/XAMPP/xamppfiles/bin/php /Applications/MAMP/bin/php/php8*/bin/php; do
  if [ -z "$PHP_BIN" ] && [ -x "$candidate" ]; then PHP_BIN="$candidate"; fi
done

if [ -z "$PHP_BIN" ]; then
  echo ""
  echo "  PHP is not installed yet."
  echo ""
  echo "  1. Install Homebrew from https://brew.sh (one command in Terminal)"
  echo "  2. Then run:  brew install php"
  echo "  3. Double-click start-mac.command again."
  echo ""
  echo "  Full instructions: LOCAL-SETUP.md"
  echo ""
  read -r -p "Press Enter to close..."
  exit 1
fi

"$PHP_BIN" start.php
