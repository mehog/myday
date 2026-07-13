<?php

namespace App\Models;

use App\InvitationTheme;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use HasFactory;

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
