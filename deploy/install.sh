#!/bin/bash
# One-step install / update / repair for cPanel. In cPanel → Terminal:
#   cd ~/repositories/enomadigitaltechnology && git pull && bash deploy/install.sh
# Downloads the latest version, copies it into public_html (keeping your
# content), fixes permissions, tests the live site and shows the admin setup code.
set -uo pipefail

REPO="$(cd "$(dirname "$0")/.." && pwd)"
WEB="${DEPLOYPATH:-$HOME/public_html}"; WEB="${WEB%/}"
DOMAIN="${1:-$(sed -n "s#.*'base_url' => 'https\?://\([^/']*\).*#\1#p" "$REPO/includes/config.php" | head -1)}"
DOMAIN="${DOMAIN:-enomadigitaltech.com}"
ok() { printf '  \033[32m✓\033[0m %s\n' "$1"; }
bad() { printf '  \033[31m✗\033[0m %s\n' "$1"; }

echo; echo "1/4  Getting the latest version from GitHub"
if git -C "$REPO" pull --ff-only -q 2>/dev/null; then ok "Up to date: $(git -C "$REPO" log -1 --format='%h %s')"; else bad "Could not download updates (continuing with the copy you have)"; fi

echo; echo "2/4  Copying the website into $WEB"
if bash "$REPO/deploy/cpanel-deploy.sh"; then ok "Website files copied"; else bad "Copy failed (see the message above)"; exit 1; fi

echo; echo "3/4  Checking access"
MODE="$(stat -c '%a' "$WEB")"; GROUP="$(stat -c '%G' "$WEB")"
if [ "$GROUP" != "nobody" ] && [ $((8#$MODE & 8#005)) -ne 5 ]; then chmod 755 "$WEB"; MODE=755; fi
ok "Web folder: permissions $MODE, group $GROUP"
PRIVATE="$(dirname "$WEB")/enoma-storage"
[ -d "$PRIVATE" ] && ok "Private data folder: $PRIVATE" || bad "Private data folder missing: $PRIVATE"

echo; echo "4/4  Testing https://$DOMAIN"
fetch() { curl -s -o /dev/null -w '%{http_code}' --max-time 15 --resolve "$DOMAIN:80:127.0.0.1" "http://$DOMAIN$1"; }
ALL=1
for path in /robots.txt / /about /check.php /admin/login; do
  code="$(fetch "$path")"
  case "$code" in 200|301|302|303) ok "$path → $code" ;; *) bad "$path → $code"; ALL=0 ;; esac
done

echo
if [ -f "$PRIVATE/admin/users.json" ] || [ -f "$WEB/storage/admin/users.json" ]; then
  echo "Admin: log in at https://$DOMAIN/admin with the username and password you chose."
else
  fetch /admin/setup >/dev/null
  CODE_FILE="$PRIVATE/admin/setup-code.txt"; [ -f "$CODE_FILE" ] || CODE_FILE="$WEB/storage/admin/setup-code.txt"
  if [ -f "$CODE_FILE" ]; then
    echo "Admin: go to https://$DOMAIN/admin and enter this setup code: $(tr -d '[:space:]' < "$CODE_FILE")"
    echo "       then choose your own username and password."
  else
    echo "Admin: open https://$DOMAIN/admin once, then run this script again to see the setup code."
  fi
fi
echo
if [ "$ALL" = 1 ]; then echo "All good: open https://$DOMAIN (press Cmd+Shift+R to skip old saved copies)."; else echo "Something is still blocked: copy everything above and send it to your developer."; fi
