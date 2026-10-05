# Put the website live with cPanel Git™ Version Control

cPanel copies the website from GitHub into your `public_html` folder. When you change the
website later, two clicks bring the update live. Your content (messages, settings, blog
posts, uploaded photos and admin login) is never overwritten by an update.

The GitHub repository is public, so no keys or passwords are needed.

## First time (about 10 minutes)

1. **Check PHP.** cPanel → **MultiPHP Manager** (or *Select PHP Version*): choose **PHP 8.1 or
   newer** for your domain. If you can choose extensions, tick `mbstring`, `gd`, `curl`,
   `fileinfo`, `exif` and `openssl` (usually already on).
2. **Clean the web folder.** cPanel → **File Manager** → `public_html`. Delete any placeholder
   files from your host, such as `index.html`, `default.html` or a "coming soon" page.
   Leave `.well-known` and `cgi-bin` alone if they are there.
3. **Connect GitHub.** cPanel → **Git™ Version Control** → **Create**:
   - *Clone a Repository*: on
   - *Clone URL*: `https://github.com/mrdeecyberworld/enomadigitaltechnology.git`
   - *Repository Path*: `repositories/enomadigitaltechnology` (keep it **outside** `public_html`)
   - *Repository Name*: `Enoma website`
   - Click **Create** and wait for it to finish.
4. **Publish.** On the Git™ Version Control list, click **Manage** next to the repository
   → **Pull or Deploy** tab → **Deploy HEAD Commit**. After a few seconds, *Last Deployment
   Information* shows the date. The website is now in `public_html`.
5. **Create your admin login.** Visit `https://yourdomain.com/admin`. It asks for a setup
   code: in **File Manager**, open `public_html/storage/admin/setup-code.txt` and copy the
   code. Then choose your username and password.
6. **Finish setup in the admin:** Photos → **Save photos to this website**, Email, Settings
   (contact details) and the **Launch checklist** on the dashboard.
7. **Turn on HTTPS:** once your SSL certificate is active (cPanel → **SSL/TLS Status** →
   *Run AutoSSL*), open `public_html/.htaccess` in File Manager and remove the `#` in front of
   the two HTTPS redirect lines near the top.

## Updating the website later

1. cPanel → **Git™ Version Control** → **Manage** → **Pull or Deploy**.
2. Click **Update from Remote** (downloads the latest version from GitHub).
3. Click **Deploy HEAD Commit** (copies it to `public_html`).

## Good to know

- **What deploy does:** it runs `deploy/cpanel-deploy.sh` (set up in `.cpanel.yml`), which
  copies the website into `public_html`. It keeps your content, the PHP version setting
  cPanel adds to `.htaccess`, and anything else already in the folder, and it skips files
  that are only for running the site on your computer.
- **A different folder** (for example an addon domain): change `DEPLOYPATH` in `.cpanel.yml`.
- **"Deploy HEAD Commit" is greyed out:** the repository in cPanel has local changes. Never
  edit files inside `repositories/`; edit through the admin panel or in GitHub instead.
- **Edits made in File Manager** to website files in `public_html` are replaced by the next
  deploy. Your admin content is not.
- **Backups:** Admin → Backup downloads all your content; cPanel → **Backup** saves everything.
