<?php
/**
 * Founder profile for the About page, from your CV (Admin → About me).
 * Each part appears on the About page once it has content.
 */

return [
    'story_heading'       => 'My story',
    'story'               => "I’m Enoma Divine Omozusi, the founder of Enoma Digital Technologies. I’m an IT Support Engineer with a master’s degree in cybersecurity, and I currently support enterprise users at Microsoft, providing L1 and L2 support across Windows and macOS and looking after user access, onboarding and the identity data that keeps daily work running smoothly.\n\nMy path into technology started with a National Diploma in Computer Science at Auchi Polytechnic. I went on to earn a BSc in European Studies at the John Paul II Catholic University of Lublin and an MSc in Cybersecurity at Maria Curie-Skłodowska University in Poland.\n\nBefore Microsoft, I spent over two years at Assurance Microfinance Co-operative Society as an IT Support Engineer in identity and access operations. I managed user identity data, helped colleagues with software and security issues, and wrote the onboarding guides and knowledge base articles that helped people solve problems on their own.\n\nI started Enoma Digital Technologies to bring that same enterprise-level care (secure accounts, well-managed devices and clear explanations) to small businesses, organizations and individuals, wherever they are in the world.",
    'inspiration_heading' => 'What inspires me',
    'inspiration'         => "Every day I see how much smoother work becomes when technology is set up properly: the right people have the right access, devices are secure and well managed, and problems are solved quickly. I also see how stressful it is when that is missing.\n\nLarge companies have whole teams for this. Most small businesses and individuals don’t. What drives me is closing that gap, so that everyone can have secure, reliable technology and actually understand it, not just use it.\n\nThat is why I care so much about clear explanations and good documentation. The best fix is one that leaves you more confident than you were before.",
    'quote'               => '',
    'quote_source'        => '',
    'experience'          => [
        [
            'role'         => 'Dispatch Engineer / IT Support',
            'organization' => 'Microsoft',
            'period'       => 'Oct 2024 – Present',
            'summary'      => 'L1 and L2 support for enterprise users across Windows, macOS and Linux environments, with a focus on user access, troubleshooting and keeping daily operations running smoothly.',
            'highlights'   => [
                'Validate and update user accounts across enterprise systems, keeping identity data accurate and consistent',
                'Handle onboarding, offboarding and role changes in line with internal access policies',
                'Investigate and resolve L2/L3 issues involving networking, authentication, APIs, performance and application configuration',
                'Support deployment and testing in virtualized and containerized environments (VMware, Hyper-V, Docker, Kubernetes)',
                'Work with QA and engineering teams to escalate defects and improve service delivery',
            ],
        ],
        [
            'role'         => 'IT Support Engineer / IAM Operations Support',
            'organization' => 'Assurance Microfinance Co-operative Society',
            'period'       => 'Feb 2022 – Apr 2024',
            'summary'      => 'Identity and access support for internal users, plus documentation, reporting and process improvement.',
            'highlights'   => [
                'Managed user identity data across systems, ensuring complete and accurate access records',
                'Investigated software and security issues affecting internal users with technical teams',
                'Wrote and maintained onboarding guides, troubleshooting documentation and knowledge base articles',
                'Followed defined procedures for handling user data in line with internal controls and compliance requirements',
            ],
        ],
    ],
    'education'           => [
        ['qualification' => 'MSc in Cybersecurity', 'institution' => 'Maria Curie-Skłodowska University, Poland', 'year' => '2025'],
        ['qualification' => 'BSc in European Studies', 'institution' => 'John Paul II Catholic University of Lublin, Poland', 'year' => '2023'],
        ['qualification' => 'National Diploma in Computer Science', 'institution' => 'Auchi Polytechnic', 'year' => '2015'],
    ],
    'certifications'      => [
        'AWS Certified Solutions Architect – Associate (2025)',
        'Career Essentials in Generative AI – Microsoft and LinkedIn Learning (2024)',
        'Career Essentials in Cybersecurity – Microsoft and LinkedIn Learning (2024)',
        'Cybersecurity Job Simulation – Mastercard, via Forage (2023)',
    ],
    'skills'              => [
        'Windows and macOS support (L1/L2)',
        'Identity and access management',
        'Active Directory and Entra ID (Azure AD)',
        'Device management: Jamf and Intune',
        'Microsoft 365 and Google Workspace',
        'Virtualization: VMware and Hyper-V',
        'Docker and Kubernetes',
        'Linux',
        'Networking and authentication troubleshooting',
        'Jira, Zendesk and CRM tools',
        'Technical documentation',
        'Problem solving',
        'Clear communication',
        'Teamwork',
    ],
];
