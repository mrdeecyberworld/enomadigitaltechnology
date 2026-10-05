#!/bin/bash
# Copies the website from the cPanel Git repository into the public web folder.
# Run by cPanel's "Deploy HEAD Commit" (see .cpanel.yml and DEPLOY-CPANEL.md).
#
# Safe to run on every update:
#  - your content is never overwritten: messages, settings, blog posts and the
#    admin login live in ~/enoma-storage (outside the web folder, created here)
#    and uploaded photos in assets/uploads/; none of these are in git
#  - the website's .htaccess always replaces the old one (a copy is kept as
#    .htaccess.before-deploy), and the deploy stops with an error if it can't
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

# Keep cPanel's PHP version block from the live .htaccess, if there is one,
# then remove the old file so the website's own rules always replace it.
HANDLER=""
if [ -f "$DEST/.htaccess" ]; then
  HANDLER="$(sed -n '/# php -- BEGIN cPanel-generated handler/,/# php -- END cPanel-generated handler/p' "$DEST/.htaccess")"
  cp "$DEST/.htaccess" "$DEST/.htaccess.before-deploy" 2>/dev/null || true
  rm -f "$DEST/.htaccess"
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

# Check that the website's rules file is in place (it routes every page and blocks private files).
if ! grep -q 'Enoma' "$DEST/.htaccess" 2>/dev/null; then
  echo "ERROR: $DEST/.htaccess could not be written. Delete it in File Manager and deploy again." >&2
  exit 1
fi

# Private data (messages, settings, admin login, blog) lives OUTSIDE the web
# folder, in enoma-storage next to it, so it can never be opened from the web.
PRIVATE="$(dirname "$DEST")/enoma-storage"
if [ ! -d "$PRIVATE" ]; then
  mkdir -p "$PRIVATE"
  # Move anything saved so far in the web folder's storage/ (first deploy after this change).
  if [ -d "$DEST/storage" ]; then
    (cd "$DEST/storage" && tar --exclude=./README.md --exclude=./.htaccess -cf - .) | tar -C "$PRIVATE" -xf -
    find "$DEST/storage" -mindepth 1 ! -name README.md ! -name .htaccess -exec rm -rf {} + 2>/dev/null || true
    echo "Moved private data to $PRIVATE"
  fi
fi
chmod 750 "$PRIVATE"

# Folders the website writes to.
mkdir -p "$DEST/storage" "$DEST/assets/uploads"
chmod 755 "$DEST/storage" "$DEST/assets/uploads"

echo "Deployed $(git -C "$SRC" rev-parse --short HEAD 2>/dev/null || echo 'latest version') to $DEST"
