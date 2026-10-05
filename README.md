# Enoma Digital Technologies — Website

Website and content management system for **Enoma Digital Technologies** (EnomaDigitalTech.com).
*Build. Secure. Empower.*

- Plain **PHP 8.1+**, no database, no Composer, no build step: runs on any shared host (cPanel etc.)
- A built-in **admin panel** at `/admin` to manage every page, address, image, email setting and color
- Hand-written CSS and vanilla JavaScript; self-hosted fonts; [Lucide](https://lucide.dev) icons
- SEO: clean, configurable URLs, sitemap, canonical tags, Open Graph / X cards, Schema.org data
- Accessibility: tested with axe in every color palette; keyboard friendly; reduced-motion support

## Run it on your computer

See **[LOCAL-SETUP.md](LOCAL-SETUP.md)**. In short: download and unzip the website, then
double-click `start-windows.bat` (Windows) or `start-mac.command` (Mac). The first time, it
installs PHP and everything else for you; after that it starts the site in seconds at
`http://127.0.0.1:8000`.
Docker users can run `docker compose up --build` instead.

## Put it on your web hosting

**With cPanel Git™ Version Control (recommended):** see **[DEPLOY-CPANEL.md](DEPLOY-CPANEL.md)**.
The website is copied from GitHub into `public_html`, and future updates take two clicks.

**By uploading files instead:**

1. Upload **everything in this folder** to your web root (usually `public_html/`), including the hidden
   `.htaccess` and `.user.ini` files.
2. Make sure the site uses PHP 8.1 or newer (cPanel → *Select PHP Version* / *MultiPHP Manager*).
3. Make sure `storage/` and `assets/uploads/` are writable (usually already true; `755` or `775`).
4. Visit `https://yourdomain.com/admin`. It asks for a one-time setup code, saved in
   `storage/admin/setup-code.txt` (or `enoma-storage/admin/setup-code.txt` when deployed with
   cPanel Git; the admin page shows the exact place); open it with your hosting File Manager. Then choose your
   username and password. Do this right after uploading, so nobody else can claim the admin.
5. In **Admin → Photos**, click **Save photos to this website** (stores the site's photos on your
   hosting so they load fast).
6. In the admin, go through the **Launch checklist** on the dashboard (email, contact details,
   founder profile, legal dates and so on).
7. Once SSL is active, uncomment the HTTPS redirect (and optionally HSTS) in `.htaccess`.
8. Submit `https://yourdomain.com/sitemap.xml` in Google Search Console and Bing Webmaster Tools.

Image uploads up to 25 MB work on hosts that read `.user.ini`. If large uploads fail, raise
`upload_max_filesize` and `post_max_size` in cPanel → MultiPHP INI Editor.

## The admin panel

| Page | What you manage |
| --- | --- |
| **Dashboard** | Launch checklist, latest messages, quick actions |
| **Messages** | Every contact, quote, consultation and service inquiry (always saved here). Read, reply, delete, export CSV |
| **Blog posts** | Write, schedule, draft and publish articles with cover images, categories, tags and SEO fields |
| **Tech & security news** | Latest headlines from CISA, Krebs, BleepingComputer, The Hacker News, SANS and more, plus go-to links. "Write a post about this" starts a draft |
| **Brand & navigation** | Business name, taglines, menu, header buttons, footer links |
| **Homepage** | Every homepage section |
| **Services** | Add, edit, reorder or remove services; each gets its own page and address |
| **Pages** | SEO titles/descriptions and header text for every other page |
| **FAQs, Training, Resources, Testimonials** | Lists you can add to, reorder and remove. Each resource guide has its own page |
| **Photos, Media library** | Every photo on the site at a glance. Change any photo by pasting a link from unsplash.com (free photos) or uploading your own. **Save photos to this website** stores copies on your own site so they load faster and don't depend on Unsplash. Uploads are automatically rotated, resized, compressed and cropped to fit, with a focus point |
| **Page URLs** | The web address of every page (e.g. `/about` → `/about-us`). Old addresses redirect automatically and all links update |
| **Blog settings, News sources, Legal pages** | Blog text and categories, news feeds, Privacy Policy and Terms |
| **Email** | Send form submissions by **SMTP** (Gmail, Outlook/Microsoft 365, Zoho, your host), **Resend API** or PHP mail. Optional automatic reply to visitors. **Send test** button |
| **Appearance** | Dark or light background, four color palettes (Midnight Blue, Obsidian, Deep Ocean, Royal Violet) and an optional custom accent color with automatic contrast checking |
| **Settings** | Contact details, social links, founder profile, scheduling link, AI assistant key |
| **Account, Backup** | Change your login; download or restore all content |

Setup reminders and placeholders (like an empty founder name) are only shown to you while you are
logged in. Visitors never see unfinished sections.

### Addresses (URLs)

Every page has its own address: `/services`, `/about`, `/blog`, `/blog/post-address`, `/resources`,
`/resources/guide-address`, one per service (e.g. `/cybersecurity`), `/contact`, `/get-a-quote`,
`/book-a-consultation`, `/faq`, `/privacy-policy`, `/terms-of-service`. Change them in
**Page URLs**, or on each service, guide or post. When an address changes, the old one returns a
permanent (301) redirect to the new one, so search rankings and shared links keep working.

### Email

- **SMTP:** use the Quick setup buttons, then enter your username and password. Gmail and Outlook
  need an *app password* when two-step verification is on.
- **Resend:** create an API key at resend.com and verify your domain there; use a "from" address on
  that domain.
- Every submission is also saved in **Messages**, so nothing is lost if email has a problem; the
  error is shown on the message.

### AI assistant

Without an API key, the assistant answers from your site content ("guided mode"). Add an Anthropic
API key in Settings to use Claude. The key stays on the server. Every message is screened first:
the assistant always says it is an AI, declines legal, medical or financial advice and refuses
offensive-security requests.

## How it works

```
index.php                Front controller: resolves every address (Admin → Page URLs) and redirects
pages/                   Page templates (home, services, about, blog, resources, contact, legal…)
admin/                   Admin panel
api/ai-assistant.php     AI assistant endpoint
includes/
  bootstrap.php          Loaded on every request
  config.php             Defaults; Admin → Settings/Email/Appearance override them
  routing.php            Addresses, redirects and automatic link updating
  content/               Original content (the admin saves edits to storage/content/*.json)
  components/, layout/, templates/   Reusable UI
  mailer.php             SMTP, Resend and PHP mail()
  media.php              Image processing for uploads
  photos.php             Saves the site's Unsplash photos onto your own hosting
  appearance.php         Palettes and custom accent color
  blog.php, forms.php, functions.php, assistant-engine.php, news.php, storage.php
assets/                  CSS, JS, fonts, images; uploads in assets/uploads/
storage/                 Admin data: content edits, messages, blog posts, settings (not web accessible)
router.php, start.php    Local server (see LOCAL-SETUP.md)
Dockerfile, docker-compose.yml   Optional local Apache setup
```

Content you edit in the admin is stored in `storage/`; the originals in `includes/content/` stay as
the fallback ("Reset to original" in each admin section). Advanced: a private `enoma-config.php`
one level above the web root can also set values (see `config.sample.php`).

## Security

- Content-Security-Policy and other security headers; private folders blocked by `.htaccess`
- Admin: one-time setup code, hashed passwords, login throttling, CSRF protection, 2-hour idle logout
- Forms: CSRF, honeypot, minimum fill time, server-side validation
- Uploads: real images only, re-encoded, metadata removed, scripts can't run in the uploads folder
- Secrets (SMTP password, API keys) are never sent back to the browser; backups leave them out
