<?php
/**
 * Services catalogue.
 *
 * To add a service later:
 *   1. Add an entry below (the array key is the URL slug).
 *   2. Create /your-slug.php containing:
 *        <?php $serviceSlug = 'your-slug'; require __DIR__ . '/includes/templates/service-page.php';
 *   3. Add the path to 'sitemap' in includes/content/site.php.
 * Cards, menus, the service finder, forms and the AI assistant pick it up automatically.
 */

return [
    'web-development' => [
        'name'      => 'Web Development',
        'nav_label' => 'Web Development',
        'icon'      => 'code-xml',
        'summary'   => 'Business websites that look professional, work on every device and make it easy for customers to contact you.',
        'includes'  => ['Business websites', 'Landing pages', 'Portfolio websites', 'Website redesign', 'Website maintenance', 'Domain and hosting setup', 'Basic SEO'],
        'cta'       => 'Explore Web Development',
        'image'     => 'webdev',
        'meta_title'       => 'Web Development Services for Small Businesses | Enoma Digital Technologies',
        'meta_description' => 'Website development and website design for small businesses, startups and professionals. Responsive business websites, landing pages, redesigns, maintenance and basic SEO.',
        'eyebrow'   => 'Web Development',
        'headline'  => 'A website that explains what you do and brings in enquiries.',
        'intro'     => 'For many customers, your website is the first thing they see of your business. I design and build fast websites that say clearly what you do, look right on phones and computers, and make it easy to get in touch.',
        'details'   => [
            ['title' => 'Business websites',        'text' => 'A site with a few pages for your services, your story and a clear way to contact you.'],
            ['title' => 'Landing pages',            'text' => 'One page for a single offer, event or campaign, with one clear thing for visitors to do.'],
            ['title' => 'Portfolio websites',       'text' => 'A simple site where professionals, creatives and consultants can show their work.'],
            ['title' => 'Website redesign',         'text' => 'Bring an old site up to date with a clearer layout, better wording and pages that work on phones.'],
            ['title' => 'Website maintenance',      'text' => 'Updates, content changes, backups and routine checks so your site stays current and secure.'],
            ['title' => 'Domain and hosting setup', 'text' => 'Help choosing, connecting and configuring your domain, hosting and business email.'],
            ['title' => 'Basic SEO',                'text' => 'Page titles, descriptions, headings and speed improvements so search engines can understand your site.'],
        ],
        'audience'  => ['Small businesses', 'Startups and entrepreneurs', 'Professionals and consultants', 'Nonprofits', 'Schools and community organizations'],
        'faqs' => [
            ['q' => 'How long does it take to build a small business website?', 'a' => 'It depends on how many pages you need, whether your text and photos are ready, and how many rounds of changes we go through. After a first call I give you a timeline for your project.'],
            ['q' => 'Will my website work on phones and tablets?', 'a' => 'Yes. Every site I build adjusts its layout to phones, tablets and computer screens.'],
            ['q' => 'Can you update or redesign my existing website?', 'a' => 'Yes. I can look at your current site, suggest improvements, and then either refresh it or rebuild it, depending on your goals and budget.'],
            ['q' => 'Do you include security in website projects?', 'a' => 'Yes. From the start I set up HTTPS, sensible logins and permissions, software updates and backups.'],
        ],
    ],

    'cybersecurity' => [
        'name'      => 'Cybersecurity',
        'nav_label' => 'Cybersecurity',
        'icon'      => 'shield-check',
        'summary'   => 'Practical security help and training so your accounts, devices and data are harder to attack.',
        'includes'  => ['Security awareness', 'Account security', 'MFA setup', 'Phishing awareness', 'Basic security assessments', 'Security guidance', 'Cybersecurity education'],
        'cta'       => 'Explore Cybersecurity',
        'image'     => 'cyber',
        'meta_title'       => 'Cybersecurity Services for Small Businesses & Individuals | Enoma Digital Technologies',
        'meta_description' => 'Practical cybersecurity services: account security, MFA setup, phishing awareness, basic security assessments and cybersecurity awareness training for small businesses, families and individuals.',
        'eyebrow'   => 'Cybersecurity Services',
        'headline'  => 'Security that fits how you work every day.',
        'intro'     => 'Most security problems start with something ordinary, like a reused password, a convincing phishing email or an account without MFA. I help you fix those gaps with clear, practical steps, and show you how to keep them fixed.',
        'details'   => [
            ['title' => 'Security awareness',         'text' => 'Plain explanations of the most common threats and the habits that stop them.'],
            ['title' => 'Account security',           'text' => 'A check of your most important email, banking, social media and business accounts, and fixes where needed.'],
            ['title' => 'MFA setup',                  'text' => 'Multi-factor authentication on your important accounts, with backup options so you do not get locked out.'],
            ['title' => 'Phishing awareness',         'text' => 'Learn to recognize suspicious emails, texts and calls before they cause harm.'],
            ['title' => 'Basic security assessments', 'text' => 'A careful look at your accounts, devices and habits, with a list of what to fix first.'],
            ['title' => 'Security guidance',          'text' => 'Plain advice on passwords, backups, updates, Wi-Fi and using cloud services safely.'],
            ['title' => 'Cybersecurity education',    'text' => 'Sessions for individuals, teams and families that leave people confident rather than scared.'],
        ],
        'audience'  => ['Small businesses and startups', 'Professionals working remotely', 'Families and older adults', 'Nonprofits and schools'],
        'note'      => 'My security work is defensive and educational. I only check systems with the owner\'s permission.',
        'faqs' => [
            ['q' => 'Is cybersecurity only for large companies?', 'a' => 'No. Small businesses, professionals and families are often targeted because they usually have fewer protections. Simple steps like multi-factor authentication and learning to spot phishing stop many common attacks.'],
            ['q' => 'What is a basic security assessment?', 'a' => 'I go through your accounts, devices, backups and everyday habits, with your permission, and give you a list of recommendations with the most important first.'],
            ['q' => 'Can you help secure my business website?', 'a' => 'Yes. I check the basics, such as HTTPS, software updates, who has access, backups and hosting settings, and then either recommend fixes or make them for you.'],
            ['q' => 'Do you respond to active security incidents?', 'a' => 'If you think you are being attacked right now, contact me and I will help you work out the next steps. For serious incidents I may point you to a specialist incident response company and, where appropriate, the police.'],
        ],
    ],

    'it-support' => [
        'name'      => 'IT Support',
        'nav_label' => 'IT Support',
        'icon'      => 'headset',
        'summary'   => 'Remote help when your computer, email, Wi-Fi or devices stop working.',
        'includes'  => ['Computer troubleshooting', 'Software installation', 'Email setup', 'Network troubleshooting', 'Device configuration', 'Remote support'],
        'cta'       => 'Get IT Support',
        'image'     => 'itsupport',
        'meta_title'       => 'Remote IT Support & Technical Support | Enoma Digital Technologies',
        'meta_description' => 'Friendly, reliable remote IT support and technical support for small businesses and individuals: computer troubleshooting, software, email setup, networks and device configuration.',
        'eyebrow'   => 'IT & Technical Support',
        'headline'  => 'Patient help when your technology stops working.',
        'intro'     => 'When something stops working, you want someone who listens, explains what went wrong and fixes it properly. I provide remote support for businesses and individuals, from a slow laptop to setting up a new office.',
        'details'   => [
            ['title' => 'Computer troubleshooting', 'text' => 'Finding the cause of slowness, error messages, crashes and other everyday computer problems.'],
            ['title' => 'Software installation',    'text' => 'Installing, updating and setting up the programs you use.'],
            ['title' => 'Email setup',              'text' => 'Business or personal email set up on your computer, phone and tablet.'],
            ['title' => 'Network troubleshooting',  'text' => 'Fixing Wi-Fi, connection and router problems.'],
            ['title' => 'Device configuration',     'text' => 'Setting up new computers, phones, printers and accessories so they work together.'],
            ['title' => 'Remote support',           'text' => 'Secure screen sharing, only with your permission, so I can help without visiting.'],
        ],
        'audience'  => ['Small offices and home offices', 'Professionals and freelancers', 'Older adults and families', 'Nonprofits'],
        'faqs' => [
            ['q' => 'Do you provide remote IT support?', 'a' => 'Yes. Most of my support happens remotely through secure screen sharing, only with your permission, and you can watch everything I do.'],
            ['q' => 'What if my problem cannot be fixed remotely?', 'a' => 'I will explain what I found and what to do next, for example taking it to a repair shop or replacing the device.'],
            ['q' => 'Can you help older family members with technology?', 'a' => 'Yes. I go step by step and at their pace, whatever their experience.'],
        ],
    ],

    'training' => [
        'name'      => 'Technology Training',
        'layout'    => 'training', // uses the dedicated Training page design
        'short_label' => 'Training',
        'nav_label' => 'Training',
        'icon'      => 'graduation-cap',
        'summary'   => 'Plain-language technology and security training for beginners, teams and everyone in between.',
        'includes'  => ['Beginner technology training', 'Cybersecurity awareness', 'Digital literacy', 'Cybersecurity fundamentals', 'Training for businesses', 'Training for young people and adults'],
        'cta'       => 'Explore Training',
        'image'     => 'training',
        'meta_title'       => 'Cybersecurity Awareness Training & Technology Training | Enoma Digital Technologies',
        'meta_description' => 'Cybersecurity training and technology education for businesses, schools, young people, adults and seniors. Phishing awareness, password security, online safety and digital literacy.',
        'eyebrow'   => 'Technology & Cybersecurity Training',
        'headline'  => 'Technology training for people of every age and level.',
        'intro'     => 'Anyone can learn to feel at ease with technology. My training explains technology and security in plain language, with exercises suited to the group, whether that is a business team, a classroom or older adults.',
        'details'   => [
            ['title' => 'Beginner technology training', 'text' => 'Relaxed, jargon-free introductions to computers, smartphones and the internet.'],
            ['title' => 'Cybersecurity awareness',      'text' => 'How to spot phishing, scams and risky habits, and what to do about them.'],
            ['title' => 'Digital literacy',             'text' => 'Searching, judging what you read, communicating and getting work done online.'],
            ['title' => 'Cybersecurity fundamentals',   'text' => 'An introduction to how security works for students and career-curious adults.'],
            ['title' => 'Training for businesses',      'text' => 'Security awareness training so staff know how to protect company accounts and data.'],
            ['title' => 'Training for young people and adults', 'text' => 'Age-appropriate sessions on online safety, privacy and responsible technology use.'],
        ],
        'audience'  => ['Young people', 'Adults', 'Seniors', 'Business teams', 'Schools', 'Community organizations'],
        'faqs' => [
            ['q' => 'Do you offer cybersecurity training?', 'a' => 'Yes. I run security awareness training for business teams, schools, community groups and individuals, plus an introduction to cybersecurity for people interested in the field.'],
            ['q' => 'Is training available online?', 'a' => 'Yes. All training is live and online. Tell me about your group and I will suggest a format.'],
            ['q' => 'Can training be adapted to our organization?', 'a' => 'Yes. I adjust the topics, examples and pace to your group, whether that is a small office, a classroom or a seniors group.'],
        ],
    ],

    'cloud-services' => [
        'name'      => 'Cloud & Digital Technology',
        'nav_label' => 'Cloud & Digital Technology',
        'icon'      => 'cloud',
        'summary'   => 'Microsoft 365, Google Workspace, cloud storage and backups, set up properly.',
        'includes'  => ['Cloud setup', 'Backups', 'Microsoft 365', 'Google Workspace', 'Cloud storage', 'Digital workflow setup'],
        'cta'       => 'Explore Cloud Services',
        'image'     => 'cloud',
        'meta_title'       => 'Cloud Setup, Microsoft 365 & Google Workspace Help | Enoma Digital Technologies',
        'meta_description' => 'Cloud and digital technology services for small businesses and individuals: cloud setup, backups, Microsoft 365, Google Workspace, cloud storage and digital workflow setup.',
        'eyebrow'   => 'Cloud & Digital Technology',
        'headline'  => 'Email, files and backups that work from anywhere.',
        'intro'     => 'Good cloud tools make it easier to work together, keep files organized and recover when something goes wrong. I help you choose, set up and secure the services that suit how you work.',
        'details'   => [
            ['title' => 'Cloud setup',            'text' => 'Planning and setting up cloud services for email, files and working together.'],
            ['title' => 'Backups',                'text' => 'Automatic backups, tested, so you can get important files back after a loss or ransomware.'],
            ['title' => 'Microsoft 365',          'text' => 'Accounts, email, Teams, OneDrive and security settings.'],
            ['title' => 'Google Workspace',       'text' => 'Gmail on your own domain, Drive, shared calendars and admin security.'],
            ['title' => 'Cloud storage',          'text' => 'Shared folders and permissions arranged so each person can open what they need and nothing else.'],
            ['title' => 'Digital workflow setup', 'text' => 'Forms, calendars, documents and e-signatures connected so there is less copying and pasting.'],
        ],
        'audience'  => ['Small businesses moving to the cloud', 'Remote and hybrid teams', 'Professionals and freelancers', 'Nonprofits'],
        'faqs' => [
            ['q' => 'Should I use Microsoft 365 or Google Workspace?', 'a' => 'Both are good. The choice depends on the tools your team already uses, the files you work with and your budget, and I can talk you through the differences.'],
            ['q' => 'Can you help move my email to a professional domain?', 'a' => 'Yes. I can set up email on your own domain name and move your existing messages across where possible.'],
        ],
    ],

    'technology-consulting' => [
        'name'      => 'Technology Consulting',
        'short_label' => 'Consulting',
        'nav_label' => 'Technology Consulting',
        'icon'      => 'compass',
        'summary'   => 'Honest advice on what technology to buy, build or skip.',
        'includes'  => ['Technology planning', 'Website strategy', 'Cybersecurity guidance', 'Digital transformation guidance', 'Small-business technology consulting'],
        'cta'       => 'Talk it through with me',
        'image'     => 'consulting',
        'meta_title'       => 'Small Business Technology Consulting | Enoma Digital Technologies',
        'meta_description' => 'Independent technology consulting for small businesses, startups and organizations: technology planning, website strategy, cybersecurity guidance and digital transformation.',
        'eyebrow'   => 'Technology Consulting',
        'headline'  => 'Get clear advice before you spend money.',
        'intro'     => 'Technology decisions are easier when someone explains the options plainly. I help you see where you are today, what you need and a realistic plan to get there.',
        'details'   => [
            ['title' => 'Technology planning',               'text' => 'A list of the tools you use now, what is missing, and improvements that fit your budget.'],
            ['title' => 'Website strategy',                  'text' => 'What your website needs to do, who it is for and how you will know it is working.'],
            ['title' => 'Cybersecurity guidance',            'text' => 'Which security improvements to make first in your situation.'],
            ['title' => 'Digital transformation guidance',   'text' => 'Moving paper and manual tasks to simple digital tools, one step at a time.'],
            ['title' => 'Small-business technology consulting', 'text' => 'Someone experienced to talk to before you choose software, hardware or a supplier.'],
        ],
        'audience'  => ['Startups and founders', 'Small businesses', 'Nonprofits and schools', 'Professionals'],
        'faqs' => [
            ['q' => 'What happens in a consultation?', 'a' => 'We talk about your goals and problems, I ask questions to understand your situation, and I suggest sensible next steps. You get useful advice whether or not you hire me.'],
            ['q' => 'Do you recommend specific vendors?', 'a' => 'I recommend tools based on your needs and budget, and I always explain why.'],
        ],
    ],
];
