<?php
/**
 * Admin content schemas. Each section maps to includes/content/{section}.php
 * (defaults) and storage/content/{section}.json (admin edits).
 *
 * Field types: text, textarea, richtext, lines, paragraphs, url, email, bool,
 * select, icon, image, password, group, repeater.
 */

declare(strict_types=1);

function admin_sections(): array
{
    $f = static fn (string $key, string $label, string $type = 'text', array $extra = []): array
        => ['key' => $key, 'label' => $label, 'type' => $type] + $extra;

    $seo = [
        $f('meta_title', 'SEO title', 'text', ['hint' => 'Shown in Google results and the browser tab. Aim for 50–60 characters.', 'counter' => 60]),
        $f('meta_description', 'SEO description', 'textarea', ['rows' => 2, 'hint' => 'Shown under the title in Google results. Aim for 140–160 characters.', 'counter' => 160]),
    ];
    $heroFields = [
        $f('eyebrow', 'Small label above the heading'),
        $f('heading', 'Page heading (H1)'),
        $f('intro', 'Intro text', 'textarea', ['rows' => 3]),
    ];
    $formPage = array_merge($seo, $heroFields, [
        $f('side_heading', 'Side column heading'),
        $f('side_points', 'Side column points', 'lines', ['hint' => 'One point per line (up to 3).']),
        $f('form_heading', 'Form heading'),
    ]);

    return [
        'site' => [
            'title' => 'Brand & navigation',
            'icon'  => 'globe',
            'intro' => 'Company name, taglines, menu and footer links.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('name', 'Business name'),
                $f('short_name', 'Short brand name'),
                $f('domain', 'Domain (display)'),
                $f('tagline', 'Primary tagline'),
                $f('statement', 'Supporting statement'),
                $f('description', 'Default site description', 'textarea', ['rows' => 2, 'hint' => 'Used for search engines and social sharing when a page has no description of its own.']),
                $f('service_area', 'Service area statement', 'text', ['hint' => 'Shown in the footer. Keep it accurate: e.g. remote services across the U.S.']),
                $f('nav', 'Main menu', 'repeater', ['item_label' => 'label', 'fields' => [
                    $f('label', 'Menu label'),
                    $f('path', 'Link', 'text', ['hint' => 'e.g. /about or /cybersecurity']),
                    $f('children', 'Dropdown', 'select', ['options' => ['' => 'None', 'services' => 'Show services dropdown']]),
                ]]),
                $f('cta_primary', 'Primary button (header)', 'group', ['fields' => [$f('label', 'Label'), $f('path', 'Link')]]),
                $f('cta_secondary', 'Secondary button (header)', 'group', ['fields' => [$f('label', 'Label'), $f('path', 'Link')]]),
                $f('footer_company', 'Footer "Company" links', 'repeater', ['item_label' => 'label', 'fields' => [$f('label', 'Label'), $f('path', 'Link')]]),
            ]],
        ],

        'home' => [
            'title' => 'Homepage',
            'icon'  => 'layout-template',
            'intro' => 'Hero, trust bar, Why Enoma, cybersecurity feature, process and about sections.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('hero', 'Hero', 'group', ['fields' => [
                    $f('eyebrow', 'Small label'),
                    $f('headline', 'Headline (H1)'),
                    $f('text', 'Supporting text', 'textarea', ['rows' => 3]),
                    $f('points', 'Check points under buttons', 'lines'),
                ]]),
                $f('trust', 'Trust bar', 'group', ['fields' => [
                    $f('heading', 'Heading'),
                    $f('items', 'Items', 'repeater', ['item_label' => 'label', 'fields' => [$f('label', 'Label'), $f('icon', 'Icon', 'icon'), $f('path', 'Link')]]),
                ]]),
                $f('why', 'Why Enoma', 'group', ['fields' => [
                    $f('eyebrow', 'Small label'), $f('heading', 'Heading'), $f('text', 'Text', 'textarea'),
                    $f('points', 'Points', 'repeater', ['item_label' => 'title', 'fields' => [$f('icon', 'Icon', 'icon'), $f('title', 'Title'), $f('text', 'Text', 'textarea', ['rows' => 2])]]),
                ]]),
                $f('security', 'Cybersecurity feature', 'group', ['fields' => [
                    $f('eyebrow', 'Small label'), $f('heading', 'Heading'), $f('text', 'Text', 'textarea'),
                    $f('items', 'Topics', 'repeater', ['item_label' => 'label', 'fields' => [$f('icon', 'Icon', 'icon'), $f('label', 'Label')]]),
                    $f('cta', 'Button', 'group', ['fields' => [$f('label', 'Label'), $f('path', 'Link')]]),
                ]]),
                $f('process', 'How it works', 'group', ['fields' => [
                    $f('eyebrow', 'Small label'), $f('heading', 'Heading'), $f('text', 'Text', 'textarea', ['rows' => 2]),
                    $f('steps', 'Steps', 'repeater', ['item_label' => 'title', 'fields' => [$f('icon', 'Icon', 'icon'), $f('title', 'Title'), $f('text', 'Text', 'textarea', ['rows' => 2])]]),
                ]]),
                $f('about', 'About section', 'group', ['fields' => [
                    $f('eyebrow', 'Small label'), $f('heading', 'Heading'),
                    $f('paragraphs', 'Paragraphs', 'paragraphs', ['hint' => 'Separate paragraphs with a blank line.']),
                    $f('values', 'Values (check list)', 'lines'),
                ]]),
            ]],
        ],

        'services' => [
            'title' => 'Services',
            'icon'  => 'layers-2',
            'intro' => 'Add, edit, reorder or remove services. Each service gets its own page at /slug automatically, and appears in the menu, footer, cards, forms and AI assistant.',
            'schema' => ['type' => 'repeater', 'map_key' => 'slug', 'item_label' => 'name', 'add_label' => 'Add service', 'fields' => [
                $f('slug', 'URL slug', 'text', ['hint' => 'Lowercase letters, numbers and dashes. The page lives at /slug. Changing it changes the page address.', 'required' => true]),
                $f('name', 'Service name', 'text', ['required' => true]),
                $f('short_label', 'Short name (footer)', 'text', ['hint' => 'Optional.']),
                $f('nav_label', 'Menu name'),
                $f('icon', 'Icon', 'icon'),
                $f('summary', 'Card summary', 'textarea', ['rows' => 2]),
                $f('includes', 'Card bullet list', 'lines'),
                $f('cta', 'Card link text'),
                $f('image', 'Page photo', 'image', ['hint' => 'Choose an uploaded image, or a photo name from Photos (e.g. cyber, webdev).']),
                $f('eyebrow', 'Page label / keyword (part of the H1)'),
                $f('headline', 'Page headline'),
                $f('intro', 'Page intro', 'textarea', ['rows' => 3]),
                $f('details', 'What we help with', 'repeater', ['item_label' => 'title', 'fields' => [$f('title', 'Title'), $f('text', 'Text', 'textarea', ['rows' => 2])]]),
                $f('audience', 'Who it’s for', 'lines'),
                $f('note', 'Note shown under the list (optional)', 'textarea', ['rows' => 2]),
                $f('faqs', 'Service FAQs', 'repeater', ['item_label' => 'q', 'fields' => [$f('q', 'Question'), $f('a', 'Answer', 'textarea', ['rows' => 3])]]),
                ...$seo,
            ]],
        ],

        'pages' => [
            'title' => 'Pages',
            'icon'  => 'file-text',
            'intro' => 'SEO titles, descriptions and header text for Home, Services, About, Resources, FAQ, Contact, Get a Quote and Book a Consultation.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('home', 'Home page', 'group', ['fields' => $seo]),
                $f('services', 'Services page', 'group', ['fields' => array_merge($seo, $heroFields)]),
                $f('about', 'About page', 'group', ['fields' => array_merge($seo, $heroFields, [$f('body', 'Extra paragraph', 'richtext')])]),
                $f('resources', 'Resources page', 'group', ['fields' => array_merge($seo, $heroFields)]),
                $f('faq', 'FAQ page', 'group', ['fields' => array_merge($seo, $heroFields)]),
                $f('contact', 'Contact page', 'group', ['fields' => $formPage]),
                $f('quote', 'Get a Quote page', 'group', ['fields' => $formPage]),
                $f('consultation', 'Book a Consultation page', 'group', ['fields' => $formPage]),
            ]],
        ],

        'faqs' => [
            'title' => 'FAQs',
            'icon'  => 'circle-help',
            'intro' => 'General questions shown on the homepage and FAQ page. They also feed the AI assistant and Google’s FAQ data. Service-specific FAQs are edited under Services.',
            'schema' => ['type' => 'repeater', 'item_label' => 'q', 'add_label' => 'Add question', 'fields' => [
                $f('q', 'Question'), $f('a', 'Answer', 'textarea', ['rows' => 3]),
            ]],
        ],

        'training' => [
            'title' => 'Training',
            'icon'  => 'graduation-cap',
            'intro' => 'Audiences, topics and program formats shown on the homepage and Training page. The Training page’s own text is under Services → Technology Training.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('audiences', 'Who we train', 'repeater', ['item_label' => 'label', 'fields' => [$f('icon', 'Icon', 'icon'), $f('label', 'Label'), $f('text', 'Text', 'textarea', ['rows' => 2])]]),
                $f('topics', 'Topics', 'repeater', ['item_label' => 'label', 'fields' => [$f('icon', 'Icon', 'icon'), $f('label', 'Label')]]),
                $f('formats', 'Program formats', 'repeater', ['item_label' => 'title', 'fields' => [$f('title', 'Title'), $f('text', 'Text', 'textarea', ['rows' => 2])]]),
            ]],
        ],

        'resources' => [
            'title' => 'Resources',
            'icon'  => 'book-open',
            'intro' => 'Short educational guides on the Resources page.',
            'schema' => ['type' => 'repeater', 'item_label' => 'title', 'add_label' => 'Add guide', 'fields' => [
                $f('title', 'Title'),
                $f('slug', 'Anchor', 'text', ['hint' => 'Lowercase words with dashes, used for links like /resources#spot-phishing.']),
                $f('icon', 'Icon', 'icon'),
                $f('tag', 'Category label'),
                $f('summary', 'Summary', 'textarea', ['rows' => 2]),
                $f('steps', 'Steps', 'lines', ['hint' => 'One step per line.']),
            ]],
        ],

        'testimonials' => [
            'title' => 'Testimonials',
            'icon'  => 'quote',
            'intro' => 'Only add real testimonials from real clients, with their permission. While this list is empty, labeled placeholder cards are shown.',
            'schema' => ['type' => 'repeater', 'item_label' => 'name', 'add_label' => 'Add testimonial', 'fields' => [
                $f('quote', 'Testimonial', 'textarea', ['rows' => 3]),
                $f('name', 'Client name'),
                $f('role', 'Role and company', 'text', ['hint' => 'e.g. Owner, Bright Bakery']),
                $f('service', 'Service (optional)'),
            ]],
        ],

        'images' => [
            'title' => 'Photos',
            'icon'  => 'monitor-check',
            'intro' => 'Photos used across the site. Upload your own image, or use an Unsplash photo ID. Always write alt text that describes the photo.',
            'schema' => ['type' => 'group', 'fields' => array_map(
                static fn (string $key) => $f($key, (image_usage()[$key] ?? ucfirst($key) . ' photo'), 'group', ['fields' => [
                    $f('file', 'Uploaded image', 'image', ['hint' => 'Takes priority over the Unsplash ID.']),
                    $f('id', 'Unsplash photo ID', 'text', ['hint' => 'The part after "photo-" in an images.unsplash.com link.']),
                    $f('alt', 'Alt text (describe the photo)'),
                ]]),
                array_keys(content_default('images') + content('images'))
            )],
        ],

        'blog' => [
            'title' => 'Blog settings',
            'icon'  => 'book-open',
            'intro' => 'Blog page text, categories and the homepage "Latest Insights" section. Write posts in Blog posts.',
            'schema' => ['type' => 'group', 'fields' => [
                ...$seo,
                $f('eyebrow', 'Small label'),
                $f('heading', 'Blog page heading (H1)'),
                $f('intro', 'Blog page intro', 'textarea', ['rows' => 2]),
                $f('show_on_home', 'Show latest posts on the homepage', 'bool'),
                $f('home_heading', 'Homepage section heading'),
                $f('home_intro', 'Homepage section intro'),
                $f('posts_per_page', 'Posts per page', 'text'),
                $f('default_author', 'Default author name'),
                $f('categories', 'Categories', 'repeater', ['item_label' => 'name', 'add_label' => 'Add category', 'fields' => [
                    $f('name', 'Name'),
                    $f('slug', 'Slug', 'text', ['hint' => 'Lowercase with dashes, e.g. cybersecurity. Changing it un-assigns existing posts.']),
                    $f('icon', 'Icon', 'icon'),
                ]]),
                $f('cta_heading', 'Call-to-action heading (end of each post)'),
                $f('cta_text', 'Call-to-action text', 'textarea', ['rows' => 2]),
            ]],
        ],

        'news' => [
            'title' => 'News sources',
            'icon'  => 'wifi',
            'intro' => 'The RSS/Atom feeds and bookmark links shown on the Tech & security news page.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('feeds', 'News feeds', 'repeater', ['item_label' => 'name', 'add_label' => 'Add feed', 'hint' => 'Most news sites publish an RSS feed. Paste its address (often ending in /feed or .xml).', 'fields' => [
                    $f('name', 'Source name'),
                    $f('url', 'Feed address (RSS or Atom)', 'url'),
                    $f('topic', 'Topic', 'select', ['options' => ['cybersecurity' => 'Cybersecurity', 'advisories' => 'Security advisories', 'technology' => 'Technology']]),
                ]]),
                $f('links', 'Go-to resource links', 'repeater', ['item_label' => 'label', 'add_label' => 'Add link', 'fields' => [
                    $f('label', 'Name'), $f('url', 'Address', 'url'), $f('note', 'Note (optional)'),
                ]]),
            ]],
        ],

        'legal' => [
            'title' => 'Legal pages',
            'icon'  => 'shield-check',
            'intro' => 'Privacy Policy and Terms of Service. Have both reviewed by a qualified professional.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('privacy', 'Privacy Policy', 'group', ['fields' => array_merge($seo, [$f('updated', 'Last updated (e.g. January 5, 2027)'), $f('body', 'Policy text', 'richtext', ['rows' => 24])])]),
                $f('terms', 'Terms of Service', 'group', ['fields' => array_merge($seo, [$f('updated', 'Last updated'), $f('governing_state', 'Governing state', 'text', ['hint' => 'Replaces {state} in the text.']), $f('body', 'Terms text', 'richtext', ['rows' => 24])])]),
            ]],
        ],

        'settings' => [
            'title' => 'Settings',
            'icon'  => 'wrench',
            'intro' => 'Contact details, social links, founder profile, form email delivery and the AI assistant.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('site_theme', 'Website color theme', 'select', ['options' => ['dark' => 'Dark premium (deep navy)', 'light' => 'Light (white)'], 'hint' => 'Changes the background and colors across the whole website.']),
                $f('contact_email', 'Public contact email', 'email', ['hint' => 'Shown in the footer and used in messages. Leave blank to hide.']),
                $f('contact_phone', 'Public phone number', 'text', ['hint' => 'Leave blank to hide.']),
                $f('booking_url', 'Online scheduling link', 'url', ['hint' => 'Calendly, Microsoft Bookings, etc. Adds a "pick a time" button to Book a Consultation.']),
                $f('social', 'Social media', 'group', ['fields' => [
                    $f('LinkedIn', 'LinkedIn URL', 'url'), $f('Facebook', 'Facebook URL', 'url'), $f('Instagram', 'Instagram URL', 'url'),
                    $f('X', 'X (Twitter) URL', 'url'), $f('YouTube', 'YouTube URL', 'url'),
                ]]),
                $f('founder', 'Founder profile', 'group', ['fields' => [
                    $f('name', 'Name'), $f('title', 'Title'), $f('bio', 'Short bio', 'textarea', ['rows' => 4]), $f('photo', 'Photo', 'image'),
                ]]),
                $f('forms', 'Form email delivery', 'group', ['hint' => 'Every submission is always saved in Messages. Turn this on to also receive them by email.', 'fields' => [
                    $f('delivery', 'Email delivery', 'select', ['options' => ['none' => 'Off (Messages inbox only)', 'mail' => 'On (send with the server’s mail function)']]),
                    $f('to', 'Send submissions to', 'email'),
                    $f('from', 'Send from address', 'email', ['hint' => 'Use an address on your own domain, e.g. no-reply@enomadigitaltech.com.']),
                ]]),
                $f('ai', 'AI assistant', 'group', ['fields' => [
                    $f('api_key', 'Anthropic API key', 'password', ['hint' => 'Stored privately on the server and never sent to browsers. Without a key, the assistant runs in guided mode using your site content.']),
                    $f('model', 'Model', 'text'),
                    $f('rate_limit', 'Messages per visitor per hour', 'text'),
                ]]),
                $f('setup_notices', 'Show yellow setup notes on the site', 'bool'),
                $f('base_url', 'Website address', 'url', ['hint' => 'Used for canonical links and the sitemap. No trailing slash.']),
            ]],
        ],
    ];
}

/** Current data for a section (what the editor starts from). */
function admin_section_data(string $section): array
{
    if ($section === 'settings') {
        $c = $GLOBALS['config'];
        $c['ai']['api_key'] = '';
        return $c;
    }
    $data = content($section);
    if ($section === 'services') {
        $list = [];
        foreach ($data as $slug => $svc) {
            $list[] = ['slug' => $slug] + $svc;
        }
        return $list;
    }
    return $data;
}

function admin_section_file(string $section): string
{
    return $section === 'settings' ? storage_dir('content') . '/settings.json' : content_override_file($section);
}

/** Where each photo key is used, for the Photos editor. */
function image_usage(): array
{
    return [
        'hero'       => 'Homepage hero (top of the homepage)',
        'cyber'      => 'Homepage cybersecurity section + Cybersecurity page',
        'webdev'     => 'Web Development page',
        'itsupport'  => 'IT Support page',
        'training'   => 'Homepage training section + Training page',
        'cloud'      => 'Cloud & Digital Technology page',
        'consulting' => 'Technology Consulting page',
        'team'       => 'About page',
        'services'   => 'Services page',
    ];
}
