<?php
/**
 * Site configuration.
 *
 * Safe defaults live here. Anything secret (API keys, email addresses you do
 * not want in git) should be set with environment variables or in a private
 * override file that lives OUTSIDE the public web root, e.g.
 *
 *   /home/youraccount/enoma-config.php      <- private (not web accessible)
 *   /home/youraccount/public_html/          <- this website
 *
 * The override file must `return [ ... ];` with any keys below.
 * See config.sample.php in the project root for an example.
 */

declare(strict_types=1);

$config = [
    // Public URL of the site, no trailing slash. Used for canonical URLs,
    // Open Graph tags, the XML sitemap and structured data.
    'base_url' => 'https://enomadigitaltech.com',

    // Set to true while developing locally to show PHP errors.
    'debug' => false,

    // ── Contact details ─────────────────────────────────────────────────
    // Leave empty until real details exist. Empty values are hidden on the
    // site and omitted from structured data (nothing is invented). When set
    // (Admin → Settings), they show on the Contact, Quote and Consultation
    // pages and in the footer, and form messages are emailed to this address.
    'contact_email' => '',
    'contact_phone' => '',

    // Online scheduling link (Calendly, Microsoft Bookings, Google Calendar
    // appointment page, etc.). When set, "Book a Consultation" pages show a
    // direct scheduling button in addition to the request form.
    'booking_url' => '',

    // Social profiles. Empty ones are hidden from visitors (only you see a reminder while logged in).
    'social' => [
        'LinkedIn'  => '',
        'Facebook'  => '',
        'Instagram' => '',
        'X'         => '',
        'YouTube'   => '',
    ],

    // Founder / about details. Empty values render as clearly marked
    // placeholders so they can be filled in later.
    'founder' => [
        'name'  => 'Enoma Divine Omozusi',
        'title' => 'Founder',
        'bio'   => 'IT Support Engineer with an MSc in Cybersecurity and enterprise experience in Windows and macOS support, identity and access management and device management. I founded Enoma Digital Technologies to give small businesses and individuals the same secure, well-run technology that large organizations rely on.',
        // Path relative to the site root, e.g. 'assets/img/founder.jpg'.
        'photo' => '',
    ],

    // ── Form delivery (INTEGRATION POINT) ───────────────────────────────
    // 'none' : forms validate but nothing is sent. Visitors are told plainly
    //          that online delivery is not connected yet.
    // 'mail' : send with PHP mail() to forms.to (most shared hosts support
    //          this). For better deliverability, swap send_form_email() in
    //          includes/forms.php for SMTP or a service such as Postmark,
    //          SendGrid or Resend.
    'forms' => [
        'delivery' => 'none',
        'to'       => '',
        'from'     => 'no-reply@enomadigitaltech.com',
    ],

    // Website color theme: 'dark' (deep navy, default) or 'light'.
    'site_theme' => 'dark',

    // Email for form submissions (Admin → Email). Method: none | php | smtp | resend
    'mail' => [
        'method'     => 'none',
        'to'         => '',
        'from_email' => '',
        'from_name'  => '',
        'smtp'       => ['host' => '', 'port' => '587', 'encryption' => 'tls', 'username' => '', 'password' => ''],
        'resend_api_key' => '',
        'auto_reply' => false,
        'auto_reply_subject' => 'We received your message',
        'auto_reply_body' => "Hi {name},\n\nThank you for contacting {company}. We have received your message and will reply by email soon.\n\nBest regards,\n{company}",
    ],

    // Show small notices to site owners when an integration is not configured.
    // Turn off once forms and the AI assistant are connected.
    'setup_notices' => true,

    // ── AI assistant (INTEGRATION POINT) ────────────────────────────────
    // Without an API key the assistant runs in "guided mode": a local,
    // rules-based helper that only uses approved website content.
    // With a key it uses the Claude API from the server (never the browser).
    // Google AdSense (Admin → Settings → Google AdSense)
    'adsense' => [
        'client'    => 'ca-pub-4418201778948074', // your publisher ID (it is public: it appears in every ad)
        'placement' => 'all',                     // all | content (blog and guides only) | off
    ],

    // Search engines (Admin → Settings → Search engines)
    'seo' => [
        'google_verification' => '',   // Google Search Console "HTML tag" code
        'bing_verification'   => '',   // Bing Webmaster Tools code
        'force_https'         => false, // send every visitor to https:// (turn on once SSL works)
    ],

    'ai' => [
        'api_key'        => '',              // prefer the ANTHROPIC_API_KEY env var
        'model'          => 'claude-opus-5-5',
        'effort'         => 'low',           // short, conversational answers
        'max_tokens'     => 2048,
        'rate_limit'     => 20,              // messages per visitor per hour
    ],
];

// Environment variable overrides (set these in cPanel, .htaccess SetEnv, or your host's panel).
$env = static fn (string $k): string => (string) (getenv($k) ?: ($_SERVER[$k] ?? ''));
if ($env('ANTHROPIC_API_KEY') !== '') {
    $config['ai']['api_key'] = $env('ANTHROPIC_API_KEY');
}
if ($env('ENOMA_AI_MODEL') !== '') {
    $config['ai']['model'] = $env('ENOMA_AI_MODEL');
}
if ($env('ENOMA_BASE_URL') !== '') {
    $config['base_url'] = rtrim($env('ENOMA_BASE_URL'), '/');
}
if ($env('ENOMA_DEBUG') === '1') {
    $config['debug'] = true;
}

// Private override file (outside the web root by default).
$overrideFile = $env('ENOMA_CONFIG_FILE') ?: dirname(__DIR__, 2) . '/enoma-config.php';
if (is_file($overrideFile) && is_readable($overrideFile)) {
    $override = require $overrideFile;
    if (is_array($override)) {
        $config = array_replace_recursive($config, $override);
    }
}

// Settings saved from the admin panel (Settings page) take priority.
$adminSettings = json_read(storage_dir('content') . '/settings.json');
if (is_array($adminSettings)) {
    // Blank secrets in the admin panel never wipe values set elsewhere.
    if (($adminSettings['ai']['api_key'] ?? '') === '') {
        unset($adminSettings['ai']['api_key']);
    }
    $config = array_replace_recursive($config, $adminSettings);
}
if ($config['ai']['api_key'] === '' && $env('ANTHROPIC_API_KEY') !== '') {
    $config['ai']['api_key'] = $env('ANTHROPIC_API_KEY');
}

return $config;
