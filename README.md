# Enoma Digital Technologies — Website

Marketing website for **Enoma Digital Technologies** (EnomaDigitalTech.com).
*Build. Secure. Empower.*

Built for **standard shared hosting** (cPanel, Hostinger, Namecheap, Bluehost, etc.):

- PHP 8.1+ with Apache `mod_rewrite` — no database, no Composer, no Node build step
- Hand-written, mobile-first CSS and vanilla JavaScript (about 11 KB CSS + 5 KB JS gzipped, no frameworks)
- Self-hosted variable fonts (Inter + Manrope) and [Lucide](https://lucide.dev) icons inlined as SVG
- Clean URLs, XML sitemap, robots.txt, canonical URLs, Open Graph / X cards, Schema.org JSON-LD
- WCAG-minded: semantic landmarks, skip link, keyboard-accessible menus, visible focus, accessible forms, reduced-motion support

## Deploying to shared hosting

1. Upload **everything in this folder** to your web root (usually `public_html/`), including the hidden `.htaccess` files.
2. Make sure the site runs PHP 8.1 or newer (cPanel → *Select PHP Version* / *MultiPHP Manager*).
3. Copy `config.sample.php` to **one level above** the web root as `enoma-config.php`
   (e.g. `/home/youraccount/enoma-config.php`) and fill in real values. Keeping it outside
   `public_html` means it can never be downloaded.
4. Make `storage/` writable by PHP (usually already true; `755` or `775`).
5. Once SSL is active, uncomment the HTTPS redirect (and optionally HSTS) in `.htaccess`.
6. Submit `https://enomadigitaltech.com/sitemap.xml` in Google Search Console and Bing Webmaster Tools.

### Local development

```bash
php -S localhost:8000 router.php
```

`router.php` mimics the `.htaccess` rules for PHP's built-in server. Set `ENOMA_DEBUG=1` to show PHP errors.

## Integration points (before launch)

| What | Where | Default |
| --- | --- | --- |
| **Form delivery** | `forms.delivery` / `forms.to` in `enoma-config.php`; sending code in `send_form_email()` in `includes/forms.php` | `none` — forms validate, and visitors are told honestly that messages are **not** sent yet |
| **AI assistant** | `ANTHROPIC_API_KEY` env var or `ai.api_key` in `enoma-config.php`; endpoint `api/ai-assistant.php` (`POST /api/ai-assistant`) | *Guided mode*: a local rules-based helper that only uses approved site content |
| **Scheduling link** | `booking_url` | Consultation request form only |
| **Contact email / phone** | `contact_email`, `contact_phone` | Hidden (nothing invented) |
| **Social profiles** | `social` | "Coming soon" placeholders |
| **Founder name, bio, photo** | `founder` | Clearly marked placeholders |
| **Testimonials** | `includes/content/testimonials.php` | Placeholder cards labeled "Client testimonial will appear here." |
| **Legal pages** | `privacy-policy.php`, `terms-of-service.php` | Starting templates — have them reviewed; fill in effective date and governing state |

Set `setup_notices` to `false` once everything is connected to hide the owner-facing yellow "Setup note" boxes.

### AI assistant details

- The API key stays on the server; the browser only talks to `/api/ai-assistant`.
- With a key, the server calls the Claude Messages API (model configurable via `ai.model` / `ENOMA_AI_MODEL`)
  using a system prompt built **only** from the site's content files, so it can't invent company facts.
- Every message passes a safety screen first: the assistant always identifies as AI, declines legal/medical/financial
  advice, and refuses offensive-security requests while offering defensive help.
- If the API fails or declines, the assistant falls back to guided mode automatically.
- Per-visitor rate limiting (`ai.rate_limit` messages per hour) is stored in `storage/ratelimit/`.

## Editing content

All copy lives in `includes/content/`:

| File | Contents |
| --- | --- |
| `site.php` | Brand, navigation, footer links, sitemap entries |
| `services.php` | The six services: summaries, bullet lists, page copy, SEO titles/descriptions, service FAQs |
| `home.php` | Homepage hero, trust bar, Why Enoma, cybersecurity feature, process, about |
| `faqs.php` | General FAQs (homepage, `/faq`, FAQPage schema, AI assistant knowledge) |
| `training.php` | Training audiences, topics and formats |
| `resources.php` | Short educational guides on `/resources` |
| `testimonials.php` | Real client testimonials (empty until provided) |
| `images.php` | Photography (see below) |

### Adding a service

1. Add an entry to `includes/content/services.php` (the key becomes the URL slug).
2. Create `your-slug.php` in the root:
   ```php
   <?php
   $serviceSlug = 'your-slug';
   require __DIR__ . '/includes/templates/service-page.php';
   ```
3. Add `/your-slug` to `sitemap` in `includes/content/site.php`.

Menus, cards, the footer, forms, the service finder and the AI assistant's knowledge update automatically.
To have the service finder recommend it, add answers in `service_finder_data()` (`includes/components/assistant.php`).

### Photography

Photos load from the Unsplash CDN (free for commercial use under the [Unsplash License](https://unsplash.com/license)),
with responsive `srcset`, lazy loading and a styled fallback if a photo can't load. To use your own photography,
put a file named after the key in `assets/img/photos/` — e.g. `assets/img/photos/hero.jpg` — and it is used
automatically. Swap Unsplash photo IDs in `includes/content/images.php`. **Review every photo before launch** to
make sure it fits the brand.

## Structure

```
index.php, services.php, training.php, about.php, resources.php, faq.php,
contact.php, get-a-quote.php, book-a-consultation.php,
web-development.php, cybersecurity.php, it-support.php, cloud-services.php, technology-consulting.php,
privacy-policy.php, terms-of-service.php, 404.php, sitemap.php, robots.txt
api/ai-assistant.php            AI assistant endpoint
assets/css/main.css             Design system + all styles
assets/js/main.js               Navigation, forms, assistant, service finder, reveal animations
assets/fonts/                   Inter + Manrope variable fonts (SIL Open Font License)
assets/img/                     Logo, icons, Open Graph image
includes/bootstrap.php          Loaded by every page
includes/config.php             Defaults + env/private-config overrides
includes/functions.php          Helpers: escaping, URLs, icons, images, schema
includes/forms.php              Validation, CSRF, spam protection, delivery
includes/assistant-engine.php   AI assistant (guided + Claude API)
includes/components/            Reusable UI: buttons, section headers, service cards, CTA, FAQ,
                                testimonials, contact form, AI assistant, service finder
includes/layout/                Header (head, nav) and footer
includes/templates/             Service page and form page templates
includes/content/               Editable content
storage/                        Runtime data (not web accessible)
```

## Security

- Content-Security-Policy, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`
- CSRF tokens, honeypot field and minimum fill time on all forms; server-side validation; Post/Redirect/Get
- `includes/` and `storage/` are blocked from the web; private config lives outside the web root
- AI replies are rendered as text (never HTML); only same-site links are shown
