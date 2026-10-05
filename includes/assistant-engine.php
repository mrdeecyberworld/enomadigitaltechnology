<?php
/**
 * Enoma AI Assistant engine.
 *
 * Two modes:
 *  - "ai":     Claude API, called from the server with an API key from config/env.
 *  - "guided": a local rules-based helper used when no key is configured or the
 *              API is unavailable. It only uses approved website content.
 *
 * Both modes share the same safety screen and knowledge base, so the assistant
 * never claims to be human, never invents company facts, declines professional
 * legal/medical/financial advice and refuses offensive-security requests.
 */

declare(strict_types=1);

/** Approved knowledge the assistant may use, built from site content files. */
function assistant_knowledge(): string
{
    $lines = [];
    $lines[] = 'Company: ' . site('name') . ' (short name: ' . site('short_name') . ', website: ' . site('domain') . ').';
    $lines[] = 'Tagline: ' . site('tagline') . ' ' . site('statement');
    $lines[] = 'Service area: ' . site('service_area') . ' The company does not list a physical office.';
    $lines[] = 'Audience: small businesses, startups, entrepreneurs, professionals, nonprofits, schools, families, older adults, students and individuals.';
    $lines[] = '';
    $lines[] = 'SERVICES (use these exact names and paths):';
    foreach (services() as $slug => $s) {
        $lines[] = '- ' . $s['name'] . ' (' . service_path($slug) . '): ' . $s['summary'] . ' Includes: ' . implode(', ', $s['includes']) . '.';
    }
    $lines[] = '';
    $lines[] = 'TRAINING AUDIENCES: ' . implode(', ', array_column(content('training')['audiences'], 'label')) . '.';
    $lines[] = 'TRAINING TOPICS: ' . implode(', ', array_column(content('training')['topics'], 'label')) . '.';
    $lines[] = '';
    $lines[] = 'PAGES: Book a Consultation (/book-a-consultation), Get a Quote (/get-a-quote), Contact (/contact), Resources (/resources), FAQ (/faq), About (/about).';
    $lines[] = 'Contact email: ' . (cfg('contact_email') ?: 'not published; direct people to /contact') . '. Phone: ' . (cfg('contact_phone') ?: 'not published') . '.';
    $lines[] = '';
    $lines[] = 'FAQ:';
    foreach (content('faqs') as $f) {
        $lines[] = 'Q: ' . $f['q'] . ' A: ' . $f['a'];
    }
    foreach (services() as $s) {
        foreach ($s['faqs'] ?? [] as $f) {
            $lines[] = 'Q: ' . $f['q'] . ' A: ' . $f['a'];
        }
    }
    return implode("\n", $lines);
}

function assistant_system_prompt(): string
{
    return <<<PROMPT
You are the Enoma AI Assistant on the website of Enoma Digital Technologies, a U.S.-based technology services company that serves clients worldwide. You help visitors understand their technology needs and find the right Enoma service.

How to respond:
- You are an automated AI assistant. If asked, say so plainly. Never claim or imply that you are a human or a staff member.
- Use only the approved company information below for facts about Enoma Digital Technologies. Do not invent prices, timelines, guarantees, team members, years of experience, certifications, clients, statistics, locations or policies. If something is not covered, say you don't have that detail and suggest booking a consultation or using the contact form.
- When the need is unclear, ask one short clarifying question.
- Recommend the most relevant service(s) by their exact names, briefly explain why, and suggest a next step (Book a Consultation, Get a Quote or Contact).
- You may give general, educational technology and cybersecurity guidance (for example: using unique passwords, enabling multi-factor authentication, recognizing phishing, keeping software updated, backing up data). Recommend professional help for anything specific or serious.
- Do not provide legal, medical or financial advice. Briefly decline and suggest a qualified professional.
- Security help is defensive only. Refuse requests to access accounts or systems without permission, crack passwords, write malware, phish, evade security, surveil people, or perform any offensive or harmful activity. Offer defensive alternatives instead.
- If someone may be in an active security incident, give calm, general first steps and recommend contacting Enoma or an appropriate professional promptly.
- Keep replies short and friendly: usually 2 to 5 sentences, plain text, no markdown headings or tables. Simple "- " bullet lines are fine.
- Never ask for passwords, payment details or sensitive personal information.

Approved company information:
PROMPT . "\n" . assistant_knowledge();
}

/** Safety screen shared by both modes. Returns a response array or null. */
function assistant_safety_screen(string $text): ?array
{
    $t = mb_strtolower($text);

    $offensive = '/\b(hack(ing)? into|break into|crack(ing)? (a |the |my |his |her |their |someone\'?s )?(password|wifi|wi-fi|account)|brute[- ]?force|keylogger|ddos|dos attack|botnet|write (a |some )?(malware|virus|ransomware|exploit)|make (a |some )?(malware|virus|ransomware)|phishing (page|kit|email) (to|for) |steal (password|credential|data|account)|bypass (2fa|mfa|security|authentication|antivirus)|spy on|track (my |someone\'?s )?(partner|spouse|wife|husband|girlfriend|boyfriend)|access (someone|somebody|another person|my (ex|partner|spouse|wife|husband))\'?s? (account|phone|email)|sql injection (on|against)|exploit (a |the )?(website|server|system) (i|that i) don\'?t own)/u';
    if (preg_match($offensive, $t)) {
        return [
            'reply' => "I can't help with accessing systems or accounts without permission, or with anything designed to cause harm. I'm happy to help on the defensive side, like securing your own accounts, setting up multi-factor authentication, recognizing phishing or getting cybersecurity awareness training.",
            'links' => [['label' => 'Cybersecurity services', 'url' => '/cybersecurity'], ['label' => 'Cybersecurity training', 'url' => '/training']],
            'suggestions' => ['How can I protect my accounts?', 'Do you offer security training?'],
        ];
    }

    $professional = '/\b(lawsuit|sue |legal advice|my lawyer|attorney|is it legal|tax(es)? (advice|return|deduction)|invest(ment)? advice|should i invest|stock|crypto(currency)? (to buy|investment)|medical|diagnos|symptom|medication|prescription|therapy)\b/u';
    if (preg_match($professional, $t)) {
        return [
            'reply' => "I'm not able to give legal, medical or financial advice. For that, please speak with a qualified professional. If your question involves technology, like protecting sensitive records or choosing secure tools, I'm glad to point you to the right Enoma service.",
            'links' => [['label' => 'Technology consulting', 'url' => '/technology-consulting']],
            'suggestions' => ['How do I keep client data secure?', 'I need technology advice'],
        ];
    }

    return null;
}

/**
 * Guided mode: rules-based responses built only from approved content.
 * @param array<int, array{role:string, content:string}> $messages
 */
function assistant_guided(array $messages): array
{
    $last = end($messages)['content'];
    $t = ' ' . mb_strtolower($last) . ' ';
    $has = static fn (string $pattern): bool => (bool) preg_match('/' . $pattern . '/u', $t);

    if ($has('\b(are you|is this) (a )?(human|real|person|bot|robot|ai)\b|\breal person\b|\btalk to (a )?(human|person|someone)\b')) {
        return [
            'reply' => "I'm an automated AI assistant, not a human. To talk with a person from Enoma Digital Technologies, book a consultation or send a message through the contact form.",
            'links' => [['label' => 'Book a Consultation', 'url' => '/book-a-consultation'], ['label' => 'Contact', 'url' => '/contact']],
            'suggestions' => [],
        ];
    }

    if ($has('\b(price|pricing|cost|how much|rate|rates|fee|fees|budget|quote|estimate)\b')) {
        return [
            'reply' => "Pricing depends on the scope of what you need, so I don't want to guess. The quickest way to get an accurate number is to request a quote with a few details about your project, or book a consultation to talk it through.",
            'links' => [['label' => 'Get a Quote', 'url' => '/get-a-quote'], ['label' => 'Book a Consultation', 'url' => '/book-a-consultation']],
            'suggestions' => ['What services do you offer?'],
        ];
    }

    if ($has('\b(hacked|compromised|breach(ed)?|someone (got|logged) into|clicked (a|on a|the) (link|attachment)|ransomware|scammed|locked out)\b')) {
        return [
            'reply' => "I'm sorry you're dealing with that. Some general first steps:\n- From a device you trust, change the password on the affected account and any account that used the same password.\n- Turn on multi-factor authentication and check the account's recovery email and phone number.\n- If money or banking is involved, contact your bank directly using a number you already trust.\n- Don't pay or reply to anyone demanding payment.\nFor hands-on help, contact Enoma Digital Technologies as soon as you can.",
            'links' => [['label' => 'Contact us now', 'url' => '/contact'], ['label' => 'Cybersecurity services', 'url' => '/cybersecurity']],
            'suggestions' => ['How do I recognize phishing?'],
        ];
    }

    if ($has('\b(where are you|located|location|office|which states?|united states|u\.s\.|usa|remote|near me|in my area|worldwide|international|countr(y|ies)|abroad|outside the us|europe|africa|canada|uk)\b')) {
        return [
            'reply' => 'Enoma Digital Technologies is based in the United States and works with clients worldwide. Services are delivered remotely, and sessions are scheduled to suit your time zone.',
            'links' => [['label' => 'Our services', 'url' => '/services']],
            'suggestions' => ['Do you provide remote IT support?'],
        ];
    }

    if ($has('\b(email address|phone|call you|contact you|reach you|get in touch|hours)\b')) {
        $email = cfg('contact_email');
        return [
            'reply' => 'The best way to reach Enoma Digital Technologies is through the contact form' . ($email ? ' or by email at ' . $email : '') . '. If you would like to talk through your needs, you can also request a consultation.',
            'links' => [['label' => 'Contact', 'url' => '/contact'], ['label' => 'Book a Consultation', 'url' => '/book-a-consultation']],
            'suggestions' => [],
        ];
    }

    if ($has('\b(book|schedule|consultation|appointment|meeting|call)\b') && !$has('\b(what happens|what is)\b')) {
        return [
            'reply' => "Great, a consultation is the easiest way to get started. Use the Book a Consultation page to request a time and share a little about what you need.",
            'links' => [['label' => 'Book a Consultation', 'url' => '/book-a-consultation']],
            'suggestions' => [],
        ];
    }

    if ($has('^\s*(hi|hello|hey|good (morning|afternoon|evening)|yo)\b[\s!.,]*$')) {
        return [
            'reply' => "Hello! What are you hoping to get done? For example: building a website, fixing a technology problem, improving your security, training a team, or setting up cloud tools.",
            'links' => [],
            'suggestions' => ['I need a website', 'I need IT support', 'Improve my cybersecurity', 'Training for my team'],
        ];
    }

    // Service intent scoring. Earlier user messages add context at lower weight.
    $keywords = [
        'web-development' => ['website', 'web site', 'webpage', 'web page', 'landing page', 'portfolio', 'domain', 'hosting', 'seo', 'search engine', 'redesign', 'wordpress', 'online presence', 'web design', 'site'],
        'cybersecurity'   => ['secur', 'protect', 'phishing', 'scam', 'password', 'mfa', '2fa', 'two-factor', 'multi-factor', 'virus', 'malware', 'privacy', 'safe online', 'breach', 'hacker', 'cyber'],
        'it-support'      => ['slow', 'computer', 'laptop', 'pc ', 'mac ', 'printer', 'wifi', 'wi-fi', 'internet', 'network', 'router', 'install', 'software', 'error', 'crash', 'broken', 'not working', 'fix', 'device', 'troubleshoot', 'tech support', 'it support', 'email setup', 'set up email'],
        'training'        => ['training', 'train ', 'learn', 'teach', 'class', 'workshop', 'course', 'student', 'school', 'senior', 'older', 'parent', 'grandparent', 'kids', 'children', 'youth', 'staff awareness', 'education', 'digital literacy'],
        'cloud-services'  => ['cloud', 'backup', 'back up', 'microsoft 365', 'office 365', 'm365', 'google workspace', 'g suite', 'gmail', 'onedrive', 'google drive', 'dropbox', 'storage', 'workflow', 'automat', 'sharepoint', 'teams'],
        'technology-consulting' => ['consult', 'advice', 'advise', 'plan', 'strategy', 'recommend', 'which should', 'startup', 'start a business', 'starting a business', 'new business', 'digital transformation', 'choose', 'guidance', 'not sure'],
    ];
    $scores = array_fill_keys(array_keys($keywords), 0.0);
    $history = array_filter($messages, static fn ($m) => $m['role'] === 'user');
    $count = count($history);
    $i = 0;
    foreach ($history as $m) {
        $weight = (++$i === $count) ? 1.0 : 0.35;
        $text = ' ' . mb_strtolower($m['content']) . ' ';
        foreach ($keywords as $slug => $words) {
            foreach ($words as $w) {
                if (preg_match('/\\b' . preg_quote($w, '/') . '/u', $text)) {
                    $scores[$slug] += $weight;
                }
            }
        }
    }
    arsort($scores);
    $ranked = array_keys(array_filter($scores, static fn ($s) => $s >= 1.0));

    // FAQ match when the question reads like a question about Enoma.
    $faq = assistant_match_faq($last);
    if ($faq && (!$ranked || $faq['score'] >= 0.6)) {
        return [
            'reply' => $faq['a'],
            'links' => assistant_links_for($ranked ? [$ranked[0]] : []),
            'suggestions' => ['Book a consultation', 'What services do you offer?'],
        ];
    }

    if ($has('\b(what (services|do you do|can you do|do you offer)|services do you|what does enoma)\b')) {
        $names = array_map(static fn ($s) => $s['name'], services());
        return [
            'reply' => 'Enoma Digital Technologies offers ' . implode(', ', array_slice($names, 0, -1)) . ' and ' . end($names) . ". Tell me a bit about what you're working on and I'll suggest where to start.",
            'links' => [['label' => 'All services', 'url' => '/services']],
            'suggestions' => ['I need a website', 'My computer has a problem', 'Improve my cybersecurity'],
        ];
    }

    if (!$ranked) {
        return [
            'reply' => "Thanks for sharing. To point you in the right direction, which of these is closest to what you need: a website, help with a technology problem, better security, training, cloud tools like Microsoft 365 or backups, or general technology advice?",
            'links' => [],
            'suggestions' => ['A website', 'A technology problem', 'Better security', 'Training', 'Cloud tools', 'Technology advice'],
        ];
    }

    $primary = $ranked[0];
    $svc = service($primary);
    $reply = assistant_service_blurb($primary, $t);
    $secondary = $ranked[1] ?? null;
    if ($secondary) {
        $reply .= ' ' . service($secondary)['name'] . ' may also be relevant: ' . lcfirst(service($secondary)['summary']);
    }
    $reply .= ' The best next step is a consultation so we can understand your situation.';

    return [
        'reply' => $reply,
        'links' => assistant_links_for(array_filter([$primary, $secondary])),
        'suggestions' => assistant_followups($primary),
    ];
}

function assistant_service_blurb(string $slug, string $t): string
{
    $svc = service($slug);
    $intro = match ($slug) {
        'web-development' => str_contains($t, 'redesign') || str_contains($t, 'update')
            ? 'It sounds like you want to improve an existing website. Our Web Development service includes website redesign and maintenance.'
            : 'It sounds like Web Development is a good fit.',
        'cybersecurity' => 'It sounds like Cybersecurity is a good fit.',
        'it-support' => 'It sounds like IT Support can help.',
        'training' => 'Technology Training sounds like a good fit.',
        'cloud-services' => 'Our Cloud & Digital Technology service sounds like a good fit.',
        'technology-consulting' => 'Technology Consulting sounds like a good place to start.',
        default => $svc['name'] . ' may be a good fit.',
    };
    $items = array_map(static fn (string $i): string => preg_match('/^[A-Z][a-z]/', $i) ? lcfirst($i) : $i, array_slice($svc['includes'], 0, 4));
    return $intro . ' ' . $svc['summary'] . ' It covers ' . implode(', ', $items) . ', and more.';
}

function assistant_followups(string $slug): array
{
    return match ($slug) {
        'web-development' => ['How long does a website take?', 'Can you redesign my site?', 'Book a consultation'],
        'cybersecurity' => ['What is a security assessment?', 'How do I spot phishing?', 'Book a consultation'],
        'it-support' => ['Do you provide remote support?', 'Book a consultation'],
        'training' => ['Is training online?', 'Can training be tailored?', 'Book a consultation'],
        'cloud-services' => ['Microsoft 365 or Google Workspace?', 'Book a consultation'],
        default => ['What happens in a consultation?', 'Book a consultation'],
    };
}

/** Service page links plus the consultation CTA. */
function assistant_links_for(array $slugs): array
{
    $links = [];
    foreach ($slugs as $slug) {
        if ($svc = service($slug)) {
            $links[] = ['label' => $svc['name'], 'url' => service_path($slug)];
        }
    }
    $links[] = ['label' => 'Book a Consultation', 'url' => '/book-a-consultation'];
    return $links;
}

/** Find the best-matching approved FAQ by word overlap. */
function assistant_match_faq(string $question): ?array
{
    $stop = ['a', 'an', 'the', 'do', 'does', 'you', 'your', 'i', 'my', 'me', 'can', 'is', 'are', 'for', 'to', 'of', 'and', 'or', 'in', 'on', 'with', 'what', 'how', 'we', 'our', 'it', 'enoma', 'digital', 'technologies', 'provide', 'offer'];
    $tokens = static function (string $s) use ($stop): array {
        $words = preg_split('/[^a-z0-9]+/', mb_strtolower($s), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_diff($words, $stop);
        // Minimal stemming so "websites"/"businesses" match "website"/"business".
        $words = array_map(static fn (string $w): string => strlen($w) > 4 ? preg_replace(['/sses$/', '/([^s])s$/'], ['ss', '$1'], $w) : $w, $words);
        return array_values(array_unique($words));
    };
    $q = $tokens($question);
    if (count($q) < 2) {
        return null;
    }
    $all = content('faqs');
    foreach (services() as $s) {
        $all = array_merge($all, $s['faqs'] ?? []);
    }
    $best = null;
    foreach ($all as $f) {
        $fq = $tokens($f['q']);
        if (!$fq) {
            continue;
        }
        $overlap = count(array_intersect($q, $fq));
        $score = $overlap / max(count($fq), 1);
        if ($overlap >= 2 && ($best === null || $score > $best['score'])) {
            $best = $f + ['score' => $score];
        }
    }
    return ($best && $best['score'] >= 0.4) ? $best : null;
}

/**
 * AI mode: call the Claude Messages API over HTTPS (raw HTTP keeps the site
 * dependency-free for shared hosting). Returns null on any failure so the
 * caller can fall back to guided mode.
 */
function assistant_claude(array $messages): ?array
{
    $key = (string) cfg('ai.api_key');
    if ($key === '' || !function_exists('curl_init')) {
        return null;
    }

    $payload = [
        'model'         => cfg('ai.model', 'claude-opus-5-5'),
        'max_tokens'    => (int) cfg('ai.max_tokens', 2048),
        'system'        => assistant_system_prompt(),
        'messages'      => $messages,
        'output_config' => ['effort' => cfg('ai.effort', 'low')],
        // If a safety classifier declines, retry server-side on Anthropic's recommended fallback model.
        'fallbacks'     => 'default',
    ];

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT        => 45,
        CURLOPT_HTTPHEADER     => [
            'content-type: application/json',
            'x-api-key: ' . $key,
            'anthropic-version: 2023-06-01',
            'anthropic-beta: server-side-fallback-2026-07-01',
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($raw === false || $status !== 200) {
        error_log('Enoma assistant: Claude API request failed with HTTP ' . $status);
        return null;
    }
    $data = json_decode((string) $raw, true);
    if (!is_array($data) || ($data['stop_reason'] ?? '') === 'refusal') {
        return null;
    }

    $text = '';
    foreach ($data['content'] ?? [] as $block) {
        if (($block['type'] ?? '') === 'text') {
            $text .= $block['text'];
        }
    }
    $text = trim($text);
    if ($text === '') {
        return null;
    }

    // Attach links for any services the reply mentions.
    $mentioned = [];
    foreach (services() as $slug => $svc) {
        if (stripos($text, $svc['name']) !== false || stripos($text, service_path($slug)) !== false) {
            $mentioned[] = $slug;
        }
    }

    return [
        'reply' => $text,
        'links' => assistant_links_for(array_slice($mentioned, 0, 3)),
        'suggestions' => [],
    ];
}
