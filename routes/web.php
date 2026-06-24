<?php

use App\Actions\StorePushSubscriptionAction;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DownloadGuestPhotosController;
use App\Http\Controllers\InvitationManifestController;
use App\Http\Controllers\WeddingEventCalendarController;
use App\Livewire\GuestContactPage;
use App\Livewire\InvitationPage;
use App\Livewire\LandingPage;
use App\Livewire\Onboarding\VerifyEmailNotice;
use App\Livewire\Onboarding\WeddingOnboarding;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPage::class)->name('home');

Route::post('/lang/{locale}', function (string $locale) {
    Locale::set($locale);

    return redirect()->back();
})->name('lang.switch');

Route::get('/onboarding', WeddingOnboarding::class)->name('onboarding');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding/verify-email', VerifyEmailNotice::class)->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/app');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

Route::get('/sitemap.xml', function () {
    return response()
        ->view('sitemap')
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Disallow: /app/',
        'Disallow: /admin/',
        'Disallow: /e/*/',
        '',
        'Sitemap: '.url('/sitemap.xml'),
    ]);

    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

Route::get('/app-api/guest-messages/photos/download/{message?}', DownloadGuestPhotosController::class)
    ->middleware(['auth', 'verified'])
    ->name('guest-messages.photos.download');

Route::get('/e/{slug}/calendar.ics', WeddingEventCalendarController::class)->name('invitation.ics');
Route::get('/e/{slug}/{token}/manifest.webmanifest', InvitationManifestController::class)->name('invitation.manifest');
Route::get('/e/{slug}/{token}/contact', GuestContactPage::class)->name('invitation.contact.guest');
Route::get('/e/{slug}', InvitationPage::class)->name('invitation.show');
Route::get('/e/{slug}/{token}', InvitationPage::class)->name('invitation.guest');
Route::post('/push/subscribe/{guest:token}', StorePushSubscriptionAction::class)->name('push.subscribe');
