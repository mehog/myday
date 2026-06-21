<?php

namespace App\Models;

use App\InvitationTheme;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'groom_name',
        'bride_name',
        'wedding_date',
        'theme',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'wedding_date' => 'date',
            'theme' => InvitationTheme::class,
        ];
    }
}
