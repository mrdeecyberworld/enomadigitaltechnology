<?php
/**
 * AI Technology Assistant and "Find the Right Service" tool.
 */

declare(strict_types=1);

function ai_assistant(): string
{
    $suggestions = [
        'I need a website for my small business',
        'How can I protect my accounts?',
        'Do you offer training for seniors?',
        'My computer is running slowly',
    ];
    ob_start(); ?>
    <div class="assistant reveal" data-assistant data-endpoint="/api/ai-assistant">
      <div class="assistant__header">
        <span class="assistant__avatar" aria-hidden="true"><?= icon('sparkles', 'icon') ?></span>
        <div>
          <p class="assistant__name">Enoma AI Assistant</p>
          <p class="assistant__meta"><span class="status-dot" aria-hidden="true"></span>Automated assistant, not a human</p>
        </div>
        <button type="button" class="assistant__reset" data-assistant-reset aria-label="Start a new conversation" hidden>
          <?= icon('refresh-cw', 'icon icon-sm') ?>
        </button>
      </div>

      <div class="assistant__log" role="log" aria-live="polite" aria-label="Conversation with the Enoma AI Assistant" data-assistant-log tabindex="0">
        <div class="msg msg--assistant">
          <p>Hi! I'm Enoma's AI assistant. Tell me what you're trying to accomplish or the technology problem you're facing, and I'll point you to the right service.</p>
        </div>
      </div>

      <div class="assistant__chips" data-assistant-chips>
        <?php foreach ($suggestions as $s): ?>
          <button type="button" class="chip" data-assistant-suggest><?= e($s) ?></button>
        <?php endforeach; ?>
      </div>

      <form class="assistant__form" data-assistant-form>
        <label for="assistant-input" class="sr-only">Describe what you need help with</label>
        <textarea id="assistant-input" name="message" rows="1" maxlength="1000" placeholder="Describe what you need help with…" required data-assistant-input></textarea>
        <button type="submit" class="assistant__send" aria-label="Send message" data-assistant-send>
          <?= icon('send', 'icon icon-sm') ?>
        </button>
      </form>
      <p class="assistant__disclaimer">
        General information only. Not legal, medical or financial advice. Please don't share passwords or sensitive personal data.
      </p>
      <noscript><p class="assistant__noscript">The assistant needs JavaScript. You can <a href="/services">browse our services</a> or <a href="/contact">contact us</a> instead.</p></noscript>
    </div>
    <?php
    return (string) ob_get_clean();
}

/** Service finder data: each need has a follow-up question; answers map to services. */
function service_finder_data(): array
{
    return [
        'website' => [
            'label' => 'I need a website', 'icon' => 'code-xml',
            'question' => 'Which best describes your website project?',
            'answers' => [
                ['label' => 'A brand-new website',            'primary' => 'web-development', 'also' => ['technology-consulting'], 'note' => 'We will plan the structure and content with you, then design and build a responsive site.'],
                ['label' => 'Redesign my existing website',   'primary' => 'web-development', 'also' => ['cybersecurity'],         'note' => 'A redesign is a good moment to improve messaging, speed, SEO basics and security.'],
                ['label' => 'Updates or ongoing maintenance', 'primary' => 'web-development', 'also' => ['cybersecurity'],         'note' => 'Website maintenance keeps content current and software patched.'],
                ['label' => 'A landing page for an offer',    'primary' => 'web-development', 'also' => ['technology-consulting'], 'note' => 'A focused landing page gives a campaign one clear goal.'],
            ],
        ],
        'it' => [
            'label' => 'I need IT support', 'icon' => 'headset',
            'question' => 'What kind of help do you need?',
            'answers' => [
                ['label' => 'A computer or device problem', 'primary' => 'it-support', 'also' => [],                 'note' => 'Most everyday computer problems can be diagnosed through a secure remote session.'],
                ['label' => 'Email or software setup',      'primary' => 'it-support', 'also' => ['cloud-services'], 'note' => 'We can set up email and applications across your devices.'],
                ['label' => 'Wi-Fi or network issues',      'primary' => 'it-support', 'also' => ['cybersecurity'],  'note' => 'Network troubleshooting is also a chance to check your router security.'],
                ['label' => 'Setting up new devices',       'primary' => 'it-support', 'also' => ['cybersecurity'],  'note' => 'New devices are easiest to secure properly from day one.'],
            ],
        ],
        'security' => [
            'label' => 'I want to improve my cybersecurity', 'icon' => 'shield-check',
            'question' => 'Who are you looking to protect?',
            'answers' => [
                ['label' => 'My business',                'primary' => 'cybersecurity', 'also' => ['training'],              'note' => 'A basic security assessment plus team awareness training is a strong starting point.'],
                ['label' => 'Myself or my family',        'primary' => 'cybersecurity', 'also' => ['training'],              'note' => 'Account security, MFA and phishing awareness protect what matters most.'],
                ['label' => 'A school or organization',   'primary' => 'cybersecurity', 'also' => ['training', 'technology-consulting'], 'note' => 'We can combine practical safeguards with education for staff and learners.'],
                ['label' => 'My website',                 'primary' => 'cybersecurity', 'also' => ['web-development'],       'note' => 'We review HTTPS, updates, user access, backups and hosting settings.'],
            ],
        ],
        'training' => [
            'label' => 'I need cybersecurity training', 'icon' => 'graduation-cap',
            'question' => 'Who is the training for?',
            'answers' => [
                ['label' => 'A business team',          'primary' => 'training', 'also' => ['cybersecurity'], 'note' => 'Cybersecurity awareness training helps every employee recognize and report threats.'],
                ['label' => 'Students or a school',     'primary' => 'training', 'also' => [],                'note' => 'Age-appropriate sessions on online safety and digital citizenship.'],
                ['label' => 'Adults or seniors',        'primary' => 'training', 'also' => ['it-support'],    'note' => 'Patient, practical sessions on scams, passwords and everyday technology.'],
                ['label' => 'Myself',                   'primary' => 'training', 'also' => ['cybersecurity'], 'note' => 'From beginner skills to cybersecurity fundamentals, at your pace.'],
            ],
        ],
        'cloud' => [
            'label' => 'I need cloud/digital technology help', 'icon' => 'cloud',
            'question' => 'What would you like to set up or improve?',
            'answers' => [
                ['label' => 'Email and collaboration (Microsoft 365 or Google Workspace)', 'primary' => 'cloud-services', 'also' => ['cybersecurity'],         'note' => 'We configure accounts, sharing and security settings properly from the start.'],
                ['label' => 'Backups',                         'primary' => 'cloud-services', 'also' => ['cybersecurity'],         'note' => 'Automatic, tested backups are one of the best protections against data loss.'],
                ['label' => 'File storage and sharing',        'primary' => 'cloud-services', 'also' => ['it-support'],            'note' => 'Organized folders and permissions keep the right files with the right people.'],
                ['label' => 'Automating a manual process',     'primary' => 'cloud-services', 'also' => ['technology-consulting'], 'note' => 'Simple digital workflows can save hours every week.'],
            ],
        ],
        'unsure' => [
            'label' => "I'm not sure", 'icon' => 'circle-help',
            'question' => 'Which of these sounds closest?',
            'answers' => [
                ['label' => "I'm starting or growing a business",   'primary' => 'technology-consulting', 'also' => ['web-development', 'cloud-services'], 'note' => 'A consultation helps you prioritize the technology that matters first.'],
                ['label' => 'Something is broken or not working',   'primary' => 'it-support',            'also' => [],                                     'note' => 'Tell us what is happening and we will help you get it working.'],
                ['label' => "I'm worried about security or scams",  'primary' => 'cybersecurity',         'also' => ['training'],                           'note' => 'We will help you understand the risk and take practical next steps.'],
                ['label' => 'I want to learn new technology skills', 'primary' => 'training',             'also' => [],                                     'note' => 'Training is available for all experience levels.'],
            ],
        ],
    ];
}

function service_finder(): string
{
    $data = service_finder_data();
    $svc = [];
    foreach (services() as $slug => $s) {
        $svc[$slug] = ['name' => $s['name'], 'summary' => $s['summary'], 'path' => service_path($slug), 'icon' => icon($s['icon'])];
    }
    ob_start(); ?>
    <div class="finder reveal" data-finder>
      <script type="application/json" data-finder-data><?= json_encode(['needs' => $data, 'services' => $svc], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ?></script>
      <div class="finder__progress" aria-hidden="true"><span data-finder-bar></span></div>

      <fieldset class="finder__step" data-finder-step="1">
        <legend class="finder__question" tabindex="-1">What do you need help with?</legend>
        <div class="finder__options">
          <?php foreach ($data as $key => $need): ?>
            <button type="button" class="finder__option" data-finder-need="<?= e($key) ?>">
              <span class="finder__option-icon"><?= icon($need['icon']) ?></span>
              <span><?= e($need['label']) ?></span>
              <?= icon('arrow-right', 'icon icon-sm finder__option-arrow') ?>
            </button>
          <?php endforeach; ?>
        </div>
        <noscript><p class="finder__noscript">Browse <a href="/services">all services</a> or <a href="/book-a-consultation">book a consultation</a> and we will help you choose.</p></noscript>
      </fieldset>

      <fieldset class="finder__step" data-finder-step="2" hidden>
        <legend class="finder__question" tabindex="-1" data-finder-q2></legend>
        <div class="finder__options finder__options--list" data-finder-answers></div>
        <button type="button" class="finder__back" data-finder-back><?= icon('arrow-right', 'icon icon-sm icon-flip') ?> Back</button>
      </fieldset>

      <div class="finder__step" data-finder-step="3" hidden>
        <h3 class="finder__question" tabindex="-1" data-finder-result-title>Our recommendation</h3>
        <div data-finder-result aria-live="polite"></div>
        <div class="btn-row">
          <?= button('Book a Consultation', '/book-a-consultation', 'primary', 'calendar-check') ?>
          <button type="button" class="btn btn-ghost" data-finder-restart><span>Start over</span><?= icon('refresh-cw', 'icon btn-icon') ?></button>
        </div>
      </div>
    </div>
    <?php
    return (string) ob_get_clean();
}
