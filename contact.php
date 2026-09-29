<?php
declare(strict_types=1);

$formPage = [
    'slug'             => 'contact',
    'type'             => 'contact',
    'title'            => 'Contact Enoma Digital Technologies',
    'meta_title'       => 'Contact Us | Enoma Digital Technologies',
    'meta_description' => 'Contact Enoma Digital Technologies about web development, cybersecurity services, IT support, technology training, cloud services or technology consulting.',
    'eyebrow'          => 'Contact',
    'intro'            => 'Questions about a project, a technology problem or our services? Send us a message and we will get back to you.',
    'side_heading'     => 'How we can help',
    'form_heading'     => 'Send us a message',
    'side_points'      => [
        ['message-square-text', 'Tell us what you need in your own words. No technical jargon required.'],
        ['clock', 'We review every message and reply by email.'],
        ['globe', 'Remote services for clients across the United States.'],
    ],
];
require __DIR__ . '/includes/templates/form-page.php';
