# Run the website on your computer

You can run the full website, including the admin panel, forms, blog and image uploads,
on your own Windows, Mac or Linux computer. Nothing is published to the internet; the site is
only reachable from your computer at an address like `http://127.0.0.1:8000`.

There are two ways. **Option A is the easiest.**

---

## Option A: One double-click (recommended)

### 1. Get the website files

Download the ZIP (sign in to GitHub first if the repository is private):
**https://github.com/mrdeecyberworld/enomadigitaltechnology/archive/refs/heads/claude/enoma-digital-website-avzxco.zip**

Unzip it somewhere easy, such as your Desktop or Documents.
(If you use Git: `git clone` the repository and check out the branch `claude/enoma-digital-website-avzxco`.)

### 2. Double-click the start file

| Computer | What to do |
| --- | --- |
| Windows | Double-click **`start-windows.bat`**. If Windows shows "Windows protected your PC", click **More info → Run anyway** |
| Mac | Double-click **`start-mac.command`**. The first time, macOS may block it: right-click it → **Open** → **Open** |
| Linux | In a terminal in the website folder: `./start-linux.sh` |

**The first time**, the script installs everything the website needs. Type **Y** when it
asks:

- **Windows:** installs PHP with the Windows Package Manager (or downloads it from
  windows.php.net if that isn't available), the Microsoft Visual C++ runtime if your PC
  lacks it (click **Yes** if Windows asks for permission), and the security certificates
  used for email and news. About 1–3 minutes. Nothing needs administrator rights except
  that runtime.
- **Mac:** installs Homebrew (the standard Mac installer for developer tools) and PHP.
  About 5–10 minutes. Type your Mac password when asked (nothing appears while you
  type; that's normal).
- **Linux:** installs PHP and its extensions with your package manager (asks for your
  password).

After that, every double-click starts the website in a few seconds.

### 3. The website opens

A window shows something like:

```
  Website:  http://127.0.0.1:8000
  Admin:    http://127.0.0.1:8000/admin
  Setup code for your first admin login: 3F9A21C0
```

Your browser opens the website automatically. **Keep that window open** while you use the
site. To stop the server, click the window and press **Ctrl + C** (or just close it).

### 4. Log in to the admin

Go to `http://127.0.0.1:8000/admin`, enter the **setup code** shown in the window, and choose
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

- **Photos:** the first time you start the site with internet, the start window saves all the
  site's photos onto your computer ("Saving 17 photos..."). After that they work offline.
  Images you upload in the admin always work offline.
- **Email** works locally too: set it up in Admin → Email and use **Save & send test**.
  (PHP mail() usually does *not* work on a personal computer; use SMTP or Resend.)
- **News headlines** (Admin → Tech & security news) need an internet connection.
- **Moving to real hosting:** upload the whole folder. If you want to keep the content you
  created locally, also upload the `storage` and `assets/uploads` folders, or use
  Admin → Backup to download your content and restore it on the live site.

## Troubleshooting

**The automatic install didn't work**
Install PHP yourself, then double-click the start file again:
- Windows: open PowerShell and run `winget install PHP.PHP.8.3`, or install XAMPP from
  https://www.apachefriends.org (the start file finds it automatically).
- Mac: install Homebrew from https://brew.sh, then run `brew install php` in Terminal.
- Linux: `sudo apt install php-cli php-mbstring php-gd php-curl php-xml`

**Windows: "VCRUNTIME140.dll was not found"**
Install the Microsoft Visual C++ runtime from https://aka.ms/vs/17/release/vc_redist.x64.exe,
then start again.

**Missing extensions ("mbstring" or others)**
- Windows (winget PHP): find `php.ini-development` in the PHP folder (run `where php` in
  PowerShell to see the folder), copy it to `php.ini`, open it in Notepad and remove the `;`
  at the start of these lines: `extension=mbstring`, `extension=gd`, `extension=curl`,
  `extension=openssl`, `extension=fileinfo`, `extension=exif`. Save and start again.
- Mac (Homebrew): `brew reinstall php`
- Linux: `sudo apt install php-mbstring php-gd php-curl`

**Browser says "This site can't be reached" / "refused to connect"**
The website only runs while its start window is open. Look at that window:
- Still installing? Wait until it says *The website is running*; the browser then opens by itself.
- Window closed, or shows *[Process completed]*? Double-click the start file again.
- An error message? Follow it, or send a screenshot of the window to your developer.

Mac: if double-clicking does nothing or says it can't be opened, open **Terminal**, type
`bash ` (with a space), drag `start-mac.command` into the Terminal window, and press Enter.

**"Port is busy"**
Another program is using ports 8000–8020. Close other local servers or restart your computer.

**I forgot my local admin password**
Delete the file `storage/admin/users.json`, then run the start script again; it shows a new
setup code.
