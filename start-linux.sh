#!/bin/bash
# Run the Enoma website on this computer: ./start-linux.sh
# The first time, it installs PHP (and the extensions the site uses) if needed, then starts it.
cd "$(dirname "$0")" || exit 1

find_php() {
  PHP_BIN="$(command -v php || true)"
  for candidate in /usr/local/bin/php /opt/lampp/bin/php; do
    if [ -z "$PHP_BIN" ] && [ -x "$candidate" ]; then PHP_BIN="$candidate"; fi
  done
}

find_php

if [ -z "$PHP_BIN" ]; then
  echo ""
  echo "  This website runs on PHP, which is not installed yet."
  read -r -p "  Install it now (needs your password for sudo)? [Y/n] " answer
  case "$answer" in [nN]*) echo "  See LOCAL-SETUP.md to install PHP yourself."; exit 1 ;; esac
  if command -v apt-get >/dev/null 2>&1; then
    sudo apt-get update && sudo apt-get install -y php-cli php-mbstring php-gd php-curl php-xml
  elif command -v dnf >/dev/null 2>&1; then
    sudo dnf install -y php-cli php-mbstring php-gd php-xml
  elif command -v pacman >/dev/null 2>&1; then
    sudo pacman -S --needed --noconfirm php php-gd
  elif command -v zypper >/dev/null 2>&1; then
    sudo zypper install -y php8 php8-mbstring php8-gd php8-curl php8-fileinfo php8-exif php8-openssl
  else
    echo "  Please install PHP 8.1+ with your package manager, then run ./start-linux.sh again."
    exit 1
  fi
  find_php
  [ -n "$PHP_BIN" ] || { echo "  PHP could not be installed. See LOCAL-SETUP.md."; exit 1; }
fi

"$PHP_BIN" start.php
status=$?
if [ "$status" -ne 0 ] && [ "$status" -ne 130 ]; then
  echo "  The website stopped with an error (see the messages above)."
  read -r -p "Press Enter to close..."; exit "$status"
fi
