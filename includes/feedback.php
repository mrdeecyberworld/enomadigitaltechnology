<?php
/**
 * Client feedback: clients send a review from the Feedback page; it waits in
 * Admin → Client feedback until you approve it, then it shows as a testimonial.
 * Nothing a visitor sends is published automatically.
 */

declare(strict_types=1);

function feedback_dir(): string
{
    return storage_dir('feedback');
}

/** All feedback records, newest first. */
function feedback_all(): array
{
    $all = [];
    foreach (store_list('feedback') as $file) {
        $rec = json_read($file, []);
        if (!empty($rec['id'])) {
            $all[] = $rec;
        }
    }
    usort($all, static fn ($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
    return $all;
}

function feedback_pending_count(): int
{
    return count(array_filter(feedback_all(), static fn ($f) => ($f['status'] ?? '') === 'pending'));
}

/** Star rating as accessible markup (1–5). */
function rating_stars(int $rating, string $class = 'stars'): string
{
    $rating = max(0, min(5, $rating));
    if ($rating === 0) {
        return '';
    }
    $out = '<span class="' . e($class) . '" role="img" aria-label="Rated ' . $rating . ' out of 5">';
    for ($i = 1; $i <= 5; $i++) {
        $out .= '<span class="star' . ($i <= $rating ? ' star--on' : '') . '" aria-hidden="true">★</span>';
    }
    return $out . '</span>';
}

/**
 * Handle the feedback form. Same pattern as handle_form(): validates, saves,
 * then redirects (Post/Redirect/Get).
 */
function handle_feedback_form(string $formId): array
{
    start_session();
    $state = ['values' => ['rating' => '5'], 'errors' => [], 'status' => null, 'message' => ''];

    if (isset($_SESSION['form_result'][$formId])) {
        $state = array_replace($state, $_SESSION['form_result'][$formId]);
        unset($_SESSION['form_result'][$formId]);
        return $state;
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['form_id'] ?? '') !== $formId) {
        return $state;
    }

    $in = static fn (string $k, int $max): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
    $v = [
        'name'    => $in('name', 100),
        'email'   => $in('email', 254),
        'role'    => $in('role', 120),
        'service' => $in('service', 60),
        'rating'  => $in('rating', 1),
        'quote'   => $in('quote', 1500),
        'consent' => !empty($_POST['consent']),
    ];
    $errors = [];
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $errors['_form'] = 'Your session expired. Please submit the form again.';
    }
    $startedAt = (int) ($_POST['started'] ?? 0);
    $isBot = ($_POST['website'] ?? '') !== '' || $startedAt === 0 || (time() - $startedAt) < 3;

    if ($v['name'] === '') {
        $errors['name'] = 'Please enter your name.';
    }
    if ($v['email'] === '') {
        $errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address, like name@example.com.';
    }
    if ($v['service'] !== '' && !array_key_exists($v['service'], service_options())) {
        $errors['service'] = 'Please choose a service from the list.';
    }
    if (!preg_match('/^[1-5]$/', $v['rating'])) {
        $errors['rating'] = 'Please choose a rating from 1 to 5 stars.';
    }
    if (mb_strlen($v['quote']) < 20) {
        $errors['quote'] = 'Please write a little more about your experience (at least 20 characters).';
    }

    if ($errors) {
        return array_replace($state, [
            'values'  => $v,
            'errors'  => $errors,
            'status'  => 'error',
            'message' => $errors['_form'] ?? 'Please correct the highlighted fields and try again.',
        ]);
    }

    $ok = true;
    if (!$isBot) {
        $id = date('Ymd-His') . '-' . bin2hex(random_bytes(3));
        $ok = json_write(feedback_dir() . '/' . $id . '.json', [
            'id'         => $id,
            'status'     => 'pending',
            'name'       => $v['name'],
            'email'      => $v['email'],
            'role'       => $v['role'],
            'service'    => $v['service'] !== '' ? (service_options()[$v['service']] ?? '') : '',
            'rating'     => (int) $v['rating'],
            'quote'      => $v['quote'],
            'consent'    => $v['consent'],
            'created_at' => date('c'),
        ]);
        if ($ok && mail_enabled()) {
            $base = rtrim((string) cfg('base_url'), '/');
            send_mail([
                'to'       => mail_settings()['to'],
                'subject'  => 'New client feedback from ' . $v['name'] . ' (' . $v['rating'] . '/5)',
                'text'     => "New feedback from your website. It is not public until you approve it.\n\n"
                    . 'Name: ' . $v['name'] . "\nEmail: " . $v['email'] . "\nRating: " . $v['rating'] . "/5\n"
                    . ($v['role'] !== '' ? 'Role: ' . $v['role'] . "\n" : '')
                    . 'May be published: ' . ($v['consent'] ? 'Yes' : 'No') . "\n\n" . $v['quote']
                    . "\n\nReview it: " . $base . '/admin/feedback',
                'reply_to' => $v['email'],
            ]);
        }
    }

    $result = $ok
        ? ['status' => 'success', 'message' => 'Thank you, ' . $v['name'] . '! Your feedback has been received. We read every review before it appears on the website.']
        : ['status' => 'error', 'message' => 'Sorry, your feedback could not be saved right now. Please try again later.'];
    $_SESSION['form_result'][$formId] = $result + ['values' => $ok ? ['rating' => '5'] : $v];
    header('Location: ' . strtok($_SERVER['REQUEST_URI'] ?? '/', '?') . '#' . $formId, true, 303);
    exit;
}
