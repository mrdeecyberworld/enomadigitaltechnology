<?php
/**
 * Sample private configuration.
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

    'forms' => [
        'delivery' => 'mail',
        'to'       => 'hello@enomadigitaltech.com',
        'from'     => 'no-reply@enomadigitaltech.com',
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
