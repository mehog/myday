<?php

namespace App\Support;

use Illuminate\Support\Js;

class Clipboard
{
    public static function alpineCopy(string $text, string $tooltipMessage = 'Link copied'): string
    {
        $textJs = Js::from($text);
        $messageJs = Js::from($tooltipMessage);

        return <<<JS
            (function (text) {
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text);
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.left = '-9999px';
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }
            })({$textJs})
            \$tooltip({$messageJs}, { theme: \$store.theme, timeout: 2000 })
        JS;
    }
}
