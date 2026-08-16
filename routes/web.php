<?php

use App\Actions\StorePushSubscriptionAction;
use App\Actions\StoreUserPushSubscriptionAction;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DodoCheckoutController;
use App\Http\Controllers\DodoWebhookController;
use App\Http\Controllers\DownloadBrochureController;
use App\Http\Controllers\DownloadGuestPhotosController;
use App\Http\Controllers\DownloadPlaceCardsController;
use App\Http\Controllers\DownloadReferralQrCodeController;
use App\Http\Controllers\DownloadSeatingPlanPdfController;
use App\Http\Controllers\InvitationManifestController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\PackagePageController;
use App\Http\Controllers\ReferralLinkController;
use App\Http\Controllers\ReferralProgramController;
use App\Http\Controllers\WeddingEventCalendarController;
use App\Http\Middleware\EnsureCoupleUser;
use App\Livewire\Dashboard\Budget;
use App\Livewire\Dashboard\CreatePush;
use App\Livewire\Dashboard\Guests;
use App\Livewire\Dashboard\Home;
use App\Livewire\Dashboard\Messages;
use App\Livewire\Dashboard\Pricing;
use App\Livewire\Dashboard\Profile;
use App\Livewire\Dashboard\Pushes;
use App\Livewire\Dashboard\Referrals;
use App\Livewire\Dashboard\Seating;
use App\Livewire\Dashboard\Wedding;
use App\Livewire\DemoExamplesPage;
use App\Livewire\GuestContactPage;
use App\Livewire\GuestPushNotificationsPage;
use App\Livewire\InvitationPage;
use App\Livewire\LandingPage;
use App\Livewire\Onboarding\OnboardingPreview;
use App\Livewire\Onboarding\VerifyEmailNotice;
use App\Livewire\Onboarding\WeddingOnboarding;
use App\Support\DashboardNav;
use App\Support\Locale;
use App\Support\LocaleUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPage::class)->name('home');
Route::get('/demo-examples', DemoExamplesPage::class)->name('demo.examples');

Route::supportBubble();

Route::get('/referral-program', ReferralProgramController::class)->name('referral-program');

Route::get('/plans', [PackagePageController::class, 'index'])->name('packages.index');
Route::get('/plans/{tier}', [PackagePageController::class, 'show'])
    ->whereIn('tier', ['free', 'basic', 'plus', 'premium'])
    ->name('packages.show');

Route::get('/terms', LegalPageController::class)->defaults('page', 'terms')->name('legal.terms');
Route::get('/privacy', LegalPageController::class)->defaults('page', 'privacy')->name('legal.privacy');
Route::get('/refund-policy', LegalPageController::class)->defaults('page', 'refund-policy')->name('legal.refund');
Route::get('/faq', LegalPageController::class)->defaults('page', 'faq')->name('legal.faq');

Route::post('/lang/{locale}', function (string $locale) {
    if (! Locale::isSupported($locale)) {
        return redirect()->back();
    }

    $previous = url()->previous();
    $previousPath = parse_url($previous, PHP_URL_PATH) ?? '';
    $isInvitationContext = str_starts_with($previousPath, '/e/');

    Locale::set($locale, persistToUser: ! $isInvitationContext);

    return redirect()->to(LocaleUrl::withLocale($previous, $locale));
})->name('lang.switch');

Route::get('/'.(config('referral.route_prefix') ?: 'ref').'/{code}', ReferralLinkController::class)
    ->name('referral.link');

Route::get('/onboarding', WeddingOnboarding::class)->name('onboarding');
Route::get('/onboarding/preview', OnboardingPreview::class)->name('onboarding.preview');

Route::redirect('/login', '/app/login')->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding/verify-email', VerifyEmailNotice::class)->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(DashboardNav::homeUrl());
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

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
        'Disallow: /dashboard/',
        'Disallow: /onboarding/preview',
        'Disallow: /e/*/',
        '',
        'User-agent: OAI-SearchBot',
        'Allow: /',
        '',
        'User-agent: GPTBot',
        'Disallow: /',
        '',
        'Sitemap: '.url('/sitemap.xml'),
    ]);

    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

Route::post('/webhooks/dodo', DodoWebhookController::class)->name('dodo.webhook');

Route::post('/app-api/dodo/checkout', DodoCheckoutController::class)
    ->middleware(['auth', 'verified'])
    ->name('dodo.checkout');

Route::get('/app-api/guest-messages/photos/download/{message?}', DownloadGuestPhotosController::class)
    ->middleware(['auth', 'verified'])
    ->name('guest-messages.photos.download');

Route::get('/app-api/referrals/qr-code/download/{format?}', DownloadReferralQrCodeController::class)
    ->middleware(['auth', 'verified'])
    ->name('referrals.qr-code.download');

Route::get('/app-api/referrals/brochure/download', DownloadBrochureController::class)
    ->middleware(['auth', 'verified'])
    ->name('referrals.brochure.download');

Route::get('/app-api/guests/place-cards/download', DownloadPlaceCardsController::class)
    ->middleware(['auth', 'verified'])
    ->name('guests.place-cards.download');

Route::get('/app-api/seating-plan/export-pdf', DownloadSeatingPlanPdfController::class)
    ->middleware(['auth', 'verified'])
    ->name('seating-plan.export-pdf');

Route::get('/e/{slug}/calendar.ics', WeddingEventCalendarController::class)->name('invitation.ics');
Route::get('/e/{slug}/{token}/manifest.webmanifest', InvitationManifestController::class)->name('invitation.manifest');
Route::get('/e/{slug}/{token}/contact', GuestContactPage::class)->name('invitation.contact.guest');
Route::get('/e/{slug}/{token}/push', GuestPushNotificationsPage::class)->name('invitation.push.guest');
Route::get('/e/{slug}', InvitationPage::class)->name('invitation.show');
Route::get('/e/{slug}/{token}', InvitationPage::class)->name('invitation.guest');
Route::post('/push/subscribe/{guest:token}', StorePushSubscriptionAction::class)->name('push.subscribe');
Route::post('/push/user/subscribe', StoreUserPushSubscriptionAction::class)
    ->middleware(['auth', 'verified'])
    ->name('push.user.subscribe');

Route::middleware(['auth', 'verified', EnsureCoupleUser::class])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/', Home::class)->name('dashboard');
        Route::get('/wedding', Wedding::class)->name('dashboard.wedding');
        Route::get('/guests', Guests::class)->name('dashboard.guests');
        Route::get('/messages', Messages::class)->name('dashboard.messages');
        Route::get('/budget', Budget::class)->name('dashboard.budget');
        Route::get('/seating', Seating::class)->name('dashboard.seating');
        Route::get('/pushes', Pushes::class)->name('dashboard.pushes');
        Route::get('/pushes/create', CreatePush::class)->name('dashboard.pushes.create');
        Route::get('/pricing', Pricing::class)->name('dashboard.pricing');
        Route::get('/referrals', Referrals::class)->name('dashboard.referrals');
        Route::get('/profile', Profile::class)->name('dashboard.profile');
        Route::post('/logout', function (Request $request) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/');
        })->name('dashboard.logout');
    });
