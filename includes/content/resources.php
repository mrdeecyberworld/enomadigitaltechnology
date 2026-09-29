<?php
/**
 * Resources: short, practical guides. General educational information only.
 * Each guide has an anchor on /resources (#slug).
 */

return [
    [
        'slug'    => 'secure-your-accounts',
        'icon'    => 'key-round',
        'tag'     => 'Cybersecurity',
        'title'   => 'Five steps to secure your most important accounts',
        'summary' => 'Your email account is the key to almost everything else. Start there.',
        'steps'   => [
            'List the accounts that matter most: email, banking, work tools, social media and your domain or website host.',
            'Use a unique password for each one. A reputable password manager makes this realistic.',
            'Turn on multi-factor authentication (MFA), preferably with an authenticator app or security key rather than text messages.',
            'Save your backup or recovery codes somewhere safe and offline.',
            'Review recovery email addresses and phone numbers so you can get back in if something goes wrong.',
        ],
    ],
    [
        'slug'    => 'spot-phishing',
        'icon'    => 'fish',
        'tag'     => 'Cybersecurity',
        'title'   => 'How to recognize a phishing message',
        'summary' => 'Phishing works by creating urgency. Slowing down is your best defense.',
        'steps'   => [
            'Be cautious of urgent requests involving money, gift cards, passwords or "account suspension."',
            'Check the sender\'s actual email address and hover over links before clicking.',
            'Watch for unexpected attachments, even from people you know.',
            'When in doubt, contact the person or company using a phone number or website you already trust, not the one in the message.',
            'If you clicked something suspicious, change your password from a trusted device and tell your IT contact promptly.',
        ],
    ],
    [
        'slug'    => 'backups-that-work',
        'icon'    => 'hard-drive',
        'tag'     => 'Cloud & Data',
        'title'   => 'Backups that actually work: the 3-2-1 approach',
        'summary' => 'A backup you have never tested is a hope, not a plan.',
        'steps'   => [
            'Keep 3 copies of important data: the original plus two backups.',
            'Store them on 2 different types of storage, for example a cloud service and an external drive.',
            'Keep 1 copy offsite or in the cloud, away from your main location.',
            'Automate backups so they happen without anyone remembering to run them.',
            'Test a restore every few months to confirm your files can really be recovered.',
        ],
    ],
    [
        'slug'    => 'website-checklist',
        'icon'    => 'layout-template',
        'tag'     => 'Web Development',
        'title'   => 'A small business website checklist',
        'summary' => 'The essentials every business website should get right.',
        'steps'   => [
            'Clearly state what you do, who you help and where you operate within the first screen.',
            'Make contacting you effortless with a visible call to action on every page.',
            'Ensure the site loads quickly and works well on phones.',
            'Use HTTPS, keep software updated and back up your site regularly.',
            'Give every page a unique title and description so search engines can understand it.',
        ],
    ],
];
