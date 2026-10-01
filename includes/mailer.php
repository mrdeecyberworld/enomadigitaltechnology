<?php
/**
 * Email sending: SMTP, Resend API or PHP mail(). Configure in Admin → Email.
 * No external libraries: works on any PHP 8.1+ host.
 */

declare(strict_types=1);

/** Email settings with defaults (and support for the older forms.* settings). */
function mail_settings(): array
{
    $m = array_replace_recursive([
        'method'     => 'none',
        'to'         => '',
        'from_email' => '',
        'from_name'  => '',
        'smtp'       => ['host' => '', 'port' => '587', 'encryption' => 'tls', 'username' => '', 'password' => ''],
        'resend_api_key' => '',
        'auto_reply' => false,
        'auto_reply_subject' => '',
        'auto_reply_body' => '',
    ], (array) cfg('mail', []));
    // Older configuration: forms.delivery = 'mail'
    if ($m['method'] === 'none' && cfg('forms.delivery') === 'mail' && cfg('forms.to')) {
        $m['method'] = 'php';
        $m['to'] = $m['to'] ?: (string) cfg('forms.to');
        $m['from_email'] = $m['from_email'] ?: (string) cfg('forms.from');
    }
    $m['from_email'] = $m['from_email'] ?: 'no-reply@' . (parse_url((string) cfg('base_url'), PHP_URL_HOST) ?: 'localhost');
    $m['from_name'] = $m['from_name'] ?: (string) site('name');
    return $m;
}

function mail_enabled(): bool
{
    $m = mail_settings();
    return $m['method'] !== 'none' && $m['to'] !== '';
}

/**
 * Send an email.
 * @param array{to: string|array, subject: string, text: string, html?: string, reply_to?: string} $msg
 * @return array{ok: bool, error: ?string}
 */
function send_mail(array $msg, ?array $settings = null): array
{
    $m = $settings ?? mail_settings();
    $to = array_values(array_filter(array_map('trim', is_array($msg['to']) ? $msg['to'] : explode(',', (string) $msg['to'])), static fn ($a) => filter_var($a, FILTER_VALIDATE_EMAIL)));
    if (!$to) {
        return ['ok' => false, 'error' => 'No valid recipient email address.'];
    }
    $clean = static fn (string $s): string => trim(str_replace(["\r", "\n"], ' ', $s));
    $msg['subject'] = $clean((string) $msg['subject']);
    $msg['reply_to'] = filter_var($msg['reply_to'] ?? '', FILTER_VALIDATE_EMAIL) ? $msg['reply_to'] : '';
    $msg['html'] ??= mail_html_wrap((string) $msg['text']);
    $from = $clean((string) $m['from_email']);
    $fromName = $clean((string) $m['from_name']);

    try {
        return match ($m['method']) {
            'smtp'   => smtp_send($m['smtp'], $from, $fromName, $to, $msg),
            'resend' => resend_send((string) $m['resend_api_key'], $from, $fromName, $to, $msg),
            'php'    => php_mail_send($from, $fromName, $to, $msg),
            default  => ['ok' => false, 'error' => 'Email sending is turned off.'],
        };
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/** Simple, branded HTML version of a plain-text email. */
function mail_html_wrap(string $text): string
{
    $body = nl2br(e($text));
    $name = e((string) site('name'));
    return '<!doctype html><html><body style="margin:0;background:#f4f6fa;font-family:Arial,Helvetica,sans-serif;color:#0d1628">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fa;padding:24px 12px"><tr><td align="center">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden">'
        . '<tr><td style="background:#0b1324;padding:20px 28px;color:#ffffff;font-weight:bold;letter-spacing:2px">ENOMA <span style="color:#67e0f3;font-weight:normal;letter-spacing:1px;font-size:12px">DIGITAL TECHNOLOGIES</span></td></tr>'
        . '<tr><td style="padding:28px;font-size:15px;line-height:1.6">' . $body . '</td></tr>'
        . '<tr><td style="padding:16px 28px;background:#f7f9fc;color:#647185;font-size:12px">' . $name . ' · ' . e((string) site('tagline')) . '</td></tr>'
        . '</table></td></tr></table></body></html>';
}

/** Encode a header value (subject, name) for non-ASCII text. */
function mail_header_text(string $s): string
{
    return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
}

/** Build a multipart (text + HTML) message. Returns [headers string, body string]. */
function mail_build(string $from, string $fromName, array $to, array $msg): array
{
    $boundary = 'b' . bin2hex(random_bytes(12));
    $domain = substr(strrchr($from, '@') ?: '@localhost', 1);
    $headers = [
        'Date: ' . date('r'),
        'From: ' . mail_header_text($fromName) . ' <' . $from . '>',
        'To: ' . implode(', ', $to),
        'Subject: ' . mail_header_text($msg['subject']),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $domain . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    if ($msg['reply_to'] !== '') {
        $headers[] = 'Reply-To: ' . $msg['reply_to'];
    }
    $part = static fn (string $type, string $content): string => '--' . $boundary . "\r\n"
        . 'Content-Type: ' . $type . '; charset=UTF-8' . "\r\n"
        . 'Content-Transfer-Encoding: base64' . "\r\n\r\n"
        . rtrim(chunk_split(base64_encode($content), 76, "\r\n")) . "\r\n";
    $body = $part('text/plain', (string) $msg['text']) . $part('text/html', (string) $msg['html']) . '--' . $boundary . "--\r\n";
    return [$headers, $body];
}

/** Send through an SMTP server (STARTTLS on 587, SSL on 465, or plain). */
function smtp_send(array $s, string $from, string $fromName, array $to, array $msg): array
{
    $host = trim((string) ($s['host'] ?? ''));
    $port = (int) ($s['port'] ?? 587) ?: 587;
    $enc = (string) ($s['encryption'] ?? 'tls');
    if ($host === '') {
        return ['ok' => false, 'error' => 'Enter your SMTP server address.'];
    }
    $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host]]);
    $sock = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$sock) {
        return ['ok' => false, 'error' => "Couldn't connect to $host on port $port ($errstr). Check the server address, port and encryption, and that your host allows outgoing email connections."];
    }
    stream_set_timeout($sock, 20);

    $read = static function () use ($sock): array {
        $text = '';
        while (($line = fgets($sock, 1024)) !== false) {
            $text .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        return [(int) substr($text, 0, 3), trim($text)];
    };
    $cmd = static function (string $command, array $expect) use ($sock, $read): string {
        fwrite($sock, $command . "\r\n");
        [$code, $text] = $read();
        if (!in_array($code, $expect, true)) {
            $step = match (true) {
                $command === 'STARTTLS' => 'a secure (TLS) connection on this port. Try Security “SSL” with port 465, or check the port number',
                str_starts_with($command, 'EHLO') => 'the connection greeting',
                str_starts_with($command, 'Date:') => 'the message',
                str_starts_with($command, 'AUTH') || (bool) preg_match('#^[A-Za-z0-9+/=]+$#', $command) => 'the login',
                str_starts_with($command, 'MAIL FROM') => 'the sender address',
                str_starts_with($command, 'RCPT TO') => 'the recipient address',
                default => strtok($command, ' '),
            };
            throw new RuntimeException('The email server rejected ' . $step . ': ' . $text);
        }
        return $text;
    };

    try {
        [$code, $greeting] = $read();
        if ($code !== 220) {
            throw new RuntimeException('Unexpected greeting from the email server: ' . $greeting);
        }
        $ehloHost = parse_url((string) cfg('base_url'), PHP_URL_HOST) ?: 'localhost';
        $caps = $cmd('EHLO ' . $ehloHost, [250]);
        if ($enc === 'tls') {
            $cmd('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                throw new RuntimeException('Could not start a secure (TLS) connection. Try port 465 with SSL instead.');
            }
            $caps = $cmd('EHLO ' . $ehloHost, [250]);
        }
        $user = (string) ($s['username'] ?? '');
        if ($user !== '') {
            if (stripos($caps, 'AUTH') !== false && stripos($caps, 'PLAIN') !== false && stripos($caps, 'LOGIN') === false) {
                $cmd('AUTH PLAIN ' . base64_encode("\0" . $user . "\0" . (string) ($s['password'] ?? '')), [235]);
            } else {
                $cmd('AUTH LOGIN', [334]);
                $cmd(base64_encode($user), [334]);
                try {
                    $cmd(base64_encode((string) ($s['password'] ?? '')), [235]);
                } catch (RuntimeException $e) {
                    throw new RuntimeException('The username or password was not accepted. For Gmail and Outlook, use an app password. (' . $e->getMessage() . ')');
                }
            }
        }
        $cmd('MAIL FROM:<' . $from . '>', [250]);
        foreach ($to as $rcpt) {
            $cmd('RCPT TO:<' . $rcpt . '>', [250, 251]);
        }
        $cmd('DATA', [354]);
        [$headers, $body] = mail_build($from, $fromName, $to, $msg);
        // Base64 bodies never contain a line starting with ".", so no dot-stuffing is needed.
        $cmd(implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.", [250]);
        @fwrite($sock, "QUIT\r\n");
        fclose($sock);
        return ['ok' => true, 'error' => null];
    } catch (RuntimeException $e) {
        @fwrite($sock, "QUIT\r\n");
        @fclose($sock);
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/** Send with the Resend API (https://resend.com). */
function resend_send(string $apiKey, string $from, string $fromName, array $to, array $msg): array
{
    if ($apiKey === '') {
        return ['ok' => false, 'error' => 'Enter your Resend API key.'];
    }
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'Your hosting needs the PHP cURL extension to use Resend.'];
    }
    $payload = array_filter([
        'from'     => ($fromName !== '' ? $fromName . ' <' . $from . '>' : $from),
        'to'       => $to,
        'subject'  => $msg['subject'],
        'text'     => $msg['text'],
        'html'     => $msg['html'],
        'reply_to' => $msg['reply_to'] ?: null,
    ]);
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json', 'User-Agent: EnomaWebsite/1.0'],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        return ['ok' => false, 'error' => 'Could not reach Resend: ' . $curlError];
    }
    if ($status >= 200 && $status < 300) {
        return ['ok' => true, 'error' => null];
    }
    $data = json_decode((string) $raw, true);
    $detail = is_array($data) ? (string) ($data['message'] ?? $data['error'] ?? '') : '';
    $hint = match (true) {
        $status === 401 || $status === 403 => ' Check that the API key is correct and active.',
        $status === 422 && stripos($detail, 'domain') !== false => ' The "from" address must use a domain you have verified in Resend.',
        default => '',
    };
    return ['ok' => false, 'error' => 'Resend returned an error (' . $status . ')' . ($detail ? ': ' . $detail : '') . '.' . $hint];
}

/** Send with PHP's built-in mail() (uses your web host's mail server). */
function php_mail_send(string $from, string $fromName, array $to, array $msg): array
{
    [$headers, $body] = mail_build($from, $fromName, $to, $msg);
    // mail() takes To and Subject separately.
    $headers = array_values(array_filter($headers, static fn ($h) => !str_starts_with($h, 'To:') && !str_starts_with($h, 'Subject:')));
    $params = filter_var($from, FILTER_VALIDATE_EMAIL) ? '-f' . $from : '';
    $ok = @mail(implode(', ', $to), mail_header_text($msg['subject']), $body, implode("\r\n", $headers), $params);
    return $ok ? ['ok' => true, 'error' => null] : ['ok' => false, 'error' => 'Your server could not send the email with PHP mail(). Try SMTP or Resend instead.'];
}
