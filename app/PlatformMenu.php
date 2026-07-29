<?php

namespace App;

enum PlatformMenu: string
{
    case Regular = 'regular';
    case GlutenFree = 'gluten_free';
    case Vegetarian = 'vegetarian';

    public function label(): string
    {
        return match ($this) {
            self::Regular => __('menu.platform_regular'),
            self::GlutenFree => __('menu.platform_gluten_free'),
            self::Vegetarian => __('menu.platform_vegetarian'),
        };
    }

    public function defaultSortOrder(): int
    {
        return match ($this) {
            self::Regular => 0,
            self::GlutenFree => 1,
            self::Vegetarian => 2,
        };
    }
}
