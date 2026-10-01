<?php
/**
 * Sample private configuration (optional: everything here can also be set in the admin panel).
 *
 * Copy this file to ONE LEVEL ABOVE your public web root and rename it to
 * enoma-config.php, e.g. /home/youraccount/enoma-config.php, then fill in
 * real values. Only include the keys you want to change.
 *
 * Alternatively set the ENOMA_CONFIG_FILE environment variable to its path.
 */

return [
    'contact_email' => 'hello@enomadigitaltech.com',

    'booking_url' => '', // e.g. https://calendly.com/your-link

    'mail' => [
        'method'     => 'smtp',              // none | smtp | resend | php
        'to'         => 'hello@enomadigitaltech.com',
        'from_email' => 'no-reply@enomadigitaltech.com',
        'from_name'  => 'Enoma Digital Technologies',
        'smtp'       => ['host' => 'smtp.gmail.com', 'port' => '587', 'encryption' => 'tls', 'username' => '', 'password' => ''],
        'resend_api_key' => '',
    ],

    'ai' => [
        'api_key' => 'sk-ant-...', // or set ANTHROPIC_API_KEY instead
    ],

    'founder' => [
        'name'  => 'Your Name',
        'title' => 'Founder',
        'bio'   => 'A short, factual introduction.',
        'photo' => 'assets/img/founder.jpg',
    ],

    'social' => [
        'LinkedIn' => 'https://www.linkedin.com/company/your-page',
    ],

    'setup_notices' => false,
];
