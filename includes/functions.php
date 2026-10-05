<?php
/**
 * Core helpers: config, content, escaping, URLs, icons, images, SEO.
 */

declare(strict_types=1);

/** Read a config value using dot notation, e.g. cfg('ai.model'). */
function cfg(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/**
 * Load a content section. Content edited in the admin panel
 * (storage/content/{name}.json) wins over the built-in defaults in
 * includes/content/{name}.php. Cached per request.
 */
function content(string $name): array
{
    static $cache = [];
    if (!isset($cache[$name])) {
        $override = json_read(content_override_file($name));
        $cache[$name] = is_array($override) ? $override : content_default($name);
    }
    return $cache[$name];
}

function site(string $key): mixed
{
    return content('site')[$key] ?? null;
}

/** All services, with sensible defaults for any optional field left blank in the admin. */
function services(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    foreach (content('services') as $slug => $svc) {
        $name = (string) ($svc['name'] ?? '') ?: ucwords(str_replace('-', ' ', (string) $slug));
        $summary = (string) ($svc['summary'] ?? '');
        $fill = static fn (string $k, string $default): string => ((string) ($svc[$k] ?? '')) !== '' ? (string) $svc[$k] : $default;
        $cache[$slug] = array_merge($svc, [
            'name'             => $name,
            'nav_label'        => $fill('nav_label', $name),
            'short_label'      => $fill('short_label', $name),
            'icon'             => $fill('icon', 'circle-help'),
            'summary'          => $summary,
            'cta'              => $fill('cta', 'Learn about ' . $name),
            'eyebrow'          => $fill('eyebrow', $name),
            'headline'         => $fill('headline', $name),
            'intro'            => $fill('intro', $summary),
            'image'            => (string) ($svc['image'] ?? ''),
            'meta_title'       => $fill('meta_title', $name . ' | ' . site('name')),
            'meta_description' => $fill('meta_description', $summary ?: (string) site('description')),
            'includes'         => array_values((array) ($svc['includes'] ?? [])),
            'details'          => array_values((array) ($svc['details'] ?? [])),
            'audience'         => array_values((array) ($svc['audience'] ?? [])),
            'faqs'             => array_values((array) ($svc['faqs'] ?? [])),
        ]);
    }
    return $cache;
}

/** A service by its address; also finds services whose address was changed. */
function service(string $slug): ?array
{
    return services()[service_key($slug)] ?? null;
}

/** Current key (address) of a service, following any address change. */
function service_key(string $slug): string
{
    if (isset(services()[$slug])) {
        return $slug;
    }
    $moved = ltrim(link_path('/' . $slug), '/');
    return isset(services()[$moved]) ? $moved : $slug;
}

/** Public path for a service (the training service lives at /training). */
function service_path(string $slug): string
{
    return '/' . service_key($slug);
}

/** HTML-escape. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Absolute URL for canonical / Open Graph / sitemap use. */
function abs_url(string $path = '/'): string
{
    return rtrim((string) cfg('base_url'), '/') . link_path($path);
}

/** Versioned asset URL for long-term browser caching. */
function asset(string $path): string
{
    $file = SITE_ROOT . '/assets/' . ltrim($path, '/');
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return '/assets/' . ltrim($path, '/') . '?v=' . $version;
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = preg_replace('/\.php$/', '', $path);
    $path = $path === '/index' ? '/' : rtrim($path, '/');
    return $path === '' ? '/' : $path;
}

function is_current(string $path): bool
{
    return current_path() === link_path($path);
}

/** Inline Lucide icon. Decorative by default (aria-hidden). */
function icon(string $name, string $class = 'icon', ?string $label = null): string
{
    static $icons = null;
    $icons ??= require INC . '/icon-data.php';
    $paths = $icons[$name] ?? $icons['circle-help'];
    $a11y = $label !== null
        ? 'role="img" aria-label="' . e($label) . '"'
        : 'aria-hidden="true" focusable="false"';
    return '<svg class="' . e($class) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" ' . $a11y . '>' . $paths . '</svg>';
}

/**
 * Responsive photo. Uses a local override in assets/img/photos/{key}.(webp|jpg|png)
 * when present, otherwise the Unsplash CDN with automatic format and sizing.
 */
function photo(string $key, string $sizes = '(min-width: 1024px) 50vw, 100vw', array $opts = []): string
{
    $img = content('images')[$key] ?? null;
    // A direct path to an uploaded image (e.g. chosen for a service in the admin).
    if ($img === null && preg_match('#^/?assets/[A-Za-z0-9_./-]+$#', $key) && !str_contains($key, '..')) {
        $img = ['file' => ltrim($key, '/'), 'alt' => '', 'id' => ''];
    }
    if ($img === null) {
        return '';
    }
    $alt = $opts['alt'] ?? ($img['alt'] ?? '');
    $eager = !empty($opts['eager']);
    $class = $opts['class'] ?? '';
    $w = (int) ($img['w'] ?? 1600);
    $h = (int) ($img['h'] ?? 1067);

    $candidates = [];
    if (!empty($img['file'])) {
        $candidates[] = preg_replace('#^/?assets/#', '', $img['file']);
    }
    foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
        $candidates[] = 'img/photos/' . $key . '.' . $ext;
    }
    // The Unsplash photo saved onto this website (Admin → Photos → Save photos).
    if (($stock = stock_local($key, (string) ($img['id'] ?? ''))) !== null) {
        $candidates[] = $stock;
    }
    foreach ($candidates as $local) {
        if (is_file(SITE_ROOT . '/assets/' . $local)) {
            $src = asset($local);
            $srcset = media_srcset($local);
            $focus = media_focus_class($local);
            if ($focus === '' && !empty($img['focus']) && preg_match('/^[a-z-]+$/', (string) $img['focus'])) {
                $focus = 'focus-' . $img['focus']; // crop focus set for a built-in photo
            }
            if ($focus !== '') {
                $class = trim($class . ' ' . $focus);
            }
            $meta = media_meta(basename($local));
            if (!empty($meta['w'])) {
                [$w, $h] = [(int) $meta['w'], (int) $meta['h']];
            } elseif ($size = @getimagesize(SITE_ROOT . '/assets/' . $local)) {
                [$w, $h] = $size;
            }
            break;
        }
    }
    if (!isset($src) && empty($img['id'])) {
        return '';
    }

    if (!isset($src)) {
        $base = 'https://images.unsplash.com/photo-' . $img['id'] . '?auto=format&fit=crop&q=85';
        $widths = [480, 768, 1080, 1440, 1920, 2400];
        $srcset = implode(', ', array_map(static fn (int $width) => $base . '&w=' . $width . ' ' . $width . 'w', $widths));
        $src = $base . '&w=1080';
        // A built-in backup photo the browser switches to if Unsplash can't be reached (see main.js).
        $backup = content_default('fallback-photos')[$key] ?? null;
        if ($backup) {
            $extra = ' data-fallback="' . e(asset($backup['file'])) . '" data-fallback-alt="' . e($backup['alt']) . '"'
                . (!empty($backup['focus']) ? ' data-fallback-class="focus-' . e($backup['focus']) . '"' : '');
        }
    }

    return sprintf(
        '<img src="%s"%s sizes="%s" alt="%s" width="%d" height="%d" class="%s" loading="%s" decoding="async"%s%s>',
        e($src),
        $srcset !== '' ? ' srcset="' . e($srcset) . '"' : '',
        e($sizes),
        e($alt),
        $w,
        $h,
        e($class),
        $eager ? 'eager' : 'lazy',
        $eager ? ' fetchpriority="high"' : '',
        $extra ?? ''
    );
}

/** Security headers (also set in .htaccess where mod_headers is available). */
function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https://images.unsplash.com; script-src 'self'; style-src 'self'; font-src 'self'; connect-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'");
}

/** Output a JSON-LD block. */
function json_ld(array $data): string
{
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . '</script>';
}

/** Organization + WebSite graph shared by every page. Only true, known facts. */
function schema_base(): array
{
    $sameAs = array_values(array_filter(cfg('social', [])));
    $org = [
        '@type' => ['Organization', 'ProfessionalService'],
        '@id'   => abs_url('/#organization'),
        'name'  => site('name'),
        'alternateName' => site('short_name'),
        'url'   => abs_url('/'),
        'logo'  => abs_url('/assets/img/logo.png'),
        'image' => abs_url('/assets/img/og-image.png'),
        'slogan' => site('tagline'),
        'description' => site('description'),
        'areaServed' => 'Worldwide',
        'knowsAbout' => ['Web development', 'Website design', 'Cybersecurity', 'Cybersecurity awareness training', 'IT support', 'Cloud services', 'Technology consulting'],
    ];
    if (cfg('contact_email')) {
        $org['email'] = cfg('contact_email');
    }
    if (cfg('contact_phone')) {
        $org['telephone'] = cfg('contact_phone');
    }
    if ($sameAs) {
        $org['sameAs'] = $sameAs;
    }

    return [
        $org,
        [
            '@type' => 'WebSite',
            '@id'   => abs_url('/#website'),
            'url'   => abs_url('/'),
            'name'  => site('name'),
            'alternateName' => site('short_name'),
            'publisher' => ['@id' => abs_url('/#organization')],
            'inLanguage' => 'en-US',
        ],
    ];
}

function schema_faq(array $faqs): array
{
    return [
        '@type' => 'FAQPage',
        'mainEntity' => array_map(static fn (array $f) => [
            '@type' => 'Question',
            'name'  => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ], $faqs),
    ];
}

/** @param array<int, array{0:string,1:string}> $crumbs [label, path] pairs */
function schema_breadcrumbs(array $crumbs): array
{
    $items = [];
    foreach (array_values($crumbs) as $i => [$label, $path]) {
        $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $label, 'item' => abs_url($path)];
    }
    return ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

function schema_service(string $slug, array $svc): array
{
    return [
        '@type' => 'Service',
        '@id'   => abs_url(service_path($slug) . '#service'),
        'name'  => $svc['name'],
        'serviceType' => $svc['name'],
        'description' => $svc['summary'],
        'provider' => ['@id' => abs_url('/#organization')],
        'areaServed' => 'Worldwide',
        'url' => abs_url(service_path($slug)),
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name'  => $svc['name'],
            'itemListElement' => array_map(static fn (string $item) => [
                '@type' => 'Offer',
                'itemOffered' => ['@type' => 'Service', 'name' => $item],
            ], $svc['includes']),
        ],
    ];
}

/** Show a notice to the site owner when a setup step is outstanding. */
/**
 * Is the person viewing the site logged in to the admin? Only checks when an
 * admin session cookie exists, so ordinary visitors never get a session.
 */
function viewer_is_admin(): bool
{
    static $is = null;
    if ($is === null) {
        $is = false;
        if (isset($_COOKIE['enoma_sid'])) {
            start_session();
            $is = !empty($_SESSION['admin_user']) && time() - (int) ($_SESSION['admin_seen'] ?? 0) < 7200;
        }
    }
    return $is;
}

/** Show a setup reminder. Only visible to you while logged in to the admin. */
function setup_notice(string $message): string
{
    if (!cfg('setup_notices') || !viewer_is_admin()) {
        return '';
    }
    return '<p class="setup-notice" role="note">' . icon('circle-alert', 'icon icon-sm') . '<span><strong>Setup note:</strong> ' . e($message) . '</span></p>';
}

/** Placeholder text for owner-supplied details that are not filled in yet. */
function placeholder(string $label): string
{
    return viewer_is_admin() ? '<span class="placeholder-text" title="Only you can see this while logged in">[' . e($label) . ']</span>' : '';
}

/** Has the founder profile been filled in (Admin → Settings)? */
function founder_ready(): bool
{
    $f = cfg('founder', []);
    return trim((string) ($f['name'] ?? '')) !== '';
}

/** Page SEO/header text from the "pages" content section. */
function page_text(string $page, string $key, string $fallback = ''): string
{
    $value = content('pages')[$page][$key] ?? '';
    return is_string($value) && $value !== '' ? $value : $fallback;
}

/**
 * Safe text formatting for admin-edited long text (legal pages, blog posts):
 *   ## Heading   ### Subheading   - bullet   1. numbered   > quote
 *   **bold**   *italic*   [link](/path or https://...)   ![alt text](assets/uploads/photo.jpg)
 * Blank lines separate blocks. Everything is HTML-escaped first; only these
 * patterns become markup, so pasted HTML or scripts are shown as plain text.
 */
function simple_format(string $text, int $headingLevel = 2): string
{
    $inline = static function (string $s): string {
        $s = e($s);
        $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s);
        $s = preg_replace('/(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])/s', '<em>$1</em>', $s);
        return preg_replace_callback('/\[([^\]]+)\]\(((?:https?:\/\/|\/|mailto:)[^)\s]*)\)/', static function ($m) {
            $external = str_starts_with($m[2], 'http');
            return '<a href="' . $m[2] . '"' . ($external ? ' rel="noopener noreferrer" target="_blank"' : '') . '>' . $m[1] . '</a>';
        }, $s);
    };
    $sub = min(6, $headingLevel + 1);
    $html = '';
    foreach (preg_split('/\n\s*\n/', str_replace("\r", '', trim($text))) as $block) {
        $lines = array_values(array_filter(array_map('rtrim', explode("\n", $block)), 'strlen'));
        if (!$lines) {
            continue;
        }
        $first = $lines[0];
        if (count($lines) === 1 && preg_match('/^###\s+(.*)$/', $first, $m)) {
            $html .= '<h' . $sub . '>' . $inline($m[1]) . '</h' . $sub . '>';
        } elseif (count($lines) === 1 && preg_match('/^##\s+(.*)$/', $first, $m)) {
            $html .= '<h' . $headingLevel . '>' . $inline($m[1]) . '</h' . $headingLevel . '>';
        } elseif (count($lines) === 1 && preg_match('/^!\[([^\]]*)\]\(\/?(assets\/[A-Za-z0-9_\/.-]+|https:\/\/[^)\s]+)\)$/', trim($first), $m) && !str_contains($m[2], '..')) {
            $src = str_starts_with($m[2], 'https://') ? $m[2] : '/' . $m[2];
            $html .= '<figure class="prose-figure"><img src="' . e($src) . '" alt="' . e($m[1]) . '" loading="lazy" decoding="async">'
                . ($m[1] !== '' ? '<figcaption>' . e($m[1]) . '</figcaption>' : '') . '</figure>';
        } elseif (preg_match('/^\s*[-*]\s+/', $first)) {
            $html .= '<ul>' . implode('', array_map(static fn ($l) => '<li>' . $inline(preg_replace('/^\s*[-*]\s+/', '', $l)) . '</li>', $lines)) . '</ul>';
        } elseif (preg_match('/^\s*\d+[.)]\s+/', $first)) {
            $html .= '<ol>' . implode('', array_map(static fn ($l) => '<li>' . $inline(preg_replace('/^\s*\d+[.)]\s+/', '', $l)) . '</li>', $lines)) . '</ol>';
        } elseif (preg_match('/^>\s?/', $first)) {
            $html .= '<blockquote><p>' . implode('<br>', array_map(static fn ($l) => $inline(preg_replace('/^>\s?/', '', $l)), $lines)) . '</p></blockquote>';
        } else {
            $html .= '<p>' . implode('<br>', array_map($inline, $lines)) . '</p>';
        }
    }
    return $html;
}

/** Resource guides (Admin → Resources), each with its own page. */
function resource_url(array $guide): string
{
    return page_url('resources', '/' . $guide['slug']);
}

function resource_find(string $slug): ?array
{
    foreach (content('resources') as $g) {
        if (($g['slug'] ?? '') === $slug) {
            return $g;
        }
    }
    return null;
}

/* ---------- Courses & Tools (shop) ---------- */

/** Published products (all products, including drafts, when $includeDrafts is true). */
function shop_products(bool $includeDrafts = false): array
{
    $out = [];
    foreach ((array) (content('shop')['products'] ?? []) as $p) {
        if (!is_array($p) || trim((string) ($p['name'] ?? '')) === '' || trim((string) ($p['slug'] ?? '')) === '') {
            continue;
        }
        if (!$includeDrafts && ($p['status'] ?? 'published') !== 'published') {
            continue;
        }
        $out[] = $p + ['type' => 'Course', 'price' => '', 'price_note' => '', 'image' => '', 'summary' => '', 'description' => '', 'includes' => [], 'format' => '', 'buy_url' => '', 'buy_label' => '', 'featured' => false];
    }
    // Featured first, otherwise in the order you listed them.
    usort($out, static fn ($a, $b) => (int) !empty($b['featured']) <=> (int) !empty($a['featured']));
    return $out;
}

function shop_find(string $slug, bool $includeDrafts = false): ?array
{
    foreach (shop_products($includeDrafts) as $p) {
        if ($p['slug'] === $slug) {
            return $p;
        }
    }
    return null;
}

function shop_url(array $p): string
{
    return page_url('shop', '/' . $p['slug']);
}

/** Numeric price (for structured data), or null when the price isn't a plain number. */
function shop_price_value(array $p): ?float
{
    $raw = preg_replace('/[^0-9.]/', '', (string) $p['price']);
    return $raw !== '' && is_numeric($raw) ? (float) $raw : null;
}

/** Price as shown to visitors: "$49", "$49.50", "Free" or the text you typed. */
function shop_price(array $p): string
{
    $text = trim((string) $p['price']);
    if ($text === '') {
        return '';
    }
    $n = shop_price_value($p);
    if ($n === null || !preg_match('/^\$?\s*[0-9][0-9,]*(\.[0-9]{1,2})?$/', $text)) {
        return $text;
    }
    return $n == 0.0 ? 'Free' : '$' . number_format($n, fmod($n, 1.0) == 0.0 ? 0 : 2);
}

/** Whether the shop has anything to show visitors (the menu link is hidden until then). */
function shop_is_open(): bool
{
    return shop_products() !== [];
}

/** Menu links from Brand & navigation, minus the shop link while nothing is for sale (admins always see it). */
function nav_items(string $which = 'nav'): array
{
    $shop = page_url('shop');
    $open = shop_is_open() || viewer_is_admin();
    return array_values(array_filter((array) site($which), static fn ($item) => $open || link_path((string) ($item['path'] ?? '')) !== $shop));
}
