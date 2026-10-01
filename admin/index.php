<?php
declare(strict_types=1);
require __DIR__ . '/includes/admin.php';
require_admin();

$messages = [];
foreach (array_reverse(glob(storage_dir('submissions') . '/*.json') ?: []) as $file) {
    $messages[] = json_read($file, []);
    if (count($messages) >= 5) {
        break;
    }
}
$total = count(glob(storage_dir('submissions') . '/*.json') ?: []);
$founder = cfg('founder', []);
$legal = content('legal');
$checklist = [
    ['Add your public contact email', (bool) cfg('contact_email'), '/admin/edit?section=settings'],
    ['Set up email so form submissions reach your inbox', mail_enabled(), '/admin/email'],
    ['Add the founder name, bio and photo', !empty($founder['name']) && !empty($founder['bio']), '/admin/edit?section=settings'],
    ['Add an online scheduling link (optional)', (bool) cfg('booking_url'), '/admin/edit?section=settings'],
    ['Connect the AI assistant with an API key (optional)', (bool) cfg('ai.api_key'), '/admin/edit?section=settings'],
    ['Add your social media links', (bool) array_filter(cfg('social', [])), '/admin/edit?section=settings'],
    ['Review the legal pages and add effective dates', !empty($legal['privacy']['updated']) && !empty($legal['terms']['updated']), '/admin/edit?section=legal'],
    ['Add real client testimonials', (bool) content('testimonials'), '/admin/edit?section=testimonials'],
];
$done = count(array_filter(array_column($checklist, 1)));

admin_header('Dashboard', 'dashboard');
?>
<header class="page-head">
  <div>
    <h1>Welcome back</h1>
    <p class="muted">Manage everything on <?= e(site('domain')) ?> from here.</p>
  </div>
  <a class="btn btn--primary" href="/" target="_blank" rel="noopener"><?= icon('arrow-up-right', 'icon icon-sm') ?> View website</a>
</header>

<div class="stats">
  <a class="stat" href="/admin/messages"><span class="stat__num"><?= unread_count() ?></span><span class="stat__label">Unread messages</span></a>
  <a class="stat" href="/admin/messages"><span class="stat__num"><?= $total ?></span><span class="stat__label">Total messages</span></a>
  <a class="stat" href="/admin/edit?section=services"><span class="stat__num"><?= count(services()) ?></span><span class="stat__label">Services</span></a>
  <a class="stat" href="/admin/blog"><span class="stat__num"><?= count(blog_posts()) ?></span><span class="stat__label">Published posts</span></a>
</div>

<div class="quick">
  <a class="quick__card" href="/admin/blog-edit?new=1"><span class="tile__icon"><?= icon('plus', 'icon icon-sm') ?></span><span><strong>Write a blog post</strong><span class="muted small">Share tips and news with your visitors.</span></span></a>
  <a class="quick__card" href="/admin/news"><span class="tile__icon"><?= icon('sparkles', 'icon icon-sm') ?></span><span><strong>Latest tech &amp; security news</strong><span class="muted small">Headlines from CISA, Krebs, BleepingComputer and more.</span></span></a>
  <a class="quick__card" href="/admin/media"><span class="tile__icon"><?= icon('hard-drive', 'icon icon-sm') ?></span><span><strong>Upload photos</strong><span class="muted small">Add your own images to the site.</span></span></a>
</div>

<div class="cols">
  <section class="panel">
    <h2>Launch checklist <span class="pill"><?= $done ?>/<?= count($checklist) ?></span></h2>
    <ul class="checklist">
      <?php foreach ($checklist as [$label, $ok, $href]): ?>
        <li class="<?= $ok ? 'is-done' : '' ?>"><?= icon($ok ? 'circle-check-big' : 'circle-alert', 'icon icon-sm') ?><a href="<?= e($href) ?>"><?= e($label) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section class="panel">
    <h2>Latest messages</h2>
    <?php if (!$messages): ?>
      <p class="muted">No messages yet. Contact, quote, consultation and service inquiry forms all arrive here.</p>
    <?php else: ?>
      <ul class="msg-list">
        <?php foreach ($messages as $m): ?>
          <li class="<?= empty($m['read']) ? 'is-unread' : '' ?>">
            <a href="/admin/messages?id=<?= e($m['id']) ?>">
              <strong><?= e($m['name']) ?></strong>
              <span class="muted"><?= e($m['type_label']) ?> · <?= e($m['service']) ?></span>
              <time datetime="<?= e($m['created_at']) ?>"><?= e(date('M j, g:i a', strtotime($m['created_at']))) ?></time>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <a class="link" href="/admin/messages">All messages →</a>
    <?php endif; ?>
  </section>
</div>

<section class="panel">
  <h2>Edit your content</h2>
  <div class="tiles">
    <?php foreach (admin_sections() as $key => $section): ?>
      <a class="tile" href="/admin/edit?section=<?= e($key) ?>">
        <span class="tile__icon"><?= icon($section['icon'], 'icon icon-sm') ?></span>
        <strong><?= e($section['title']) ?></strong>
        <span class="muted small"><?= e($section['intro']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php admin_footer();
