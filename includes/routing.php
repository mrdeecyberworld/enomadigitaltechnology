<?php
/**
 * URL routing: configurable page addresses, automatic redirects from old
 * addresses, and link rewriting so every link always points to the current one.
 */

declare(strict_types=1);

/** Page key => template file in pages/. */
const PAGE_FILES = [
    'services'     => 'services.php',
    'about'        => 'about.php',
    'blog'         => 'blog.php',
    'resources'    => 'resources.php',
    'faq'          => 'faq.php',
    'contact'      => 'contact.php',
    'quote'        => 'quote.php',
    'consultation' => 'consultation.php',
    'privacy'      => 'privacy.php',
    'terms'        => 'terms.php',
    'credits'      => 'credits.php',
    'shop'         => 'shop.php',
    'feedback'     => 'feedback.php',
];

/** Addresses that can never be used for pages. */
const RESERVED_SLUGS = ['theme.css', 'admin', 'api', 'assets', 'storage', 'includes', 'pages', 'index', 'index.php', 'router', 'sitemap.xml', 'robots.txt', 'favicon.ico', 'favicon.svg', 'site.webmanifest', 'feed', 'feed.xml', 'search', 'wp-admin', 'wp-login.php'];

function routes(): array
{
    static $routes = null;
    if ($routes === null) {
        $routes = content_default('routes');
        $custom = json_read(content_override_file('routes'));
        if (is_array($custom)) {
            foreach ($custom as $k => $v) {
                if (isset($routes[$k]) && is_string($v) && $v !== '') {
                    $routes[$k] = $v;
                }
            }
        }
    }
    return $routes;
}

/** Current address of a page, e.g. page_url('about') => '/about'. */
function page_url(string $key, string $suffix = ''): string
{
    if ($key === 'home') {
        return '/' . ltrim($suffix, '/');
    }
    return '/' . (routes()[$key] ?? $key) . $suffix;
}

function redirects_file(): string
{
    return content_override_file('redirects');
}

/** ['exact' => ['/old' => '/new'], 'prefix' => ['/old/' => '/new/']] */
function redirects(): array
{
    static $cache = null;
    if ($cache === null) {
        $r = json_read(redirects_file(), []);
        $cache = ['exact' => (array) ($r['exact'] ?? []), 'prefix' => (array) ($r['prefix'] ?? [])];
    }
    return $cache;
}

/**
 * Record that $old now lives at $new. Paths ending in "/" are prefix redirects
 * (used when a section such as the blog moves). Chains are collapsed and loops removed.
 */
function record_redirect(string $old, string $new): void
{
    if ($old === $new || $old === '' || $new === '') {
        return;
    }
    $r = json_read(redirects_file(), []);
    $type = str_ends_with($old, '/') ? 'prefix' : 'exact';
    $map = (array) ($r[$type] ?? []);
    unset($map[$new]); // the new address is live again
    foreach ($map as $from => $to) {
        if ($to === $old) {
            $map[$from] = $new;
        } elseif ($type === 'prefix' && str_starts_with($to, $old)) {
            $map[$from] = $new . substr($to, strlen($old));
        }
    }
    $map[$old] = $new;
    $r[$type] = array_filter($map, static fn ($to, $from) => $to !== $from, ARRAY_FILTER_USE_BOTH);
    json_write(redirects_file(), $r);
}

/** Resolve a site path through the redirect table. Keeps any ?query or #hash. */
function link_path(string $href): string
{
    if ($href === '' || $href[0] !== '/' || str_starts_with($href, '//')) {
        return $href;
    }
    $r = redirects();
    if (!$r['exact'] && !$r['prefix']) {
        return $href;
    }
    $suffix = '';
    if (preg_match('/^([^?#]*)([?#].*)$/', $href, $m)) {
        [$href, $suffix] = [$m[1], $m[2]];
    }
    for ($i = 0; $i < 5; $i++) {
        if (isset($r['exact'][$href])) {
            $href = $r['exact'][$href];
            continue;
        }
        foreach ($r['prefix'] as $from => $to) {
            // "/blog/" also covers "/blog" itself.
            if ($href === rtrim($from, '/')) {
                $href = rtrim($to, '/') ?: '/';
                continue 2;
            }
            if (str_starts_with($href, $from)) {
                $href = $to . substr($href, strlen($from));
                continue 2;
            }
        }
        break;
    }
    return $href . $suffix;
}

/** Output filter: point every site link and form at the current addresses. */
function rewrite_links(string $html): string
{
    $r = redirects();
    if (!$r['exact'] && !$r['prefix']) {
        return $html;
    }
    return preg_replace_callback('/\b(href|action)="(\/(?!\/)[^"]*)"/', static fn ($m) => $m[1] . '="' . link_path($m[2]) . '"', $html) ?? $html;
}

/** Is this slug free to use for a page? Returns an error message or null. */
function slug_problem(string $slug, string $ownerKey = ''): ?string
{
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        return '“' . $slug . '” isn’t a valid address. Use lowercase letters, numbers and single dashes.';
    }
    if (in_array($slug, RESERVED_SLUGS, true)) {
        return '“' . $slug . '” is reserved by the system. Please choose another address.';
    }
    foreach (routes() as $key => $s) {
        if ($s === $slug && 'page:' . $key !== $ownerKey) {
            return '“' . $slug . '” is already used by another page.';
        }
    }
    foreach (array_keys(content('services')) as $s) {
        if ($s === $slug && 'service:' . $s !== $ownerKey) {
            return '“' . $slug . '” is already used by a service.';
        }
    }
    return null;
}

/** A page now lives at $path again: stop redirecting it elsewhere. */
function clear_redirect(string $path): void
{
    $r = json_read(redirects_file(), []);
    $changed = false;
    foreach (['exact' => $path, 'prefix' => rtrim($path, '/') . '/'] as $type => $key) {
        if (isset($r[$type][$key])) {
            unset($r[$type][$key]);
            $changed = true;
        }
    }
    if ($changed) {
        json_write(redirects_file(), $r);
    }
}
