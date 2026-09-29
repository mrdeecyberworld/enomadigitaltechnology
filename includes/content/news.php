<?php
/**
 * Sources for Admin → Tech & security news. Edit in Admin → News sources.
 * Feeds are RSS or Atom URLs. Headlines link to the original articles;
 * write blog posts in your own words and credit the source.
 */

return [
    'feeds' => [
        ['name' => 'CISA Cybersecurity Advisories', 'url' => 'https://www.cisa.gov/cybersecurity-advisories/all.xml', 'topic' => 'advisories'],
        ['name' => 'CISA News',                     'url' => 'https://www.cisa.gov/news.xml',                          'topic' => 'advisories'],
        ['name' => 'Krebs on Security',             'url' => 'https://krebsonsecurity.com/feed/',                       'topic' => 'cybersecurity'],
        ['name' => 'BleepingComputer',              'url' => 'https://www.bleepingcomputer.com/feed/',                  'topic' => 'cybersecurity'],
        ['name' => 'The Hacker News',               'url' => 'https://feeds.feedburner.com/TheHackersNews',             'topic' => 'cybersecurity'],
        ['name' => 'Dark Reading',                  'url' => 'https://www.darkreading.com/rss.xml',                     'topic' => 'cybersecurity'],
        ['name' => 'SANS Internet Storm Center',    'url' => 'https://isc.sans.edu/rssfeed.xml',                        'topic' => 'cybersecurity'],
        ['name' => 'Schneier on Security',          'url' => 'https://www.schneier.com/feed/atom/',                     'topic' => 'cybersecurity'],
        ['name' => 'Microsoft Security Blog',       'url' => 'https://www.microsoft.com/en-us/security/blog/feed/',     'topic' => 'cybersecurity'],
        ['name' => 'Google Online Security Blog',   'url' => 'https://security.googleblog.com/feeds/posts/default',     'topic' => 'cybersecurity'],
        ['name' => 'Ars Technica',                  'url' => 'https://feeds.arstechnica.com/arstechnica/index',         'topic' => 'technology'],
        ['name' => 'The Verge',                     'url' => 'https://www.theverge.com/rss/index.xml',                  'topic' => 'technology'],
        ['name' => 'TechCrunch',                    'url' => 'https://techcrunch.com/feed/',                            'topic' => 'technology'],
        ['name' => 'Wired',                         'url' => 'https://www.wired.com/feed/rss',                          'topic' => 'technology'],
    ],
    'links' => [
        ['label' => 'CISA Known Exploited Vulnerabilities', 'url' => 'https://www.cisa.gov/known-exploited-vulnerabilities-catalog', 'note' => 'Vulnerabilities being actively exploited. Check software your clients use.'],
        ['label' => 'CISA Secure Our World',                'url' => 'https://www.cisa.gov/secure-our-world',                         'note' => 'Plain-language security tips for families and small businesses.'],
        ['label' => 'FTC Consumer Alerts',                  'url' => 'https://consumer.ftc.gov/consumer-alerts',                      'note' => 'Current scams targeting U.S. consumers. Great for training topics.'],
        ['label' => 'FBI Internet Crime Complaint Center',  'url' => 'https://www.ic3.gov/',                                          'note' => 'Where U.S. victims report cybercrime; public service announcements.'],
        ['label' => 'NIST Small Business Cybersecurity',    'url' => 'https://www.nist.gov/itl/smallbusinesscyber',                   'note' => 'Guides and resources written for small businesses.'],
        ['label' => 'NIST Cybersecurity Framework',         'url' => 'https://www.nist.gov/cyberframework',                           'note' => 'The standard framework for managing cyber risk.'],
        ['label' => 'Have I Been Pwned',                    'url' => 'https://haveibeenpwned.com/',                                   'note' => 'Check whether an email address appears in known data breaches.'],
        ['label' => 'CVE Program',                          'url' => 'https://www.cve.org/',                                          'note' => 'Look up publicly disclosed vulnerabilities by ID.'],
        ['label' => 'National Cybersecurity Alliance',      'url' => 'https://www.staysafeonline.org/',                               'note' => 'Awareness campaigns and educational material.'],
        ['label' => 'Hacker News',                          'url' => 'https://news.ycombinator.com/',                                 'note' => 'What the tech community is discussing today.'],
        ['label' => 'Google Search Console',                'url' => 'https://search.google.com/search-console',                     'note' => 'See how your website performs in Google search.'],
    ],
];
