<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

$catOptions = [];
foreach (blog_categories() as $slug => $c) {
    $catOptions[$slug] = $c['name'];
}
if (!$catOptions) {
    $catOptions = ['' => 'Uncategorized'];
}
$f = static fn (string $key, string $label, string $type = 'text', array $extra = []): array => ['key' => $key, 'label' => $label, 'type' => $type] + $extra;
$schema = ['type' => 'group', 'fields' => [
    $f('title', 'Title', 'text', ['required' => true]),
    $f('excerpt', 'Summary', 'textarea', ['rows' => 3, 'counter' => 200, 'hint' => 'One or two sentences shown on blog cards, under the title and in search results.']),
    $f('body', 'Article', 'richtext', ['rows' => 26]),
    $f('cover', 'Cover image', 'image', ['hint' => 'Optional. Without one, a designed cover in the category’s colors is shown. Best size: 1600 × 900 px.']),
    $f('cover_alt', 'Cover image description (alt text)'),
    $f('category', 'Category', 'select', ['options' => $catOptions]),
    $f('tags', 'Tags', 'lines', ['hint' => 'One per line.']),
    $f('status', 'Status', 'select', ['options' => ['draft' => 'Draft (not visible)', 'published' => 'Published']]),
    $f('published_at', 'Publish date', 'datetime', ['hint' => 'Set a future date to schedule the post. Leave empty to use the moment you publish.']),
    $f('featured', 'Feature this post at the top of the blog', 'bool'),
    $f('author', 'Author name', 'text', ['hint' => 'Leave empty to use “' . ((string) (content('blog')['default_author'] ?? '') ?: site('name')) . '”.']),
    $f('slug', 'URL slug', 'text', ['hint' => 'The address of the post: /blog/your-slug. Leave empty to create it from the title.']),
    $f('sources', 'Sources', 'lines', ['hint' => 'Links to articles you referenced, one per line. Shown at the end of the post.']),
    $f('meta_title', 'SEO title (optional)', 'text', ['counter' => 60]),
    $f('meta_description', 'SEO description (optional)', 'textarea', ['rows' => 2, 'counter' => 160]),
]];

$id = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['id'] ?? ''));
$post = null;
if ($id !== '') {
    foreach (blog_posts(true) as $p) {
        if ($p['id'] === $id) { $post = $p; break; }
    }
    if (!$post) {
        flash('That post no longer exists.', 'error');
        redirect('/admin/blog');
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_csrf_check();
    if (($_POST['action'] ?? '') === 'delete' && $post) {
        blog_delete($post['id']);
        flash('Post deleted.');
        redirect('/admin/blog');
    }
    $data = field_parse($schema, $_POST['data'] ?? [], $post ?? []);
    unset($data['author_display']);
    if (trim((string) $data['title']) === '') {
        flash('Please add a title.', 'error');
        redirect($post ? '/admin/blog-edit?id=' . $post['id'] : '/admin/blog-edit?new=1');
    }
    $data['sources'] = array_values(array_filter((array) $data['sources'], static fn ($s) => preg_match('#^https?://#i', (string) $s)));
    $data['id'] = $post['id'] ?? substr(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $data['title'])), '-'), 0, 50) . '-' . bin2hex(random_bytes(3));
    $data['slug'] = blog_unique_slug((string) ($data['slug'] ?: $data['title']), $data['id']);
    if (($_POST['publish_now'] ?? '') === '1') {
        $data['status'] = 'published';
    }
    if ($data['status'] === 'published' && $data['published_at'] === '') {
        $data['published_at'] = date('Y-m-d H:i');
    }
    $data['updated_at'] = date('c');
    if (blog_write($data)) {
        $msg = $data['status'] === 'published'
            ? (strtotime($data['published_at']) > time() ? 'Post scheduled for ' . date('M j, Y g:i a', strtotime($data['published_at'])) . '.' : 'Post published. It’s live on your blog.')
            : 'Draft saved.';
        flash($msg);
    } else {
        flash('Could not save the post. Check that the storage folder is writable.', 'error');
    }
    redirect('/admin/blog-edit?id=' . $data['id']);
}

// New post, optionally started from a news headline.
if (!$post) {
    $topic = mb_substr(trim((string) ($_GET['topic'] ?? '')), 0, 300);
    $source = (string) ($_GET['source'] ?? '');
    $post = [
        'title'    => $topic,
        'status'   => 'draft',
        'category' => isset($catOptions[(string) ($_GET['cat'] ?? '')]) ? (string) $_GET['cat'] : (string) array_key_first($catOptions),
        'sources'  => preg_match('#^https?://#i', $source) ? [$source] : [],
        'body'     => $topic !== '' ? "## What happened\n\nSummarize the news in your own words. Link to the original source rather than copying it.\n\n## Why it matters\n\nExplain what this means for small businesses, professionals or families.\n\n## What you should do\n\n- First practical step\n- Second practical step\n\n## Need help?\n\nWe can help you put this into practice. [Book a consultation](/book-a-consultation)." : '',
    ];
}
$isNew = empty($post['id']);
$live = !$isNew && blog_is_live($post);

admin_header($isNew ? 'New post' : 'Edit post', 'blog');
?>
<p><a class="link" href="/admin/blog">← All posts</a></p>
<header class="page-head">
  <div>
    <h1><?= $isNew ? 'New post' : 'Edit post' ?></h1>
    <?php if (!$isNew): ?><p class="muted"><?= $live ? 'Live at' : 'Will appear at' ?> <a href="<?= e(blog_url($post)) ?>" target="_blank" rel="noopener"><?= e(blog_url($post)) ?></a></p><?php endif; ?>
  </div>
  <?php if (!$isNew): ?><a class="btn btn--light" href="<?= e(blog_url($post)) ?>" target="_blank" rel="noopener"><?= icon('arrow-up-right', 'icon icon-sm') ?> <?= $live ? 'View post' : 'Preview' ?></a><?php endif; ?>
</header>

<?php if (!empty($_GET['topic'])): ?>
  <div class="notice notice--info">Started from a news headline. Write the article in your own words, add your perspective, and keep the source link so readers can check the original.</div>
<?php endif; ?>

<form method="post" class="editor post-editor" data-editor>
  <?= csrf_field() ?>
  <?= field_render($schema, 'data', $post) ?>
  <div class="savebar">
    <span class="muted small" data-dirty-note><?= $isNew ? 'Not saved yet.' : 'All changes saved.' ?></span>
    <button type="submit" class="btn btn--light">Save</button>
    <?php if (($post['status'] ?? 'draft') !== 'published'): ?>
      <button type="submit" name="publish_now" value="1" class="btn btn--primary"><?= icon('send', 'icon icon-sm') ?> Publish</button>
    <?php else: ?>
      <button type="submit" class="btn btn--primary"><?= icon('check', 'icon icon-sm') ?> Update post</button>
    <?php endif; ?>
  </div>
</form>

<?php if (!$isNew): ?>
<details class="panel danger">
  <summary>Delete this post</summary>
  <form method="post" class="stack" data-confirm="Delete this post permanently?">
    <?= csrf_field() ?>
    <p class="muted">This permanently removes the post from your blog. Tip: set it to Draft instead if you might want it back.</p>
    <button type="submit" name="action" value="delete" class="btn btn--danger">Delete post</button>
  </form>
</details>
<?php endif; ?>
<?php admin_footer();
