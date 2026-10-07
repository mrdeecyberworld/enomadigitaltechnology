<?php
/**
 * Homepage copy and shared marketing blocks (why Enoma, process, cybersecurity, training).
 */

return [
    // Which homepage sections are shown (Admin → Homepage → Sections to show).
    // Kept short on purpose: details live on their own pages.
    'sections' => [
        'trust'     => false,
        'why'       => true,
        'security'  => false,
        'assistant' => true,
        'training'  => false,
        'process'   => true,
        'about'     => false,
        'faq'       => true,
    ],
    'faq_count' => '4',

    'hero' => [
        'eyebrow'  => 'Independent IT and web services · Remote, worldwide',
        'headline' => 'Websites, IT support and cybersecurity. Done properly.',
        'text'     => 'Enoma Digital Technologies is a small, founder-led business. We set up technology that works, keep it secure, and explain everything in plain language, for small businesses, professionals and families.',
        'points'   => ['MSc in Cybersecurity', 'AWS Certified Solutions Architect', 'Enterprise IT support experience'],
    ],

    'trust' => [
        'heading' => 'Technology. Security. Support.',
        'items'   => [
            ['label' => 'Web Development',     'icon' => 'code-xml',       'path' => '/web-development'],
            ['label' => 'Cybersecurity',       'icon' => 'shield-check',   'path' => '/cybersecurity'],
            ['label' => 'IT Support',          'icon' => 'headset',        'path' => '/it-support'],
            ['label' => 'Technology Training', 'icon' => 'graduation-cap', 'path' => '/training'],
        ],
    ],

    'why' => [
        'eyebrow' => 'How we work',
        'heading' => 'Straight answers, no jargon.',
        'text'    => "Most people don't need more technology. They need the right setup, explained clearly, by someone who answers their messages. That is how we like to work.",
        'points'  => [
            ['icon' => 'heart-handshake', 'title' => 'You deal with the person doing the work', 'text' => 'No call centre and no ticket queue. You talk directly to the engineer looking after your setup.'],
            ['icon' => 'shield-check',    'title' => 'Security from the start',                 'text' => 'Accounts, backups and updates are set up properly from day one, not patched on later.'],
            ['icon' => 'target',          'title' => 'Only what you need',                      'text' => "We recommend what fits your goals and budget, and we tell you when you don't need something."],
            ['icon' => 'lightbulb',       'title' => 'You understand your own setup',           'text' => 'We explain what we are doing as we go, so you are never left guessing.'],
        ],
    ],

    'security' => [
        'eyebrow' => 'Cybersecurity',
        'heading' => 'Your Digital Security Matters',
        'text'    => "Cybersecurity isn't only for large corporations. Every business, professional and family connected to the internet has something worth protecting.",
        'items'   => [
            ['icon' => 'user',          'label' => 'Account security'],
            ['icon' => 'key-round',     'label' => 'Password security'],
            ['icon' => 'smartphone',    'label' => 'Multi-factor authentication'],
            ['icon' => 'fish',          'label' => 'Phishing awareness'],
            ['icon' => 'laptop',        'label' => 'Device security'],
            ['icon' => 'database',      'label' => 'Data protection'],
            ['icon' => 'graduation-cap','label' => 'Security awareness training'],
        ],
        'cta' => ['label' => 'Improve Your Security', 'path' => '/cybersecurity'],
    ],

    'process' => [
        'eyebrow' => 'Working together',
        'heading' => 'How a project works',
        'text'    => 'Four clear steps, and you always know what happens next.',
        'steps'   => [
            ['icon' => 'message-square-text', 'title' => 'Tell us what you need',      'text' => 'Book a short call or send a message about your goals or the problem.'],
            ['icon' => 'search',              'title' => 'We look at your situation',  'text' => 'We ask questions and check your current setup before recommending anything.'],
            ['icon' => 'wrench',              'title' => 'We agree a plan, then do it', 'text' => 'You get a clear plan and price, then we build, fix or set things up and keep you updated.'],
            ['icon' => 'rocket',              'title' => 'You are set up and confident', 'text' => 'You get working technology and know how to use it and keep it safe.'],
        ],
    ],

    'about' => [
        'eyebrow' => 'About Enoma',
        'heading' => 'Technology With a Human Approach',
        'paragraphs' => [
            'Enoma Digital Technologies was created to make technology more accessible, practical and secure for the people and organizations who depend on it every day.',
            'We believe good technology help starts with listening. Whether you are launching a website, recovering from a frustrating tech problem or learning how to protect your accounts, you deserve clear explanations, honest recommendations and solutions that fit your goals.',
        ],
        'values' => ['Clarity over jargon', 'Security by default', 'Honest recommendations', 'Education that lasts'],
    ],
];
