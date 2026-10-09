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
    'description' => 'Enoma Digital Technologies: freelance web developer and IT specialist offering web development, cybersecurity, IT support and technology training for clients worldwide.',
    'service_area' => 'Remote technology services for clients worldwide.',

    // Primary navigation (kept short; the logo links home and every service is in
    // the Services menu). 'children' => 'services' renders the services menu.
    // The Courses & Tools link only shows once a product is published.
    'nav' => [
        ['label' => 'Services',        'page' => 'services', 'path' => '/services', 'children' => 'services'],
        ['label' => 'Courses & Tools', 'page' => 'shop', 'path' => '/courses-and-tools'],
        ['label' => 'About',           'page' => 'about', 'path' => '/about'],
        ['label' => 'Blog',            'page' => 'blog', 'path' => '/blog'],
        ['label' => 'Contact',         'page' => 'contact', 'path' => '/contact'],
    ],

    'cta_primary'   => ['label' => 'Hire Me',             'path' => '/get-a-quote'],
    'cta_secondary' => ['label' => 'Get a Quote',         'path' => '/get-a-quote'],

    'footer_company' => [
        ['label' => 'About',            'page' => 'about', 'path' => '/about'],
        ['label' => 'Courses & Tools',  'page' => 'shop', 'path' => '/courses-and-tools'],
        ['label' => 'Blog',             'page' => 'blog', 'path' => '/blog'],
        ['label' => 'Resources',        'page' => 'resources', 'path' => '/resources'],
        ['label' => 'FAQ',              'page' => 'faq', 'path' => '/faq'],
        ['label' => 'Leave Feedback',   'page' => 'feedback', 'path' => '/feedback'],
        ['label' => 'Contact',          'page' => 'contact', 'path' => '/contact'],
        ['label' => 'Privacy Policy',   'page' => 'privacy', 'path' => '/privacy-policy'],
        ['label' => 'Terms of Service', 'page' => 'terms', 'path' => '/terms-of-service'],
    ],
];
