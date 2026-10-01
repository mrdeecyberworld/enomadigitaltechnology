<?php
/**
 * Brand, navigation and page registry.
 * Edit text here; templates read from this file.
 */

return [
    'name'        => 'Enoma Digital Technologies',
    'short_name'  => 'Enoma Digital Tech',
    'domain'      => 'EnomaDigitalTech.com',
    'tagline'     => 'Build. Secure. Empower.',
    'statement'   => 'Technology solutions built around your needs.',
    'description' => 'Enoma Digital Technologies provides web development, cybersecurity, IT support and technology training for businesses and individuals.',
    'service_area' => 'Remote technology services for clients across the United States.',

    // Primary navigation. 'children' => 'services' renders the services menu.
    'nav' => [
        ['label' => 'Home',            'path' => '/'],
        ['label' => 'Services',        'path' => '/services', 'children' => 'services'],
        ['label' => 'Cybersecurity',   'path' => '/cybersecurity'],
        ['label' => 'Web Development', 'path' => '/web-development'],
        ['label' => 'Training',        'path' => '/training'],
        ['label' => 'About',           'path' => '/about'],
        ['label' => 'Blog',            'path' => '/blog'],
        ['label' => 'Resources',       'path' => '/resources'],
        ['label' => 'Contact',         'path' => '/contact'],
    ],

    'cta_primary'   => ['label' => 'Book a Consultation', 'path' => '/book-a-consultation'],
    'cta_secondary' => ['label' => 'Get a Quote',         'path' => '/get-a-quote'],

    'footer_company' => [
        ['label' => 'About',            'path' => '/about'],
        ['label' => 'Blog',             'path' => '/blog'],
        ['label' => 'Resources',        'path' => '/resources'],
        ['label' => 'FAQ',              'path' => '/faq'],
        ['label' => 'Contact',          'path' => '/contact'],
        ['label' => 'Privacy Policy',   'path' => '/privacy-policy'],
        ['label' => 'Terms of Service', 'path' => '/terms-of-service'],
    ],
];
