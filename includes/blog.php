<?php
/**
 * Blog: posts are stored one per file in storage/blog/{id}.json and managed
 * in Admin → Blog posts. Starter posts are copied in on first use.
 */

declare(strict_types=1);

function blog_dir(): string
{
    return storage_dir('blog');
}

/** Copy the starter posts into storage once. */
function blog_seed_if_needed(): void
{
    $marker = blog_dir() . '/.seeded';
    if (is_file($marker)) {
        blog_add_seed_covers();
        blog_seed_extra('blog-seed-2');
        return;
    }
    $now = time();
    foreach (content_default('blog-seed') as $i => $post) {
        $post['id'] = $post['slug'];
        $post['status'] = 'published';
        // Newest first in the order listed.
        $post['published_at'] = date('Y-m-d H:i', $now - $i * 3600);
        $post['updated_at'] = date('c', $now);
        $post['author'] = '';
        blog_write($post);
    }
    @file_put_contents($marker, date('c'));
    @file_put_contents(blog_dir() . '/.covers', date('c'));
    blog_seed_extra('blog-seed-2');
}

/**
 * Add a later set of starter posts once (also on sites that already have posts).
 * A post that already exists is left alone, and the set is never added twice,
 * so a post you delete does not come back.
 */
function blog_seed_extra(string $set): void
{
    $marker = blog_dir() . '/.seeded-' . preg_replace('/[^a-z0-9-]/', '', $set);
    if (is_file($marker)) {
        return;
    }
    $now = time();
    foreach (content_default($set) as $i => $post) {
        if (is_file(blog_dir() . '/' . $post['slug'] . '.json')) {
            continue;
        }
        $post['id'] = $post['slug'];
        $post['status'] = 'published';
        // Spread over the past weeks, newest first in the order listed.
        $post['published_at'] = date('Y-m-d H:i', $now - $i * 4 * 86400);
        $post['updated_at'] = date('c', $now);
        $post['author'] = '';
        blog_write($post);
    }
    @file_put_contents($marker, date('c'));
}

/** Give starter posts saved before cover photos existed their photo (once; posts you've changed keep your choice). */
function blog_add_seed_covers(): void
{
    $marker = blog_dir() . '/.covers';
    if (is_file($marker)) {
        return;
    }
    foreach (content_default('blog-seed') as $seed) {
        $file = blog_dir() . '/' . $seed['slug'] . '.json';
        $post = json_read($file);
        if (is_array($post) && ($post['cover'] ?? '') === '' && !empty($seed['cover'])) {
            $post['cover'] = $seed['cover'];
            blog_write($post);
        }
    }
    @file_put_contents($marker, date('c'));
}

function blog_write(array $post): bool
{
    $id = preg_replace('/[^a-z0-9-]/', '', (string) ($post['id'] ?? ''));
    if ($id === '') {
        return false;
    }
    return json_write(blog_dir() . '/' . $id . '.json', $post);
}

function blog_delete(string $id): void
{
    $id = preg_replace('/[^a-z0-9-]/', '', $id);
    if ($id !== '') {
        store_delete(blog_dir() . '/' . $id . '.json');
    }
}

function blog_normalize(array $p): array
{
    $settings = content('blog');
    return array_merge([
        'id' => '', 'slug' => '', 'title' => 'Untitled post', 'excerpt' => '', 'body' => '',
        'category' => '', 'tags' => [], 'cover' => '', 'cover_alt' => '', 'author' => '',
        'status' => 'draft', 'featured' => false, 'published_at' => '', 'updated_at' => '',
        'meta_title' => '', 'meta_description' => '', 'sources' => [],
    ], $p, [
        'author_display' => ((string) ($p['author'] ?? '')) !== '' ? $p['author'] : ((string) ($settings['default_author'] ?? '') ?: site('name')),
    ]);
}

/**
 * All posts, newest first. Drafts and posts scheduled for the future are
 * hidden from the public unless $includeDrafts is true.
 */
function blog_posts(bool $includeDrafts = false): array
{
    static $cache = [];
    $key = $includeDrafts ? 'all' : 'public';
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    blog_seed_if_needed();
    $posts = [];
    foreach (store_list('blog') as $file) {
        $p = json_read($file);
        if (!is_array($p)) {
            continue;
        }
        $p = blog_normalize($p);
        if (!$includeDrafts && !blog_is_live($p)) {
            continue;
        }
        $posts[] = $p;
    }
    usort($posts, static fn ($a, $b) => strcmp((string) $b['published_at'], (string) $a['published_at']));
    return $cache[$key] = $posts;
}

function blog_is_live(array $p): bool
{
    return ($p['status'] ?? '') === 'published'
        && ($p['published_at'] ?? '') !== ''
        && strtotime((string) $p['published_at']) <= time();
}

function blog_find(string $slug, bool $includeDrafts = false): ?array
{
    foreach (blog_posts($includeDrafts) as $p) {
        if ($p['slug'] === $slug) {
            return $p;
        }
    }
    return null;
}

function blog_categories(): array
{
    $cats = [];
    foreach ((array) (content('blog')['categories'] ?? []) as $c) {
        if (!empty($c['slug'])) {
            $cats[$c['slug']] = $c + ['icon' => 'file-text', 'name' => $c['slug']];
        }
    }
    return $cats;
}

function blog_category(string $slug): array
{
    return blog_categories()[$slug] ?? ['name' => 'Articles', 'slug' => '', 'icon' => 'file-text'];
}

function blog_url(array $p): string
{
    return page_url('blog', '/' . $p['slug']);
}

function blog_reading_minutes(array $p): int
{
    return max(1, (int) round(str_word_count(strip_tags((string) $p['body'])) / 220));
}

function blog_date(array $p, string $format = 'F j, Y'): string
{
    $t = strtotime((string) $p['published_at']);
    return $t ? date($format, $t) : '';
}

/**
 * Cover image for a post: the uploaded cover, or designed cover art in the
 * category's colors when no image is set (so every post looks finished).
 */
function blog_cover(array $p, string $sizes = '(min-width: 1024px) 33vw, 100vw', bool $eager = false): string
{
    $cat = blog_category((string) $p['category']);
    $variant = 'cover-art--' . (abs(crc32((string) ($p['slug'] ?: $cat['slug']))) % 4);
    $art = '<div class="cover-art ' . $variant . '" aria-hidden="true"><span class="cover-art__grid"></span>'
        . '<span class="cover-art__icon">' . icon($cat['icon'], 'icon') . '</span>'
        . '<span class="cover-art__label">' . e($cat['name']) . '</span></div>';
    $cover = (string) $p['cover'];
    if ($cover !== '') {
        $opts = ['eager' => $eager];
        if ((string) $p['cover_alt'] !== '') {
            $opts['alt'] = (string) $p['cover_alt'];
        }
        $img = photo($cover, $sizes, $opts);
        if ($img !== '') {
            return $art . $img;
        }
    }
    return $art;
}

/** Post card used on the blog index, homepage and related posts. */
function blog_card(array $p, string $headingTag = 'h3', bool $large = false): string
{
    $cat = blog_category((string) $p['category']);
    ob_start(); ?>
    <article class="post-card<?= $large ? ' post-card--large' : '' ?> reveal">
      <div class="post-card__media"><?= blog_cover($p, $large ? '(min-width: 1024px) 60vw, 100vw' : '(min-width: 1024px) 33vw, 100vw') ?></div>
      <div class="post-card__body">
        <p class="post-card__meta"><span class="post-card__cat"><?= e($cat['name']) ?></span><span aria-hidden="true">·</span><time datetime="<?= e(blog_date($p, 'Y-m-d')) ?>"><?= e(blog_date($p, 'M j, Y')) ?></time><span aria-hidden="true">·</span><span><?= blog_reading_minutes($p) ?> min read</span></p>
        <<?= $headingTag ?> class="post-card__title"><a href="<?= e(blog_url($p)) ?>"><?= e($p['title']) ?></a></<?= $headingTag ?>>
        <p class="post-card__excerpt"><?= e($p['excerpt']) ?></p>
        <span class="post-card__more" aria-hidden="true">Read article <?= icon('arrow-right', 'icon icon-xs') ?></span>
      </div>
    </article>
    <?php
    return (string) ob_get_clean();
}

/** A unique, URL-safe slug. */
function blog_unique_slug(string $wanted, string $exceptId = ''): string
{
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($wanted)), '-') ?: 'post';
    $base = substr($base, 0, 80);
    $slug = $base;
    $taken = [];
    foreach (blog_posts(true) as $p) {
        if ($p['id'] !== $exceptId) {
            $taken[$p['slug']] = true;
        }
    }
    for ($n = 2; isset($taken[$slug]); $n++) {
        $slug = $base . '-' . $n;
    }
    return $slug;
}
