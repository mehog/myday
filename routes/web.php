<?php

use App\Livewire\LandingPage;
use App\Livewire\InvitationPage;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPage::class)->name('home');

Route::get('/e/{slug}', InvitationPage::class)->name('invitation.show');
Route::get('/e/{slug}/{token}', InvitationPage::class)->name('invitation.guest');
