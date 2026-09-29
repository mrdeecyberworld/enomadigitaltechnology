<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$crumbs = [['Home', '/'], ['Privacy Policy', '/privacy-policy']];
$page = [
    'title'       => 'Privacy Policy | Enoma Digital Technologies',
    'description' => 'How Enoma Digital Technologies collects, uses and protects information submitted through EnomaDigitalTech.com.',
    'path'        => '/privacy-policy',
    'schema'      => [schema_breadcrumbs($crumbs)],
];
require INC . '/layout/header.php';
echo page_hero('Privacy Policy', ['eyebrow' => 'Legal', 'crumbs' => $crumbs]);
$contact = cfg('contact_email') ? '<a href="mailto:' . e(cfg('contact_email')) . '">' . e(cfg('contact_email')) . '</a>' : 'our <a href="/contact">contact page</a>';
?>
<section class="section">
  <div class="container container--narrow prose">
    <p class="prose__updated">Last updated: <?= placeholder('Effective date') ?></p>
    <?= setup_notice('This policy is a starting template. Have it reviewed by a qualified professional before launch and update it whenever your data practices change.') ?>

    <p><?= e(site('name')) ?> ("we", "us") respects your privacy. This policy explains what information we collect through <?= e(site('domain')) ?>, how we use it and the choices you have.</p>

    <h2>Information we collect</h2>
    <ul>
      <li><strong>Information you provide.</strong> When you submit a contact, quote, consultation or inquiry form we collect the details you enter, such as your name, email address, optional phone number and company, the service you are interested in, an optional budget range and your message.</li>
      <li><strong>AI assistant messages.</strong> Messages you type into the AI assistant are processed to generate a response. When an AI provider is connected, messages are sent to that provider for processing. Please do not enter passwords or sensitive personal information.</li>
      <li><strong>Technical information.</strong> Like most websites, our hosting provider may automatically log basic technical data such as IP address, browser type and pages requested, for security and reliability.</li>
      <li><strong>Cookies.</strong> We use a strictly necessary session cookie to protect forms against abuse. We do not use advertising cookies.</li>
    </ul>

    <h2>How we use information</h2>
    <ul>
      <li>To respond to your requests and provide the services you ask for.</li>
      <li>To prepare quotes and schedule consultations.</li>
      <li>To keep the website secure and prevent spam or abuse.</li>
      <li>To improve our website and services.</li>
    </ul>
    <p>We do not sell your personal information.</p>

    <h2>Sharing</h2>
    <p>We share information only with service providers that help us operate the website and our business (for example hosting, email and, where enabled, AI processing), when required by law, or with your consent.</p>

    <h2>Retention and security</h2>
    <p>We keep information only as long as needed for the purposes above and use reasonable safeguards to protect it. No method of transmission or storage is completely secure.</p>

    <h2>Your choices</h2>
    <p>You may ask us to access, correct or delete personal information you have provided. Depending on where you live, you may have additional rights under state privacy laws. Contact us through <?= $contact ?>.</p>

    <h2>Children</h2>
    <p>This website is not directed to children under 13, and we do not knowingly collect their personal information online. Training for young people is arranged with parents, schools or organizations.</p>

    <h2>Changes</h2>
    <p>We may update this policy from time to time. The date above shows when it was last revised.</p>
  </div>
</section>
<?php require INC . '/layout/footer.php'; ?>
