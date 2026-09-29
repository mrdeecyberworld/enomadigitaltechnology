<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$crumbs = [['Home', '/'], ['Terms of Service', '/terms-of-service']];
$page = [
    'title'       => 'Terms of Service | Enoma Digital Technologies',
    'description' => 'Terms governing use of the Enoma Digital Technologies website, including the AI assistant and educational resources.',
    'path'        => '/terms-of-service',
    'schema'      => [schema_breadcrumbs($crumbs)],
];
require INC . '/layout/header.php';
echo page_hero('Terms of Service', ['eyebrow' => 'Legal', 'crumbs' => $crumbs]);
?>
<section class="section">
  <div class="container container--narrow prose">
    <p class="prose__updated">Last updated: <?= placeholder('Effective date') ?></p>
    <?= setup_notice('These terms are a starting template. Have them reviewed by a qualified professional before launch.') ?>

    <p>These terms apply to your use of <?= e(site('domain')) ?>, operated by <?= e(site('name')) ?>. By using the website you agree to them.</p>

    <h2>Website content</h2>
    <p>Content on this website, including resources and guides, is provided for general informational and educational purposes. It is not legal, financial or other professional advice, and it may not fit your specific situation.</p>

    <h2>AI assistant</h2>
    <p>The AI assistant is an automated tool, not a human. It provides general information about our services and technology topics and may be incomplete or inaccurate. It does not provide legal, medical or financial advice, and it will not assist with activities that could harm systems or people. Do not rely on it for decisions without confirming with a qualified professional.</p>

    <h2>Services</h2>
    <p>Specific services are provided under a separate written agreement or proposal. Quotes and information on this website do not create a contract until both parties agree in writing.</p>

    <h2>Acceptable use</h2>
    <p>Do not misuse the website, attempt to disrupt it, submit false information or use it for any unlawful purpose.</p>

    <h2>Intellectual property</h2>
    <p>The Enoma Digital Technologies name, logo and original website content belong to <?= e(site('name')) ?>. Photography is used under the license of its respective source.</p>

    <h2>Limitation of liability</h2>
    <p>The website is provided "as is." To the extent permitted by law, <?= e(site('name')) ?> is not liable for damages arising from use of the website.</p>

    <h2>Governing law</h2>
    <p>These terms are governed by the laws of <?= placeholder('State') ?>, United States.</p>

    <h2>Contact</h2>
    <p>Questions about these terms? <a href="/contact">Contact us</a>.</p>
  </div>
</section>
<?php require INC . '/layout/footer.php'; ?>
