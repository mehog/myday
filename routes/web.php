<?php

use App\Livewire\InvitationPage;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/e/{slug}', InvitationPage::class)->name('invitation.show');
Route::get('/e/{slug}/{token}', InvitationPage::class)->name('invitation.guest');
