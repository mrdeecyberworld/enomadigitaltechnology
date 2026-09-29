<?php
/**
 * Accessible FAQ accordion using native <details>/<summary>.
 */

declare(strict_types=1);

function faq_list(array $faqs): string
{
    $html = '<div class="faq-list">';
    foreach ($faqs as $f) {
        $html .= '<details class="faq-item reveal">'
            . '<summary><span class="faq-item__q">' . e($f['q']) . '</span>'
            . '<span class="faq-item__toggle" aria-hidden="true">' . icon('chevron-down', 'icon icon-sm') . '</span></summary>'
            . '<div class="faq-item__a"><p>' . e($f['a']) . '</p></div>'
            . '</details>';
    }
    return $html . '</div>';
}
