#!/bin/bash
# Copies the website from the cPanel Git repository into the public web folder.
# Run by cPanel's "Deploy HEAD Commit" (see .cpanel.yml and DEPLOY-CPANEL.md).
#
# Safe to run on every update:
#  - your content is never overwritten: messages, settings, blog posts, admin
#    login (storage/) and uploaded photos (assets/uploads/) are not in git
#  - the PHP version setting cPanel adds to .htaccess (MultiPHP Manager) is kept
#  - local-only developer files are left out
set -euo pipefail

SRC="$(cd "$(dirname "$0")/.." && pwd)"
DEST="${DEPLOYPATH:-$HOME/public_html}"
DEST="${DEST%/}"

if [ "$SRC" = "$DEST" ]; then
  echo "The repository is the web folder itself; nothing to copy."
  exit 0
fi
mkdir -p "$DEST"

# Keep cPanel's PHP version block from the live .htaccess, if there is one.
HANDLER=""
if [ -f "$DEST/.htaccess" ]; then
  HANDLER="$(sed -n '/# php -- BEGIN cPanel-generated handler/,/# php -- END cPanel-generated handler/p' "$DEST/.htaccess")"
fi

# Copy the site (tar keeps hidden files such as .htaccess and .user.ini).
tar -C "$SRC" \
  --exclude=./.git --exclude=./.gitignore --exclude=./.gitattributes \
  --exclude=./.cpanel.yml --exclude=./deploy --exclude=./DEPLOY-CPANEL.md \
  --exclude=./Dockerfile --exclude=./docker-compose.yml --exclude=./.dockerignore \
  --exclude='./start-*' --exclude=./start.php --exclude=./router.php \
  --exclude=./LOCAL-SETUP.md --exclude=./README.md \
  -cf - . | tar -C "$DEST" -xf -

if [ -n "$HANDLER" ] && ! grep -q 'BEGIN cPanel-generated handler' "$DEST/.htaccess"; then
  printf '\n%s\n' "$HANDLER" >> "$DEST/.htaccess"
fi

# Folders the website writes to.
mkdir -p "$DEST/storage" "$DEST/assets/uploads"
chmod 755 "$DEST/storage" "$DEST/assets/uploads"

echo "Deployed $(git -C "$SRC" rev-parse --short HEAD 2>/dev/null || echo 'latest version') to $DEST"
