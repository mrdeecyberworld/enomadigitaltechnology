<?php
declare(strict_types=1);

$formPage = [
    'slug'             => 'book-a-consultation',
    'type'             => 'consultation',
    'title'            => 'Book a Consultation',
    'meta_title'       => 'Book a Technology Consultation | Enoma Digital Technologies',
    'meta_description' => 'Book a consultation with Enoma Digital Technologies to discuss your website, cybersecurity, IT support, training or technology plans.',
    'eyebrow'          => 'Book a Consultation',
    'intro'            => 'A consultation is the easiest way to get clarity. We will listen, ask the right questions and recommend practical next steps.',
    'side_heading'     => 'What to expect',
    'form_heading'     => 'Request a consultation',
    'side_points'      => [
        ['message-square-text', 'A conversation about your goals and challenges, not a sales pitch.'],
        ['lightbulb', 'Clear recommendations, whether or not you choose to work with us.'],
        ['calendar-check', 'Include your preferred days and times in your message and we will confirm by email.'],
    ],
];
require __DIR__ . '/includes/templates/form-page.php';
