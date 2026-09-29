<?php
/**
 * Schema-driven form rendering and parsing for the admin editor.
 */

declare(strict_types=1);

function field_id(string $name): string
{
    return 'f-' . trim(preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
}

function field_token(string $name): string
{
    return '__R' . substr(md5($name), 0, 8) . '__';
}

function icon_names(): array
{
    static $names = null;
    return $names ??= array_keys(require INC . '/icon-data.php');
}

/** Render one field (recursively for groups and repeaters). */
function field_render(array $f, string $name, mixed $value): string
{
    $type = $f['type'];
    $id = field_id($name);
    $label = e($f['label'] ?? '');
    $hint = !empty($f['hint']) ? '<p class="hint">' . e($f['hint']) . '</p>' : '';
    $required = !empty($f['required']) ? ' required' : '';

    switch ($type) {
        case 'group':
            $html = '<fieldset class="group"><legend>' . $label . '</legend>' . $hint . '<div class="group__body">';
            foreach ($f['fields'] as $child) {
                $html .= field_render($child, $name . '[' . $child['key'] . ']', is_array($value) ? ($value[$child['key']] ?? null) : null);
            }
            return $html . '</div></fieldset>';

        case 'repeater':
            return repeater_render($f, $name, is_array($value) ? array_values($value) : []);

        case 'bool':
            return '<div class="field field--check"><input type="hidden" name="' . e($name) . '" value="0">'
                . '<input type="checkbox" id="' . $id . '" name="' . e($name) . '" value="1"' . (!empty($value) ? ' checked' : '') . '>'
                . '<label for="' . $id . '">' . $label . '</label>' . $hint . '</div>';

        case 'select':
        case 'icon':
            $options = $type === 'icon' ? array_combine(icon_names(), icon_names()) : $f['options'];
            $opts = '';
            foreach ($options as $val => $text) {
                $opts .= '<option value="' . e((string) $val) . '"' . ((string) $value === (string) $val ? ' selected' : '') . '>' . e((string) $text) . '</option>';
            }
            $preview = $type === 'icon' ? '<span class="icon-preview" data-icon-preview>' . icon((string) ($value ?: 'circle-help')) . '</span>' : '';
            return '<div class="field"><label for="' . $id . '">' . $label . '</label>' . $hint
                . '<div class="select-row">' . $preview . '<select id="' . $id . '" name="' . e($name) . '"' . ($type === 'icon' ? ' data-icon-select' : '') . '>' . $opts . '</select></div></div>';

        case 'image':
            $val = (string) ($value ?? '');
            $src = image_preview_src($val);
            return '<div class="field field--image" data-image-field><label for="' . $id . '">' . $label . '</label>' . $hint
                . '<div class="image-row"><span class="thumb" data-thumb>' . ($src ? '<img src="' . e($src) . '" alt="">' : '') . '</span>'
                . '<input type="text" id="' . $id . '" name="' . e($name) . '" value="' . e($val) . '" data-image-input placeholder="No image selected">'
                . '<button type="button" class="btn btn--light" data-media-pick>Choose…</button>'
                . '<button type="button" class="btn btn--light" data-media-clear aria-label="Remove image">Clear</button></div></div>';

        case 'password':
            $set = cfg('ai.api_key') !== '' && cfg('ai.api_key') !== null;
            return '<div class="field"><label for="' . $id . '">' . $label . '</label>' . $hint
                . '<input type="password" id="' . $id . '" name="' . e($name) . '" value="" autocomplete="new-password" placeholder="' . ($set ? 'A key is saved. Leave blank to keep it.' : 'Not set') . '">'
                . '</div>';

        case 'textarea':
        case 'richtext':
        case 'lines':
        case 'paragraphs':
            $text = match ($type) {
                'lines' => is_array($value) ? implode("\n", $value) : (string) $value,
                'paragraphs' => is_array($value) ? implode("\n\n", $value) : (string) $value,
                default => (string) ($value ?? ''),
            };
            if ($type === 'richtext') {
                $hint .= '<p class="hint">Formatting: <code>## Heading</code> on its own line, <code>- item</code> for lists, <code>**bold**</code>, <code>[link text](/contact)</code>. Leave a blank line between paragraphs.</p>';
            }
            $rows = (int) ($f['rows'] ?? ($type === 'lines' ? max(3, substr_count($text, "\n") + 2) : 4));
            $counter = isset($f['counter']) ? ' data-counter="' . (int) $f['counter'] . '"' : '';
            return '<div class="field"><label for="' . $id . '">' . $label . '</label>' . $hint
                . '<textarea id="' . $id . '" name="' . e($name) . '" rows="' . $rows . '"' . $counter . $required . '>' . e($text) . '</textarea></div>';

        default: // text, url, email
            $inputType = in_array($type, ['url', 'email'], true) ? $type : 'text';
            $counter = isset($f['counter']) ? ' data-counter="' . (int) $f['counter'] . '"' : '';
            return '<div class="field"><label for="' . $id . '">' . $label . '</label>' . $hint
                . '<input type="' . $inputType . '" id="' . $id . '" name="' . e($name) . '" value="' . e((string) ($value ?? '')) . '"' . $counter . $required . '></div>';
    }
}

function repeater_item(array $f, string $itemName, array $item, bool $open): string
{
    $labelKey = $f['item_label'] ?? null;
    $title = $labelKey && !empty($item[$labelKey]) ? (string) $item[$labelKey] : 'New item';
    $html = '<details class="repeater__item" data-item' . ($open ? ' open' : '') . '>'
        . '<summary><span class="repeater__title" data-item-title data-title-field="' . e((string) $labelKey) . '">' . e(mb_strimwidth($title, 0, 80, '…')) . '</span>'
        . '<span class="repeater__tools">'
        . '<button type="button" class="tool" data-move="up" aria-label="Move up">' . icon('chevron-down', 'icon icon-xs icon-up') . '</button>'
        . '<button type="button" class="tool" data-move="down" aria-label="Move down">' . icon('chevron-down', 'icon icon-xs') . '</button>'
        . '<button type="button" class="tool tool--danger" data-remove aria-label="Remove">' . icon('x', 'icon icon-xs') . '</button>'
        . '</span></summary><div class="repeater__body">';
    foreach ($f['fields'] as $child) {
        $html .= field_render($child, $itemName . '[' . $child['key'] . ']', $item[$child['key']] ?? null);
    }
    return $html . '</div></details>';
}

function repeater_render(array $f, string $name, array $items): string
{
    $token = field_token($name);
    $html = '<div class="repeater" data-repeater data-token="' . $token . '">';
    if (!empty($f['label'])) {
        $html .= '<p class="repeater__label">' . e($f['label']) . '</p>';
    }
    if (!empty($f['hint'])) {
        $html .= '<p class="hint">' . e($f['hint']) . '</p>';
    }
    $html .= '<div class="repeater__items" data-items>';
    foreach ($items as $i => $item) {
        $html .= repeater_item($f, $name . '[' . $i . ']', (array) $item, false);
    }
    $html .= '</div><template data-template>' . repeater_item($f, $name . '[' . $token . ']', [], true) . '</template>'
        . '<button type="button" class="btn btn--light btn--add" data-add>' . icon('plus', 'icon icon-xs') . ' ' . e($f['add_label'] ?? 'Add item') . '</button></div>';
    return $html;
}

/** Parse submitted input for a field, keeping unknown keys from $old. */
function field_parse(array $f, mixed $input, mixed $old): mixed
{
    $str = static fn ($v, int $max = 20000): string => is_string($v) ? mb_substr(trim(str_replace("\r", '', $v)), 0, $max) : '';

    switch ($f['type']) {
        case 'group':
            $out = is_array($old) ? $old : [];
            foreach ($f['fields'] as $child) {
                $out[$child['key']] = field_parse($child, is_array($input) ? ($input[$child['key']] ?? null) : null, $out[$child['key']] ?? null);
            }
            return $out;

        case 'repeater':
            $items = [];
            foreach (is_array($input) ? $input : [] as $row) {
                $item = field_parse(['type' => 'group', 'fields' => $f['fields']], $row, []);
                if (implode('', array_map(static fn ($v) => is_array($v) ? implode('', array_map('json_encode', $v)) : (string) $v, $item)) === '') {
                    continue; // skip completely empty rows
                }
                $items[] = $item;
            }
            if (!empty($f['map_key'])) {
                $keyed = [];
                foreach ($items as $item) {
                    $key = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string) ($item[$f['map_key']] ?? $item['name'] ?? 'item'))), '-') ?: 'item';
                    $base = $key;
                    for ($n = 2; isset($keyed[$key]); $n++) {
                        $key = $base . '-' . $n;
                    }
                    unset($item[$f['map_key']]);
                    $keyed[$key] = $item;
                }
                return $keyed;
            }
            return $items;

        case 'bool':
            return !empty($input) && $input !== '0';

        case 'select':
            $v = is_string($input) ? $input : '';
            return array_key_exists($v, $f['options']) ? $v : (string) array_key_first($f['options']);

        case 'icon':
            return in_array($input, icon_names(), true) ? $input : 'circle-help';

        case 'password':
            $v = $str($input, 500);
            return $v !== '' ? $v : (string) ($old ?? '');

        case 'lines':
            return array_values(array_filter(array_map('trim', explode("\n", $str($input))), 'strlen'));

        case 'paragraphs':
            return array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', $str($input))), 'strlen'));

        case 'url':
            $v = $str($input, 2000);
            return ($v === '' || preg_match('#^https?://#i', $v)) ? $v : 'https://' . ltrim($v, '/');

        case 'email':
            $v = $str($input, 254);
            return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : '';

        case 'image':
            $v = $str($input, 500);
            return preg_match('#^[A-Za-z0-9_./-]*$#', $v) && !str_contains($v, '..') ? $v : '';

        default:
            return $str($input, $f['type'] === 'text' ? 1000 : 20000);
    }
}

/** URL for an image preview: an uploaded file path or a photo key. */
function image_preview_src(string $value): string
{
    if ($value === '') {
        return '';
    }
    if (preg_match('#^(assets/|/assets/)#', $value)) {
        return '/' . ltrim($value, '/');
    }
    $img = content('images')[$value] ?? null;
    if ($img && !empty($img['file'])) {
        return '/' . ltrim($img['file'], '/');
    }
    if ($img && !empty($img['id'])) {
        return 'https://images.unsplash.com/photo-' . $img['id'] . '?auto=format&fit=crop&w=160&q=60';
    }
    return '';
}
