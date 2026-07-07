<?php

namespace App;

enum GuestMessageVisitMatch: string
{
    case Match = 'match';
    case Soft = 'soft';
    case Mismatch = 'mismatch';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Match => __('app.guest_messages_visit_match'),
            self::Soft => __('app.guest_messages_visit_soft'),
            self::Mismatch => __('app.guest_messages_visit_mismatch'),
            self::Unknown => __('app.guest_messages_visit_unknown'),
        };
    }

    public function adminLabel(): string
    {
        return match ($this) {
            self::Match => 'Verified',
            self::Soft => 'Soft match',
            self::Mismatch => 'Different device',
            self::Unknown => 'Unknown',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Match => 'success',
            self::Soft => 'warning',
            self::Mismatch => 'danger',
            self::Unknown => 'gray',
        };
    }
}
