#!/bin/bash
# Double-click to run the Enoma website on this computer: ./start-linux.sh.
cd "$(dirname "$0")" || exit 1

PHP_BIN="$(command -v php || true)"
for candidate in /opt/homebrew/bin/php /usr/local/bin/php /Applications/XAMPP/xamppfiles/bin/php /Applications/MAMP/bin/php/php8*/bin/php; do
  if [ -z "$PHP_BIN" ] && [ -x "$candidate" ]; then PHP_BIN="$candidate"; fi
done

if [ -z "$PHP_BIN" ]; then
  echo ""
  echo "  PHP is not installed yet."
  echo ""
  echo "  Ubuntu/Debian: sudo apt install php-cli php-mbstring php-gd php-curl php-xml"
  echo "  Fedora:        sudo dnf install php-cli php-mbstring php-gd php-xml"
  echo "  Then run ./start-linux.sh again."
  echo ""
  echo "  Full instructions: LOCAL-SETUP.md"
  echo ""
  read -r -p "Press Enter to close..."
  exit 1
fi

"$PHP_BIN" start.php
