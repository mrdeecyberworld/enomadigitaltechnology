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
        'headline' => 'Websites, IT support and cybersecurity. Explained in plain language.',
        'text'     => 'I am a freelance web developer and IT specialist, working as Enoma Digital Technologies. I build websites and set up and secure the technology that small businesses, professionals and families rely on, for clients anywhere in the world.',
        'points'   => ['MSc in Cybersecurity', 'AWS Certified Solutions Architect', 'Enterprise IT support experience'],
    ],

    // "I'm a freelancer" section under the hero (Admin → Homepage → Freelancer section).
    'freelancer' => [
        'eyebrow' => 'Your freelancer',
        'heading' => "I'm a freelancer, so you work with me directly",
        'text'    => 'When you hire Enoma Digital Technologies, you hire me. I plan the work, do it myself and stay your contact from the first message to the handover, with no agency or account manager in between.',
        'photo'   => '', // your picture (Admin → Homepage → Freelancer section → Your photo)
        'role'    => 'Freelance web developer and IT specialist',
        'facts'   => ['Remote, working with clients worldwide', 'MSc in Cybersecurity', 'AWS Certified Solutions Architect'],
        'ways'    => [
            ['icon' => 'rocket',         'title' => 'One-off projects',   'text' => 'A website, a migration or a security setup, for a fixed price agreed before I start.'],
            ['icon' => 'calendar-check', 'title' => 'Ongoing support',    'text' => 'A monthly plan for website care or IT support, so you can message me whenever something goes wrong.'],
            ['icon' => 'clock',          'title' => 'Pay-as-you-go help', 'text' => 'Prepaid hours for quick fixes and questions, whenever you need them.'],
        ],
    ],

    // Slider near the bottom of the homepage (Admin → Homepage → Slider).
    'showcase' => [
        'eyebrow' => 'Hire a freelancer',
        'heading' => 'Six ways I can help',
        'text'    => 'I handle every project myself, from the first call to the handover.',
        'slides'  => [
            ['photo' => 'webdev',     'icon' => 'code-xml',       'tag' => 'Web development',     'title' => 'Websites that bring you customers',        'text' => 'Fast, secure, mobile-ready websites with a clear message and an easy way to get in touch.', 'label' => 'Get a website quote',   'path' => '/web-development'],
            ['photo' => 'cyber',      'icon' => 'shield-check',   'tag' => 'Cybersecurity',       'title' => 'Accounts and data that are hard to break into', 'text' => 'MFA, email security, backups and a practical security check, explained without jargon.',  'label' => 'Improve your security', 'path' => '/cybersecurity'],
            ['photo' => 'itsupport',  'icon' => 'headset',        'tag' => 'Remote IT support',   'title' => 'Tech problems fixed, wherever you are',    'text' => 'Friendly remote help with computers, email and accounts, with every fix explained.',      'label' => 'Get IT help',           'path' => '/it-support'],
            ['photo' => 'cloud',      'icon' => 'cloud',          'tag' => 'Cloud and email',     'title' => 'Microsoft 365 and Google Workspace, set up right', 'text' => 'Professional email, shared files and secure sign-ins for you and your team.',     'label' => 'Explore cloud setup',   'path' => '/cloud-services'],
            ['photo' => 'training',   'icon' => 'graduation-cap', 'tag' => 'Training',            'title' => 'Training for every age and level',          'text' => 'Plain-language training on phishing, passwords and staying safe online.',               'label' => 'See training',          'path' => '/training'],
            ['photo' => 'consulting', 'icon' => 'lightbulb',      'tag' => 'Consulting',          'title' => 'Honest advice before you spend',           'text' => 'Advice on which tools fit your goals and budget. If you do not need something, I will tell you.', 'label' => 'Book a consultation', 'path' => '/book-a-consultation'],
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
        'heading' => 'Plain answers from the person doing the work',
        'text'    => "Most people need the technology they already have set up properly and explained clearly, by someone who answers their messages.",
        'points'  => [
            ['icon' => 'heart-handshake', 'title' => 'You deal with me, the person doing the work', 'text' => 'There is no call centre or ticket queue. You talk directly to the engineer looking after your setup.'],
            ['icon' => 'shield-check',    'title' => 'Security from the start',                 'text' => 'I set up accounts, backups and updates properly on day one.'],
            ['icon' => 'target',          'title' => 'Only what you need',                      'text' => "I recommend what fits your goals and budget, and I tell you when you don't need something."],
            ['icon' => 'lightbulb',       'title' => 'You understand your own setup',           'text' => 'I explain what I am doing as I go, so you are never left guessing.'],
        ],
    ],

    'security' => [
        'eyebrow' => 'Cybersecurity',
        'heading' => 'Small businesses and families need security too',
        'text'    => "Anyone with email, online banking or a website has accounts and data worth protecting, and small organizations are often easier targets than large ones.",
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
        'text'    => 'Every project follows the same four steps, so you know what comes next.',
        'steps'   => [
            ['icon' => 'message-square-text', 'title' => 'Tell me what you need',      'text' => 'Book a short call or send me a message about your goals or the problem.'],
            ['icon' => 'search',              'title' => 'I look at your situation',   'text' => 'I ask questions and check your current setup before recommending anything.'],
            ['icon' => 'wrench',              'title' => 'We agree a plan, then I do it', 'text' => 'You get a clear plan and price, then I build, fix or set things up and keep you updated.'],
            ['icon' => 'rocket',              'title' => 'You are set up and know how it works', 'text' => 'You get working technology and know how to use it and keep it safe.'],
        ],
    ],

    'about' => [
        'eyebrow' => 'About Enoma',
        'heading' => 'Why I started Enoma',
        'paragraphs' => [
            'I started Enoma Digital Technologies so that small businesses and individuals can get the secure, well-run technology that large companies take for granted.',
            'Good help starts with listening. Whether you are launching a website, dealing with a tech problem or learning to protect your accounts, I explain things clearly, recommend only what you need and fit the work to your goals.',
        ],
        'values' => ['Plain language', 'Security from the start', 'Honest recommendations', 'You learn how your setup works'],
    ],
];
