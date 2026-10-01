<?php
/**
 * Website colors (Admin → Appearance): light/dark theme, palette, custom accent.
 */

declare(strict_types=1);

function palettes(): array
{
    return [
        'midnight' => ['name' => 'Midnight Blue', 'note' => 'Deep navy with blue and cyan. Calm and trustworthy.', 'swatch' => ['#0b1322', '#111c30', '#2563eb', '#22c3e6']],
        'obsidian' => ['name' => 'Obsidian',      'note' => 'Graphite black with electric blue. Sleek and modern.', 'swatch' => ['#0b0c10', '#13151b', '#2563eb', '#22d3ee']],
        'ocean'    => ['name' => 'Deep Ocean',    'note' => 'Dark teal with aqua. Fresh and distinctive.', 'swatch' => ['#06171c', '#0b2228', '#0f766e', '#38bdf8']],
        'royal'    => ['name' => 'Royal Violet',  'note' => 'Deep indigo with violet. Bold and premium.', 'swatch' => ['#0e0b24', '#16132f', '#5b3fd6', '#e879f9']],
    ];
}

function appearance(): array
{
    $a = (array) cfg('appearance', []);
    $theme = (string) ($a['theme'] ?? cfg('site_theme', 'dark'));
    $palette = (string) ($a['palette'] ?? 'midnight');
    $accent = strtolower((string) ($a['accent'] ?? ''));
    return [
        'theme'   => $theme === 'light' ? 'light' : 'dark',
        'palette' => isset(palettes()[$palette]) ? $palette : 'midnight',
        'accent'  => preg_match('/^#[0-9a-f]{6}$/', $accent) ? $accent : '',
    ];
}

/** @return array{0:int,1:int,2:int} */
function hex_rgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function rgb_hex(array $c): string
{
    return sprintf('#%02x%02x%02x', ...array_map(static fn ($v) => max(0, min(255, (int) round($v))), $c));
}

/** Mix a color toward white (amount > 0) or black (amount < 0). */
function color_mix(string $hex, float $amount): string
{
    $c = hex_rgb($hex);
    $target = $amount >= 0 ? 255 : 0;
    $t = abs($amount);
    return rgb_hex(array_map(static fn ($v) => $v + ($target - $v) * $t, $c));
}

function color_luminance(string $hex): float
{
    $lin = static function (int $v): float {
        $s = $v / 255;
        return $s <= 0.03928 ? $s / 12.92 : (($s + 0.055) / 1.055) ** 2.4;
    };
    [$r, $g, $b] = hex_rgb($hex);
    return 0.2126 * $lin($r) + 0.7152 * $lin($g) + 0.0722 * $lin($b);
}

function color_contrast(string $a, string $b): float
{
    $la = color_luminance($a);
    $lb = color_luminance($b);
    return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
}

/** Darken or lighten $hex until it reaches $ratio contrast against $against. */
function color_for_contrast(string $hex, string $against, float $ratio, bool $lighten): string
{
    for ($i = 0; $i <= 20 && color_contrast($hex, $against) < $ratio; $i++) {
        $hex = color_mix($hex, $lighten ? 0.08 : -0.08);
    }
    return $hex;
}

/** CSS overrides for a custom accent color, with readable text guaranteed. */
function custom_accent_css(string $accent, string $theme): string
{
    $button = color_for_contrast($accent, '#ffffff', 4.6, false);   // white text on buttons
    $hover = color_mix($button, -0.15);
    $bright = color_mix($accent, 0.15);
    $light = color_mix($accent, 0.55);
    $rgb = static fn (string $h): string => implode(', ', hex_rgb($h));
    $vars = [
        '--blue-700' => $hover, '--blue-600' => $button, '--blue-500' => $bright, '--blue-300' => $light,
        '--blue-100' => color_mix($accent, 0.85), '--blue-50' => color_mix($accent, 0.93),
        '--accent-rgb' => $rgb($button), '--accent-bright-rgb' => $rgb($bright), '--accent-light-rgb' => $rgb($light),
        '--accent-mid' => color_mix($accent, -0.1), '--logo-1' => $bright,
    ];
    if ($theme === 'light') {
        $vars += [
            '--accent-text' => color_for_contrast($accent, '#ffffff', 4.6, false),
            '--accent-text-hover' => $hover,
            '--accent-soft' => color_mix($accent, 0.93), '--accent-soft-2' => color_mix($accent, 0.85),
        ];
    } else {
        $text = color_for_contrast(color_mix($accent, 0.45), '#16132f', 4.8, true);
        $vars += [
            '--accent-text' => $text, '--accent-text-hover' => color_mix($text, 0.3),
            '--accent-soft' => 'rgba(' . $rgb($bright) . ', .15)', '--accent-soft-2' => 'rgba(' . $rgb($bright) . ', .25)',
        ];
    }
    $css = '';
    foreach ($vars as $k => $v) {
        $css .= '  ' . $k . ': ' . $v . ";\n";
    }
    return "/* Custom accent color from Admin → Appearance */\nhtml[data-site-theme][data-palette] {\n" . $css . "}\n";
}
