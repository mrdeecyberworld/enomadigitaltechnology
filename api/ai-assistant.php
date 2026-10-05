<?php
/**
 * POST /api/ai-assistant
 *
 * Request:  {"messages": [{"role": "user"|"assistant", "content": "..."}]}
 * Response: {"reply": "...", "links": [{"label","url"}], "suggestions": ["..."], "mode": "ai"|"guided"}
 *
 * The API key never reaches the browser: it is read on the server from the
 * ANTHROPIC_API_KEY environment variable or the private config file.
 */

declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require INC . '/assistant-engine.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $body, int $status = 200): never
{
    // Point links at the current page addresses (Admin → Page URLs).
    foreach ($body['links'] ?? [] as $i => $link) {
        $body['links'][$i]['url'] = link_path((string) $link['url']);
    }
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(['error' => 'Method not allowed'], 405);
}

// Same-origin only.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
    respond(['error' => 'Forbidden'], 403);
}

// Simple per-visitor rate limit (session + hashed IP, stored in storage/).
start_session();
$limit = (int) cfg('ai.rate_limit', 20);
$now = time();
$_SESSION['ai_hits'] = array_values(array_filter($_SESSION['ai_hits'] ?? [], static fn ($t) => $t > $now - 3600));
$ipFile = rate_limit_file();
$ipHits = [];
if ($ipFile && is_file($ipFile)) {
    $ipHits = array_values(array_filter((array) json_decode((string) file_get_contents($ipFile), true), static fn ($t) => $t > $now - 3600));
}
if (count($_SESSION['ai_hits']) >= $limit || count($ipHits) >= $limit * 2) {
    respond([
        'reply' => "You've reached the message limit for now. Please try again later, or book a consultation to talk with us directly.",
        'links' => [['label' => 'Book a Consultation', 'url' => '/book-a-consultation']],
        'suggestions' => [],
        'mode' => 'limited',
    ], 429);
}
$_SESSION['ai_hits'][] = $now;
session_write_close();
if ($ipFile) {
    $ipHits[] = $now;
    @file_put_contents($ipFile, json_encode($ipHits), LOCK_EX);
}

// Validate input.
$input = json_decode((string) file_get_contents('php://input', false, null, 0, 40000), true);
$raw = is_array($input['messages'] ?? null) ? array_slice($input['messages'], -12) : [];
$messages = [];
foreach ($raw as $m) {
    $role = $m['role'] ?? '';
    $content = is_string($m['content'] ?? null) ? trim(mb_substr($m['content'], 0, 1000)) : '';
    if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
        continue;
    }
    // Merge consecutive same-role turns so the conversation alternates.
    if ($messages && end($messages)['role'] === $role) {
        $messages[count($messages) - 1]['content'] .= "\n" . $content;
        continue;
    }
    $messages[] = ['role' => $role, 'content' => $content];
}
while ($messages && $messages[0]['role'] !== 'user') {
    array_shift($messages);
}
if (!$messages || end($messages)['role'] !== 'user') {
    respond(['error' => 'Please send a message.'], 422);
}

$lastUser = end($messages)['content'];

if ($screen = assistant_safety_screen($lastUser)) {
    respond($screen + ['mode' => 'guided']);
}

if ($ai = assistant_claude($messages)) {
    respond($ai + ['mode' => 'ai']);
}

respond(assistant_guided($messages) + ['mode' => 'guided']);

function rate_limit_file(): ?string
{
    $dir = storage_root() . '/ratelimit';
    if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
        return null;
    }
    if (!is_writable($dir)) {
        return null;
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return $dir . '/' . hash('sha256', $ip . '|' . __FILE__) . '.json';
}
