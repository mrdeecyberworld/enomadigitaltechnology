#!/bin/bash
# Double-click to run the Enoma website on this Mac (or run ./start-mac.command).
# The first time, it installs everything the website needs (Homebrew and PHP), then starts it.
cd "$(dirname "$0")" || exit 1

find_php() {
  PHP_BIN="$(command -v php || true)"
  for candidate in /opt/homebrew/bin/php /usr/local/bin/php /Applications/XAMPP/xamppfiles/bin/php /Applications/MAMP/bin/php/php8*/bin/php; do
    if [ -z "$PHP_BIN" ] && [ -x "$candidate" ]; then PHP_BIN="$candidate"; fi
  done
}

load_brew() {
  for b in /opt/homebrew/bin/brew /usr/local/bin/brew; do
    if [ -x "$b" ]; then eval "$("$b" shellenv)"; return 0; fi
  done
  command -v brew >/dev/null 2>&1
}

pause_and_exit() {
  echo ""
  read -r -p "Press Enter to close..."
  exit "${1:-1}"
}

find_php

if [ -z "$PHP_BIN" ]; then
  echo ""
  echo "  ============================================================"
  echo "   First-time setup"
  echo "  ============================================================"
  echo ""
  echo "  This website runs on PHP, which is not on this Mac yet."
  echo "  It will be installed for you now with Homebrew (free, the standard"
  echo "  Mac installer for developer tools). It takes about 5-10 minutes,"
  echo "  one time only. Your Mac password may be requested: type it and"
  echo "  press Enter (nothing appears on screen while you type)."
  echo ""
  read -r -p "  Install now? [Y/n] " answer
  case "$answer" in [nN]*) echo "  OK. See LOCAL-SETUP.md to install PHP yourself."; pause_and_exit 1 ;; esac

  if ! load_brew; then
    echo ""
    echo "  Installing Homebrew..."
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)" || {
      echo "  Homebrew could not be installed. See https://brew.sh and LOCAL-SETUP.md."
      pause_and_exit 1
    }
    load_brew || { echo "  Homebrew was installed but can't be found. Close this window and try again."; pause_and_exit 1; }
  fi

  echo ""
  echo "  Installing PHP..."
  brew install php || { echo "  PHP could not be installed. Try: brew install php"; pause_and_exit 1; }
  find_php
  if [ -z "$PHP_BIN" ]; then
    echo "  PHP was installed but can't be found. Close this window and double-click start-mac.command again."
    pause_and_exit 1
  fi
  echo ""
  echo "  All set. Starting the website..."
fi

"$PHP_BIN" start.php
status=$?
if [ "$status" -ne 0 ] && [ "$status" -ne 130 ]; then
  echo "  The website stopped with an error (see the messages above)."
  pause_and_exit "$status"
fi
