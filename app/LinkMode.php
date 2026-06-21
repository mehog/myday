<?php

namespace App;

enum LinkMode: string
{
    case Public = 'public';
    case TokenOnly = 'token_only';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public shareable link',
            self::TokenOnly => 'Personal token links only',
        };
    }
}
