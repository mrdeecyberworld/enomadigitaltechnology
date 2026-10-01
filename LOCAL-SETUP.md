# Run the website on your computer

You can run the full website, including the admin panel, forms, blog and image uploads,
on your own Windows, Mac or Linux computer. Nothing is published to the internet; the site is
only reachable from your computer at an address like `http://localhost:8000`.

There are two ways. **Option A is the easiest.**

---

## Option A: PHP (recommended, about 5 minutes)

### 1. Get the website files

On GitHub, open the repository, switch to the branch `claude/enoma-digital-website-avzxco`,
click **Code → Download ZIP**, and unzip it somewhere easy such as your Desktop.
(If you use Git: `git clone` the repository and check out that branch.)

### 2. Install PHP (one time only)

**Windows**

1. Open **PowerShell** (press the Windows key, type *PowerShell*, press Enter).
2. Run:
   ```
   winget install PHP.PHP.8.3
   ```
3. Close PowerShell when it finishes.

No winget? Install **XAMPP** from https://www.apachefriends.org instead; the start script
finds it automatically.

**Mac**

1. Install Homebrew: open **Terminal** and paste the command from https://brew.sh
2. Then run:
   ```
   brew install php
   ```

**Linux (Ubuntu/Debian)**

```
sudo apt install php-cli php-mbstring php-gd php-curl php-xml
```

### 3. Start the website

| Computer | What to do |
| --- | --- |
| Windows | Double-click **`start-windows.bat`** in the website folder |
| Mac | Double-click **`start-mac.command`**. The first time, macOS may block it: right-click it → **Open** → **Open** |
| Linux | In a terminal in the website folder: `./start-linux.sh` |
| Any (terminal) | `php start.php` |

A window opens showing something like:

```
  Website:  http://localhost:8000
  Admin:    http://localhost:8000/admin
  Setup code for your first admin login: 3F9A21C0
```

Your browser opens the website automatically. **Keep that window open** while you use the
site. To stop the server, click the window and press **Ctrl + C** (or just close it).

### 4. Log in to the admin

Go to `http://localhost:8000/admin`, enter the **setup code** shown in the window, and choose
a username and password. Everything you change in the admin is saved in the `storage` folder
on your computer.

---

## Option B: Docker (identical to real web hosting)

Use this if you already have **Docker Desktop** (https://www.docker.com/products/docker-desktop/).
It runs the site with Apache, exactly like shared hosting, including the `.htaccess` rules.

In a terminal, inside the website folder:

```
docker compose up --build
```

Then open **http://localhost:8080** (admin: http://localhost:8080/admin).
The setup code is in the file `storage/admin/setup-code.txt` (created the first time you
visit `/admin`). Stop with **Ctrl + C**.

---

## Good to know

- **Photos from Unsplash** need an internet connection. Images you upload in the admin work offline.
- **Email** works locally too: set it up in Admin → Email and use **Save & send test**.
  (PHP mail() usually does *not* work on a personal computer; use SMTP or Resend.)
- **News headlines** (Admin → Tech & security news) need an internet connection.
- **Moving to real hosting:** upload the whole folder. If you want to keep the content you
  created locally, also upload the `storage` and `assets/uploads` folders, or use
  Admin → Backup to download your content and restore it on the live site.

## Troubleshooting

**"PHP is not installed" / "php is not recognized"**
Close the window, reopen it after installing PHP (Windows needs a fresh window to see it),
then try again.

**Missing extensions ("mbstring" or others)**
- Windows (winget PHP): find `php.ini-development` in the PHP folder (run `where php` in
  PowerShell to see the folder), copy it to `php.ini`, open it in Notepad and remove the `;`
  at the start of these lines: `extension=mbstring`, `extension=gd`, `extension=curl`,
  `extension=openssl`, `extension=fileinfo`, `extension=exif`. Save and start again.
- Mac (Homebrew): `brew reinstall php`
- Linux: `sudo apt install php-mbstring php-gd php-curl`

**"Port is busy"**
Another program is using ports 8000–8020. Close other local servers or restart your computer.

**I forgot my local admin password**
Delete the file `storage/admin/users.json`, then run the start script again; it shows a new
setup code.
