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
                $f('service_area', 'Service area statement', 'text', ['hint' => 'Shown in the footer. Keep it accurate: e.g. remote services for clients worldwide']),
                $f('nav', 'Main menu (the bar at the top)', 'repeater', ['item_label' => 'label', 'add_label' => 'Add a menu link', 'hint' => 'Add, remove, hide or reorder the links in the top menu. Use the arrows to change the order.', 'fields' => [
                    $f('label', 'Menu label', 'text', ['hint' => 'The word shown in the menu, e.g. About.']),
                    $f('page', 'Page', 'select', ['options' => menu_page_options(), 'hint' => 'Choose a page on your website, or “Custom link” to type any address below.']),
                    $f('path', 'Custom link (optional)', 'text', ['hint' => 'Only used with “Custom link”, e.g. /about or https://example.com']),
                    $f('children', 'Dropdown', 'select', ['options' => ['' => 'None', 'services' => 'Show services dropdown']]),
                    $f('hide', 'Hide from the menu (keep it here for later)', 'bool'),
                ]]),
                $f('cta_primary', 'Main button (header, right side)', 'group', ['fields' => [$f('label', 'Label'), $f('path', 'Link'), $f('hide', 'Hide this button in the header', 'bool')]]),
                $f('cta_secondary', 'Secondary button (header)', 'group', ['fields' => [$f('label', 'Label'), $f('path', 'Link')]]),
                $f('footer_company', 'Footer "Company" links', 'repeater', ['item_label' => 'label', 'add_label' => 'Add a footer link', 'fields' => [
                    $f('label', 'Label'),
                    $f('page', 'Page', 'select', ['options' => menu_page_options()]),
                    $f('path', 'Custom link (optional)', 'text', ['hint' => 'Only used with “Custom link”.']),
                    $f('hide', 'Hide this link', 'bool'),
                ]]),
            ]],
        ],

        'home' => [
            'title' => 'Homepage',
            'icon'  => 'layout-template',
            'intro' => 'Hero, trust bar, Why Enoma, cybersecurity feature, process and about sections. Choose which sections appear under Sections to show: a shorter homepage is easier to read.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('sections', 'Sections to show', 'group', ['fields' => [
                    $f('trust', 'Trust bar (service links under the hero)', 'bool'),
                    $f('why', 'Why Enoma', 'bool'),
                    $f('security', 'Cybersecurity feature', 'bool'),
                    $f('assistant', 'AI assistant and service finder', 'bool'),
                    $f('training', 'Training feature', 'bool'),
                    $f('process', 'How it works (process steps)', 'bool'),
                    $f('about', 'About Enoma', 'bool'),
                    $f('faq', 'FAQ', 'bool'),
                    $f('showcase', 'Slider near the bottom of the page', 'bool'),
                ]]),
                $f('faq_count', 'Number of FAQ questions on the homepage', 'text', ['hint' => 'The rest are on the FAQ page.']),
                $f('hero', 'Hero', 'group', ['fields' => [
                    $f('eyebrow', 'Small label'),
                    $f('headline', 'Headline (H1)'),
                    $f('text', 'Supporting text', 'textarea', ['rows' => 3]),
                    $f('points', 'Check points under buttons', 'lines'),
                ]]),
                $f('showcase', 'Slider', 'group', ['fields' => [
                    $f('eyebrow', 'Small label'), $f('heading', 'Heading'), $f('text', 'Text', 'textarea', ['rows' => 2]),
                    $f('slides', 'Slides', 'repeater', ['item_label' => 'title', 'add_label' => 'Add slide', 'fields' => [
                        $f('photo', 'Photo', 'image', ['hint' => 'Choose an uploaded image, or a photo name from Photos (e.g. webdev, cyber, itsupport).']),
                        $f('icon', 'Icon', 'icon'), $f('tag', 'Small label'), $f('title', 'Title'), $f('text', 'Text', 'textarea', ['rows' => 2]),
                        $f('label', 'Button text'), $f('path', 'Button link'),
                    ]]),
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
                $f('_original', '', 'hidden'),
                $f('slug', 'URL slug', 'text', ['hint' => 'Lowercase letters, numbers and dashes. The page lives at /slug. Changing it changes the page address.', 'required' => true]),
                $f('name', 'Service name', 'text', ['required' => true]),
                $f('short_label', 'Short name (footer)', 'text', ['hint' => 'Optional.']),
                $f('nav_label', 'Menu name'),
                $f('icon', 'Icon', 'icon'),
                $f('layout', 'Page design', 'select', ['options' => ['' => 'Standard service page', 'training' => 'Training page (audiences, topics, programs)']]),
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
                $f('_original', '', 'hidden'),
                $f('title', 'Title'),
                $f('slug', 'Page address', 'text', ['hint' => 'Each guide has its own page at /resources/this-address. Lowercase words with dashes.']),
                $f('icon', 'Icon', 'icon'),
                $f('tag', 'Category label'),
                $f('summary', 'Summary', 'textarea', ['rows' => 2]),
                $f('steps', 'Steps', 'lines', ['hint' => 'One step per line.']),
                $f('body', 'Extra introduction (optional)', 'richtext', ['rows' => 6]),
                $f('meta_title', 'SEO title (optional)', 'text', ['counter' => 60]),
                $f('meta_description', 'SEO description (optional)', 'textarea', ['rows' => 2, 'counter' => 160]),
            ]],
        ],

        'profile' => [
            'title' => 'About me',
            'icon'  => 'user-round',
            'intro' => 'Your story, what inspires you and your experience from your CV, shown on the About page. Your name, title, short bio and photo are in Settings → Founder. Each part appears on the website once you fill it in.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('story_heading', 'Story heading'),
                $f('story', 'My story', 'richtext', ['rows' => 8, 'hint' => 'Your background in your own words: where you started, what you have done and why you started Enoma Digital Technologies.']),
                $f('inspiration_heading', 'Inspiration heading'),
                $f('inspiration', 'What inspires me', 'richtext', ['rows' => 6, 'hint' => 'The people, moments and beliefs that drive your work.']),
                $f('quote', 'A favorite quote or personal motto (optional)', 'textarea', ['rows' => 2]),
                $f('quote_source', 'Who said it (optional)'),
                $f('experience', 'Experience', 'repeater', ['item_label' => 'role', 'add_label' => 'Add a role', 'fields' => [
                    $f('role', 'Job title'),
                    $f('organization', 'Organization'),
                    $f('period', 'Dates', 'text', ['hint' => 'e.g. 2019 – Present']),
                    $f('summary', 'What you did', 'textarea', ['rows' => 3]),
                    $f('highlights', 'Highlights (optional)', 'lines', ['hint' => 'One achievement per line.']),
                ]]),
                $f('education', 'Education', 'repeater', ['item_label' => 'qualification', 'add_label' => 'Add education', 'fields' => [
                    $f('qualification', 'Degree or qualification'),
                    $f('institution', 'School or institution'),
                    $f('year', 'Year (optional)'),
                ]]),
                $f('certifications', 'Certifications', 'lines', ['hint' => 'One per line, e.g. CompTIA Security+ (2023).']),
                $f('skills', 'Skills', 'lines', ['hint' => 'One per line, e.g. Network security.']),
            ]],
        ],

        'shop' => [
            'title' => 'Courses & Tools',
            'icon'  => 'shopping-bag',
            'intro' => 'Courses, software, templates and tools clients can buy. Each one gets its own page. Until you add a checkout link, customers place an order and you email them how to pay (see Orders); after payment you send a private download link. Upload the files customers receive in Product files. When your payments are ready, paste a Stripe Payment Link, PayPal, Gumroad, Lemon Squeezy or Payhip link and the Buy button goes there. The menu link appears once at least one product is published.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('eyebrow', 'Small label above the heading'),
                $f('heading', 'Shop page heading (H1)'),
                $f('intro', 'Shop page intro', 'textarea', ['rows' => 2]),
                $f('empty', 'Message while nothing is for sale yet', 'textarea', ['rows' => 2]),
                $f('checkout_note', 'Note under the Buy button', 'text'),
                $f('home_heading', 'Homepage section heading'),
                $f('home_intro', 'Homepage section intro'),
                ...$seo,
                $f('products', 'Products', 'repeater', ['item_label' => 'name', 'add_label' => 'Add a course or tool', 'fields' => [
                    $f('_original', '', 'hidden'),
                    $f('name', 'Name'),
                    $f('slug', 'Page address', 'text', ['hint' => 'Its page will be at /courses-and-tools/this-address. Lowercase words with dashes; leave blank to use the name.']),
                    $f('status', 'Visible on the website?', 'select', ['options' => ['published' => 'Published (visible)', 'draft' => 'Draft (only you can see it)']]),
                    $f('type', 'Type', 'select', ['options' => ['Course' => 'Course', 'Software' => 'Software', 'Tool' => 'Tool', 'Template' => 'Template', 'Guide' => 'Guide (e-book / PDF)', 'Bundle' => 'Bundle', 'Workshop' => 'Workshop']]),
                    $f('featured', 'Feature it (shown first and on the homepage)', 'bool'),
                    $f('price', 'Price', 'text', ['hint' => 'e.g. 49 or 49.99 (US dollars). Type 0 for free, or any text such as “Pay what you want”.']),
                    $f('price_note', 'Price note (optional)', 'text', ['hint' => 'e.g. One-time payment · Lifetime access']),
                    $f('image', 'Cover image', 'image'),
                    $f('summary', 'Short description', 'textarea', ['rows' => 2, 'hint' => 'One or two sentences for the product card.']),
                    $f('description', 'Full description', 'richtext', ['rows' => 8]),
                    $f('includes', 'What’s included', 'lines', ['hint' => 'One item per line, e.g. “6 video lessons (2 hours)”.']),
                    $f('format', 'Format and access (optional)', 'text', ['hint' => 'e.g. Online, self-paced · Instant download (PDF + Excel)']),
                    $f('file', 'What customers get after payment: option A, an uploaded file', 'select', ['options' => ['' => '— None —'] + array_combine(array_keys(product_files()), array_map(static fn ($n, $s) => $n . ' (' . format_bytes($s) . ')', array_keys(product_files()), product_files())), 'hint' => 'Upload it first in Upload courses & software. It stays private: customers only get it through a download link you create for their paid order. Leave as “None” if you use a link (option B) instead.']),
                    $f('download_url', 'What customers get after payment: option B, a download link', 'url', ['hint' => 'No upload needed: paste any link, e.g. Google Drive, Dropbox, OneDrive, MEGA, WeTransfer, YouTube (unlisted) or your course platform. Customers never see it on the website: after they pay, you send it from Orders (Create private link), which opens this link for them.']),
                    $f('buy_url', 'Checkout link (add later)', 'url', ['hint' => 'Leave empty for now: customers place an order and you email them how to pay. When ready, paste your Stripe Payment Link, PayPal, Gumroad, Lemon Squeezy or Payhip link here and the Buy button goes there.']),
                    $f('buy_label', 'Buy button text (optional)', 'text', ['hint' => 'Default: “Buy now” (or “Get it free” for free items).']),
                    $f('meta_title', 'SEO title (optional)', 'text', ['counter' => 60]),
                    $f('meta_description', 'SEO description (optional)', 'textarea', ['rows' => 2, 'counter' => 160]),
                ]]),
            ]],
        ],

        'testimonials' => [
            'title' => 'Testimonials',
            'icon'  => 'quote',
            'intro' => 'Only add real testimonials from real clients, with their permission. Clients can also send feedback on the /feedback page; approve it in Client feedback and it is added here automatically. While this list is empty, the section is hidden from visitors.',
            'schema' => ['type' => 'repeater', 'item_label' => 'name', 'add_label' => 'Add testimonial', 'fields' => [
                $f('quote', 'Testimonial', 'textarea', ['rows' => 3]),
                $f('name', 'Client name'),
                $f('role', 'Role and company', 'text', ['hint' => 'e.g. Owner, Bright Bakery']),
                $f('service', 'Service (optional)'),
                $f('rating', 'Star rating (optional)', 'select', ['options' => ['' => 'No stars', '5' => '★★★★★ 5', '4' => '★★★★ 4', '3' => '★★★ 3', '2' => '★★ 2', '1' => '★ 1']]),
            ]],
        ],

        'images' => [
            'title' => 'Photos',
            'icon'  => 'monitor-check',
            'intro' => 'Every photo on the site. For each one, upload your own image or paste a link to a photo on unsplash.com (free to use). Always write alt text that describes the photo.',
            'schema' => ['type' => 'group', 'fields' => array_map(
                static fn (string $key) => $f($key, (image_usage()[$key] ?? ucfirst($key) . ' photo'), 'group', ['fields' => [
                    $f('file', 'Uploaded image', 'image', ['hint' => 'Takes priority over the Unsplash ID.']),
                    $f('id', 'Unsplash photo (link or ID)', 'text', ['hint' => 'Find a photo you like on unsplash.com (free to use), copy the address from your browser and paste it here. Then click “Save photos to this website” above.']),
                    $f('alt', 'Alt text (describe the photo)'),
                ]]),
                array_keys(content_default('images') + content('images'))
            )],
        ],

        'routes' => [
            'title' => 'Page URLs',
            'icon'  => 'link-2',
            'intro' => 'Choose the web address of every page. When you change one, the old address automatically redirects to the new one and every link on the site updates. Service, guide and blog post addresses are set on each item.',
            'schema' => ['type' => 'group', 'fields' => array_map(
                static fn (string $key, string $label) => $f($key, $label, 'text', ['prefix' => '/', 'hint' => $key === 'blog' ? 'Blog posts live at /this-address/post-address.' : ($key === 'resources' ? 'Guides live at /this-address/guide-address.' : '')]),
                array_keys(PAGE_FILES),
                ['Services page', 'About page', 'Blog', 'Resources', 'FAQ page', 'Contact page', 'Get a Quote page', 'Book a Consultation page', 'Privacy Policy', 'Terms of Service', 'Photo credits', 'Courses & Tools (shop)', 'Client feedback page']
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
            'intro' => 'Contact details, social links, founder profile, scheduling link and the AI assistant. Email delivery is under Email; colors under Appearance.',
            'schema' => ['type' => 'group', 'fields' => [
                $f('contact_email', 'Public contact email', 'email', ['hint' => 'Shown in the footer and used in messages. Leave blank to hide.']),
                $f('contact_phone', 'Public phone number', 'text', ['hint' => 'Leave blank to hide.']),
                $f('booking_url', 'Online scheduling link', 'url', ['hint' => 'Calendly, Microsoft Bookings, etc. Adds a "pick a time" button to Book a Consultation.']),
                $f('social', 'Social media', 'group', ['fields' => [
                    $f('LinkedIn', 'LinkedIn URL', 'url'), $f('Facebook', 'Facebook URL', 'url'), $f('Instagram', 'Instagram URL', 'url'),
                    $f('X', 'X (Twitter) URL', 'url'), $f('YouTube', 'YouTube URL', 'url'),
                ]]),
                $f('freelance', 'Freelancer status', 'group', ['fields' => [
                    $f('show', 'Show that I am a freelancer (top bar and homepage badge)', 'bool'),
                    $f('status', 'Badge text', 'text', ['hint' => 'Shown with a green dot in the homepage hero and the header.']),
                    $f('bar_text', 'Top bar text', 'text'),
                    $f('link_label', 'Button text'), $f('link', 'Button link'),
                ]]),
                $f('founder', 'Founder profile', 'group', ['fields' => [
                    $f('name', 'Name'), $f('title', 'Title'), $f('bio', 'Short bio', 'textarea', ['rows' => 4]), $f('photo', 'Photo', 'image'),
                ]]),
                $f('ai', 'AI assistant', 'group', ['fields' => [
                    $f('api_key', 'Anthropic API key', 'password', ['is_set' => (string) cfg('ai.api_key') !== '', 'hint' => 'Stored privately on the server and never sent to browsers. Without a key, the assistant runs in guided mode using your site content.']),
                    $f('model', 'Model', 'text'),
                    $f('rate_limit', 'Messages per visitor per hour', 'text'),
                ]]),
                $f('adsense', 'Google AdSense (ads on your website)', 'group', ['fields' => [
                    $f('client', 'Publisher ID', 'text', ['hint' => 'Looks like ca-pub-1234567890123456. You can paste the whole AdSense code; only the ID is kept. Your ads.txt file is created automatically.']),
                    $f('placement', 'Where ads may appear', 'select', ['options' => ['all' => 'All pages (needed while Google reviews your site)', 'content' => 'Blog and guides only (recommended once approved)', 'off' => 'Off (no ads)']]),
                ]]),
                $f('seo', 'Search engines (Google Search Console)', 'group', ['fields' => [
                    $f('google_verification', 'Google Search Console verification code', 'text', ['hint' => 'In Search Console choose “URL prefix”, then the “HTML tag” method, and paste the whole tag or just the code here. Save, then click Verify in Search Console.']),
                    $f('bing_verification', 'Bing Webmaster Tools verification code (optional)', 'text', ['hint' => 'Bing also powers Yahoo and DuckDuckGo results. Paste the whole tag or just the code.']),
                    $f('force_https', 'Always use the secure address (https://)', 'bool', ['hint' => 'Turn on once https://enomadigitaltech.com opens with a padlock. Visitors using http:// are then sent to the secure address, which Google prefers.']),
                ]]),
                $f('setup_notices', 'Show me setup reminders on the website while I’m logged in', 'bool'),
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
            $list[] = ['slug' => $slug, '_original' => $slug] + $svc;
        }
        return $list;
    }
    if ($section === 'resources') {
        return array_map(static fn ($g) => ['_original' => $g['slug'] ?? ''] + $g, $data);
    }
    if ($section === 'shop') {
        $data['products'] = array_map(static fn ($p) => ['_original' => $p['slug'] ?? ''] + (array) $p, (array) ($data['products'] ?? []));
        return $data;
    }
    if ($section === 'routes') {
        return routes();
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
        'team'       => 'About page (our story section)',
        'services'   => 'Services page',
        'people'     => 'Homepage about section',
        'about'      => 'About page header',
        'blog'       => 'Blog page header',
        'resources'  => 'Resources page header',
        'post-mfa'      => 'Starter blog post: Multi-Factor Authentication',
        'post-phishing' => 'Starter blog post: Phishing signs',
        'post-website'  => 'Starter blog post: Website essentials',
        'post-backup'   => 'Starter blog post: 3-2-1 Backup Rule',
    ];
}
