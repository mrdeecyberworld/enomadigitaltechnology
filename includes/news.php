<?php
/**
 * Headline fetcher for Admin → Tech & security news.
 * Fetches RSS/Atom feeds in parallel, caches each for an hour in storage/cache/news.
 */

declare(strict_types=1);

const NEWS_CACHE_TTL = 3600;
const NEWS_PER_FEED = 8;

function news_topics(): array
{
    return ['cybersecurity' => 'Cybersecurity', 'advisories' => 'Security advisories', 'technology' => 'Technology'];
}

/**
 * @return array{items: array, errors: array, fetched_at: int}
 */
function news_fetch_all(array $feeds, bool $force = false): array
{
    $cacheDir = storage_dir('cache/news');
    $items = [];
    $errors = [];
    $pending = [];
    $oldest = time();

    foreach ($feeds as $feed) {
        $url = (string) ($feed['url'] ?? '');
        if (!preg_match('#^https?://#i', $url)) {
            continue;
        }
        $file = $cacheDir . '/' . md5($url) . '.json';
        $cached = json_read($file);
        if (!$force && is_array($cached) && time() - (int) $cached['at'] < NEWS_CACHE_TTL) {
            $items = array_merge($items, $cached['items']);
            $oldest = min($oldest, (int) $cached['at']);
            continue;
        }
        $pending[] = ['feed' => $feed, 'file' => $file, 'cached' => $cached];
    }

    if ($pending && function_exists('curl_multi_init')) {
        $mh = curl_multi_init();
        $handles = [];
        foreach ($pending as $i => $p) {
            $ch = curl_init($p['feed']['url']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; EnomaSiteAdmin/1.0; +' . abs_url('/') . ')',
                CURLOPT_HTTPHEADER     => ['Accept: application/rss+xml, application/atom+xml, application/xml, text/xml;q=0.9, */*;q=0.5'],
                CURLOPT_ENCODING       => '',
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$i] = $ch;
        }
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) {
                curl_multi_select($mh, 1.0);
            }
        } while ($running && $status === CURLM_OK);

        foreach ($handles as $i => $ch) {
            $p = $pending[$i];
            $body = (string) curl_multi_getcontent($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            $parsed = $code >= 200 && $code < 300 ? news_parse($body, $p['feed']) : null;
            if ($parsed === null) {
                $errors[] = $p['feed']['name'] ?? $p['feed']['url'];
                // Fall back to the last good copy, however old.
                if (is_array($p['cached'])) {
                    $items = array_merge($items, $p['cached']['items']);
                }
                continue;
            }
            json_write($p['file'], ['at' => time(), 'items' => $parsed]);
            $items = array_merge($items, $parsed);
        }
        curl_multi_close($mh);
    } elseif ($pending) {
        foreach ($pending as $p) {
            $errors[] = $p['feed']['name'] ?? $p['feed']['url'];
        }
    }

    usort($items, static fn ($a, $b) => $b['ts'] <=> $a['ts']);
    return ['items' => $items, 'errors' => $errors, 'fetched_at' => $pending ? time() : $oldest];
}

/** Parse RSS 2.0, RSS 1.0 (RDF) or Atom. Returns null if it isn't a feed. */
function news_parse(string $xml, array $feed): ?array
{
    if (trim($xml) === '') {
        return null;
    }
    $prev = libxml_use_internal_errors(true);
    $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    if ($doc === false) {
        return null;
    }

    $entries = [];
    $root = $doc->getName();
    if ($root === 'rss') {
        foreach ($doc->channel->item as $it) {
            $entries[] = [(string) $it->title, (string) $it->link, (string) ($it->pubDate ?: $it->children('http://purl.org/dc/elements/1.1/')->date), (string) $it->description];
        }
    } elseif ($root === 'feed') {
        foreach ($doc->entry as $it) {
            $link = '';
            foreach ($it->link as $l) {
                $rel = (string) $l['rel'];
                if ($rel === '' || $rel === 'alternate') {
                    $link = (string) $l['href'];
                    break;
                }
            }
            $entries[] = [(string) $it->title, $link, (string) ($it->published ?: $it->updated), (string) ($it->summary ?: $it->content)];
        }
    } elseif ($root === 'RDF') {
        foreach ($doc->children('http://purl.org/rss/1.0/')->item as $it) {
            $entries[] = [(string) $it->title, (string) $it->link, (string) $it->children('http://purl.org/dc/elements/1.1/')->date, (string) $it->description];
        }
    } else {
        return null;
    }

    $items = [];
    foreach (array_slice($entries, 0, NEWS_PER_FEED) as [$title, $link, $date, $summary]) {
        $title = trim(html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $link = trim($link);
        if ($title === '' || !preg_match('#^https?://#i', $link)) {
            continue;
        }
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($summary), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $items[] = [
            'title'   => mb_substr($title, 0, 300),
            'link'    => $link,
            'ts'      => strtotime($date) ?: 0,
            'summary' => mb_strimwidth($text, 0, 240, '…'),
            'source'  => (string) ($feed['name'] ?? parse_url($link, PHP_URL_HOST)),
            'topic'   => (string) ($feed['topic'] ?? 'technology'),
        ];
    }
    return $items;
}
