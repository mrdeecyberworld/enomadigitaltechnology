<?php
/**
 * Blog settings (editable in Admin → Blog settings).
 * Posts themselves are managed in Admin → Blog posts.
 */

return [
    'meta_title'       => 'Blog: Cybersecurity & Technology Insights | Enoma Digital Technologies',
    'meta_description' => 'Practical cybersecurity tips, website advice and technology insights for small businesses, professionals and families from Enoma Digital Technologies.',
    'eyebrow'          => 'Blog',
    'heading'          => 'Articles on security and technology',
    'intro'            => 'Plain-language articles on protecting your accounts, improving your website and choosing technology.',
    'home_heading'     => 'Latest articles',
    'home_intro'       => 'Practical tips on cybersecurity, websites and everyday technology.',
    'show_on_home'     => false,
    'posts_per_page'   => '9',
    'default_author'   => 'Enoma Digital Technologies',
    'categories'       => [
        ['name' => 'Cybersecurity',         'slug' => 'cybersecurity',      'icon' => 'shield-check'],
        ['name' => 'Web Development',       'slug' => 'web-development',    'icon' => 'code-xml'],
        ['name' => 'IT Support',            'slug' => 'it-support',         'icon' => 'headset'],
        ['name' => 'Cloud & Data',          'slug' => 'cloud',              'icon' => 'cloud'],
        ['name' => 'Training & Education',  'slug' => 'training',           'icon' => 'graduation-cap'],
        ['name' => 'Tech News',             'slug' => 'tech-news',          'icon' => 'sparkles'],
    ],
    'cta_heading'      => 'Want help putting this into practice?',
    'cta_text'         => 'Book a consultation and I will help you apply it to your business, website or family.',
];
