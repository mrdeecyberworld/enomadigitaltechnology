<?php
/**
 * Homepage copy and shared marketing blocks (why Enoma, process, cybersecurity, training).
 */

return [
    // Which homepage sections are shown (Admin → Homepage → Sections to show).
    // Kept short on purpose: details live on their own pages.
    'sections' => [
        'freelancer' => true,
        'trust'     => false,
        'why'       => false,
        'security'  => false,
        'assistant' => false,
        'training'  => false,
        'process'   => true,
        'about'     => false,
        'showcase'  => true,
    ],

    'hero' => [
        'eyebrow'  => 'Freelance web developer and IT specialist · Remote, worldwide',
        'headline' => 'Websites, IT support and cybersecurity. Done properly.',
        'text'     => 'I am a freelance web developer and IT specialist, working as Enoma Digital Technologies. I build websites, set up technology that works, keep it secure and explain everything in plain language, for small businesses, professionals and families worldwide.',
        'points'   => ['MSc in Cybersecurity', 'AWS Certified Solutions Architect', 'Enterprise IT support experience'],
    ],

    // "I'm a freelancer" section under the hero (Admin → Homepage → Freelancer section).
    'freelancer' => [
        'eyebrow' => 'Your freelancer',
        'heading' => "I'm a freelancer, so you work with me directly",
        'text'    => 'No agency, no account managers and no hand-offs. When you hire Enoma Digital Technologies, you hire me: I plan the work, do it myself and stay your contact from the first message to the final handover.',
        'photo'   => '', // your picture (Admin → Homepage → Freelancer section → Your photo)
        'role'    => 'Freelance web developer and IT specialist',
        'facts'   => ['Remote, working with clients worldwide', 'MSc in Cybersecurity', 'AWS Certified Solutions Architect'],
        'ways'    => [
            ['icon' => 'rocket',         'title' => 'One-off projects',   'text' => 'A website, a migration or a security setup, for a fixed price agreed before I start.'],
            ['icon' => 'calendar-check', 'title' => 'Ongoing support',    'text' => 'A monthly plan for website care or IT support, so help is always one message away.'],
            ['icon' => 'clock',          'title' => 'Pay-as-you-go help', 'text' => 'Prepaid hours for quick fixes and questions, whenever you need them.'],
        ],
    ],

    // Slider near the bottom of the homepage (Admin → Homepage → Slider).
    'showcase' => [
        'eyebrow' => 'Hire a freelancer',
        'heading' => 'One freelancer, everything your technology needs',
        'text'    => 'A quick look at what I can take off your plate. Every project is handled personally, start to finish.',
        'slides'  => [
            ['photo' => 'webdev',     'icon' => 'code-xml',       'tag' => 'Web development',     'title' => 'Websites that bring you customers',        'text' => 'Fast, secure, mobile-ready websites with a clear message and an easy way to get in touch.', 'label' => 'Get a website quote',   'path' => '/web-development'],
            ['photo' => 'cyber',      'icon' => 'shield-check',   'tag' => 'Cybersecurity',       'title' => 'Accounts and data locked down properly',   'text' => 'MFA, email security, backups and a practical security check, explained without jargon.',  'label' => 'Improve your security', 'path' => '/cybersecurity'],
            ['photo' => 'itsupport',  'icon' => 'headset',        'tag' => 'Remote IT support',   'title' => 'Tech problems fixed, wherever you are',    'text' => 'Friendly remote help with computers, email and accounts, with every fix explained.',      'label' => 'Get IT help',           'path' => '/it-support'],
            ['photo' => 'cloud',      'icon' => 'cloud',          'tag' => 'Cloud and email',     'title' => 'Microsoft 365 and Google Workspace, set up right', 'text' => 'Professional email, shared files and secure sign-ins for you and your team.',     'label' => 'Explore cloud setup',   'path' => '/cloud-services'],
            ['photo' => 'training',   'icon' => 'graduation-cap', 'tag' => 'Training',            'title' => 'Confidence with technology, at any age',   'text' => 'Plain-language training on phishing, passwords and staying safe online.',               'label' => 'See training',          'path' => '/training'],
            ['photo' => 'consulting', 'icon' => 'lightbulb',      'tag' => 'Consulting',          'title' => 'Honest advice before you spend',           'text' => 'The right tools for your goals and budget, and a straight answer when you do not need something.', 'label' => 'Book a consultation', 'path' => '/book-a-consultation'],
        ],
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
        'eyebrow' => 'How I work',
        'heading' => 'Straight answers, no jargon.',
        'text'    => "Most people don't need more technology. They need the right setup, explained clearly, by someone who answers their messages. That is how I like to work.",
        'points'  => [
            ['icon' => 'heart-handshake', 'title' => 'You deal with me, the person doing the work', 'text' => 'No call centre and no ticket queue. You talk directly to the engineer looking after your setup.'],
            ['icon' => 'shield-check',    'title' => 'Security from the start',                 'text' => 'Accounts, backups and updates are set up properly from day one, not patched on later.'],
            ['icon' => 'target',          'title' => 'Only what you need',                      'text' => "I recommend what fits your goals and budget, and I tell you when you don't need something."],
            ['icon' => 'lightbulb',       'title' => 'You understand your own setup',           'text' => 'I explain what I am doing as I go, so you are never left guessing.'],
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
            ['icon' => 'message-square-text', 'title' => 'Tell me what you need',      'text' => 'Book a short call or send me a message about your goals or the problem.'],
            ['icon' => 'search',              'title' => 'I look at your situation',   'text' => 'I ask questions and check your current setup before recommending anything.'],
            ['icon' => 'wrench',              'title' => 'We agree a plan, then I do it', 'text' => 'You get a clear plan and price, then I build, fix or set things up and keep you updated.'],
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
