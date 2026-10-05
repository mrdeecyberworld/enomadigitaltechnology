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
        'eyebrow'  => 'Build. Secure. Empower.',
        'headline' => 'Build Smarter. Stay Secure.',
        'text'     => 'Enoma Digital Technologies helps businesses and individuals build professional digital experiences, solve technology challenges, and strengthen their cybersecurity.',
        'points'   => ['Remote services worldwide', 'Security built in from day one', 'Clear, human communication'],
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
        'eyebrow' => 'Why Enoma',
        'heading' => 'Technology Without the Confusion',
        'text'    => 'Technology should make your work and life easier, not more complicated. Enoma Digital Technologies focuses on making technology practical, understandable and genuinely useful, with honest recommendations and clear explanations at every step.',
        'points'  => [
            ['icon' => 'heart-handshake', 'title' => 'Human Support',           'text' => 'Real people. Clear communication. Practical solutions.'],
            ['icon' => 'shield-check',    'title' => 'Security Mindset',        'text' => 'Security is considered from the beginning, not added as an afterthought.'],
            ['icon' => 'target',          'title' => 'Built Around Your Needs', 'text' => 'No unnecessary technology. Solutions based on your goals.'],
            ['icon' => 'lightbulb',       'title' => 'Education First',         'text' => "We don't just solve problems. We help you understand them."],
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
        'eyebrow' => 'Our Process',
        'heading' => 'How It Works',
        'text'    => 'A simple, transparent process from first conversation to lasting results.',
        'steps'   => [
            ['icon' => 'message-square-text', 'title' => 'Tell Us What You Need',                    'text' => 'Share your goals or the problem you are facing through a consultation or quick form.'],
            ['icon' => 'search',              'title' => 'We Understand the Problem',                'text' => 'We ask the right questions and look at your situation before recommending anything.'],
            ['icon' => 'wrench',              'title' => 'We Build or Recommend the Right Solution', 'text' => 'You get a clear plan, then we build, configure or guide, keeping you informed throughout.'],
            ['icon' => 'rocket',              'title' => 'You Move Forward With Confidence',         'text' => 'You leave with working technology and an understanding of how to use and protect it.'],
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
