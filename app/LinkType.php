<?php

namespace App;

enum LinkType: string
{
    case Public = 'public';
    case Personal = 'personal';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Javni link',
            self::Personal => 'Personalni link',
        };
    }
}
