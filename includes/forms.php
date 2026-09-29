<?php
/**
 * Form handling: contact, quote, consultation and service inquiry forms.
 *
 * Forms post back to the page they are on (works without JavaScript), are
 * validated server-side, protected with a CSRF token, a honeypot and a
 * minimum fill time, then delivered according to cfg('forms.delivery').
 */

declare(strict_types=1);

const FORM_TYPES = [
    'contact'      => 'Contact message',
    'quote'        => 'Quote request',
    'consultation' => 'Consultation request',
    'inquiry'      => 'Service inquiry',
];

const BUDGET_RANGES = [
    'Under $1,000',
    '$1,000 – $2,500',
    '$2,500 – $5,000',
    '$5,000 – $10,000',
    '$10,000+',
    'Not sure yet',
];

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('enoma_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(?string $token): bool
{
    start_session();
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/** Service options for form selects. */
function service_options(): array
{
    $options = [];
    foreach (services() as $slug => $svc) {
        $options[$slug] = $svc['name'];
    }
    $options['not-sure'] = "I'm not sure yet";
    return $options;
}

/**
 * Handle a submission for the given form id. Returns the state used to render
 * the form: values, errors, status ('success'|'error'|null) and message.
 */
function handle_form(string $formId, string $type, ?string $defaultService = null): array
{
    start_session();
    $state = [
        'values'  => ['service' => $defaultService ?? ''],
        'errors'  => [],
        'status'  => null,
        'message' => '',
    ];

    // Post/Redirect/Get: show the result stored before the redirect.
    if (isset($_SESSION['form_result'][$formId])) {
        $state = array_replace($state, $_SESSION['form_result'][$formId]);
        unset($_SESSION['form_result'][$formId]);
        return $state;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['form_id'] ?? '') !== $formId) {
        return $state;
    }

    $in = static fn (string $k, int $max): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
    $values = [
        'name'    => $in('name', 100),
        'email'   => $in('email', 254),
        'phone'   => $in('phone', 30),
        'company' => $in('company', 120),
        'service' => $in('service', 60),
        'budget'  => $in('budget', 40),
        'message' => $in('message', 5000),
    ];
    $errors = [];

    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $errors['_form'] = 'Your session expired. Please submit the form again.';
    }

    // Spam checks: hidden honeypot field and a minimum time on the page.
    $startedAt = (int) ($_POST['started'] ?? 0);
    $isBot = ($_POST['website'] ?? '') !== '' || $startedAt === 0 || (time() - $startedAt) < 3;

    if ($values['name'] === '') {
        $errors['name'] = 'Please enter your name.';
    }
    if ($values['email'] === '') {
        $errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address, like name@example.com.';
    }
    if ($values['phone'] !== '' && !preg_match('/^[0-9+().\-\s]{7,30}$/', $values['phone'])) {
        $errors['phone'] = 'Please enter a valid phone number, or leave this field blank.';
    }
    if (!array_key_exists($values['service'], service_options())) {
        $errors['service'] = 'Please choose the service you are interested in.';
    }
    if ($values['budget'] !== '' && !in_array($values['budget'], BUDGET_RANGES, true)) {
        $errors['budget'] = 'Please choose a budget range from the list.';
    }
    if (mb_strlen($values['message']) < 10) {
        $errors['message'] = 'Please tell us a little more (at least 10 characters).';
    }

    if ($errors) {
        return array_replace($state, [
            'values'  => $values,
            'errors'  => $errors,
            'status'  => 'error',
            'message' => $errors['_form'] ?? 'Please correct the highlighted fields and try again.',
        ]);
    }

    if ($isBot) {
        // Pretend success to bots; nothing is sent.
        $result = ['status' => 'success', 'message' => 'Thank you. Your message has been received.'];
    } else {
        $result = deliver_form($type, $values);
    }

    $_SESSION['form_result'][$formId] = $result + ['values' => $result['status'] === 'success' ? ['service' => $defaultService ?? ''] : $values];
    $redirect = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') . '#' . $formId;
    header('Location: ' . $redirect, true, 303);
    exit;
}

/**
 * Deliver a validated submission: it is always saved to the admin inbox
 * (Admin → Messages) and, when email is configured, also emailed.
 * Returns ['status' => 'success'|'error', 'message' => string].
 */
function deliver_form(string $type, array $v): array
{
    $serviceName = service_options()[$v['service']] ?? $v['service'];
    $saved = save_submission($type, $v, $serviceName);

    $emailed = false;
    if (cfg('forms.delivery') === 'mail' && cfg('forms.to')) {
        $emailed = send_form_email($type, $v, $serviceName);
    }

    if ($saved || $emailed) {
        return ['status' => 'success', 'message' => 'Thank you, ' . $v['name'] . '. Your ' . strtolower(FORM_TYPES[$type] ?? 'message') . ' has been received. We will reply by email.'];
    }
    $email = cfg('contact_email');
    return ['status' => 'error', 'message' => 'Sorry, your message could not be saved right now. Please try again later' . ($email ? ' or email us at ' . $email : '') . '.'];
}

/** Store a submission as a JSON file in storage/submissions (not web accessible). */
function save_submission(string $type, array $v, string $serviceName): bool
{
    $id = date('Ymd-His') . '-' . bin2hex(random_bytes(3));
    return json_write(storage_dir('submissions') . '/' . $id . '.json', [
        'id'           => $id,
        'type'         => $type,
        'type_label'   => FORM_TYPES[$type] ?? $type,
        'name'         => $v['name'],
        'email'        => $v['email'],
        'phone'        => $v['phone'],
        'company'      => $v['company'],
        'service'      => $serviceName,
        'budget'       => $v['budget'],
        'message'      => $v['message'],
        'page'         => current_path(),
        'created_at'   => date('c'),
        'read'         => false,
    ]);
}

/** Send via PHP mail(). Replace with SMTP / an email API for production-grade deliverability. */
function send_form_email(string $type, array $v, string $serviceName): bool
{
    $clean = static fn (string $s): string => str_replace(["\r", "\n"], ' ', $s);
    $subject = '[' . site('short_name') . '] ' . (FORM_TYPES[$type] ?? 'Website message') . ' from ' . $clean($v['name']);
    $body = implode("\n", [
        'Form: ' . (FORM_TYPES[$type] ?? $type),
        'Name: ' . $v['name'],
        'Email: ' . $v['email'],
        'Phone: ' . ($v['phone'] ?: '-'),
        'Company: ' . ($v['company'] ?: '-'),
        'Service: ' . $serviceName,
        'Budget: ' . ($v['budget'] ?: '-'),
        '',
        'Message:',
        $v['message'],
        '',
        'Sent from ' . abs_url(current_path()) . ' on ' . date('Y-m-d H:i T'),
    ]);
    $headers = implode("\r\n", [
        'From: ' . site('short_name') . ' <' . $clean((string) cfg('forms.from')) . '>',
        'Reply-To: ' . $clean($v['email']),
        'Content-Type: text/plain; charset=UTF-8',
    ]);
    return mail((string) cfg('forms.to'), '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
}
