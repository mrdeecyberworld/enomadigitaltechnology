<?php
declare(strict_types=1);

$formPage = [
    'slug'             => 'get-a-quote',
    'type'             => 'quote',
    'title'            => 'Get a Quote',
    'meta_title'       => 'Request a Quote for Web Development, IT or Cybersecurity | Enoma Digital Technologies',
    'meta_description' => 'Request a quote from Enoma Digital Technologies for a website, cybersecurity services, IT support, training, cloud setup or technology consulting.',
    'eyebrow'          => 'Get a Quote',
    'intro'            => 'Tell us about your project and we will prepare a quote based on your goals, scope and timeline.',
    'side_heading'     => 'What happens next',
    'form_heading'     => 'Tell us about your project',
    'side_points'      => [
        ['clipboard-list', 'Share your goals, what you have today and any deadlines.'],
        ['search', 'We review your request and may ask a few clarifying questions.'],
        ['file-text', 'You receive a clear, itemized quote with no obligation.'],
    ],
];
require __DIR__ . '/includes/templates/form-page.php';
