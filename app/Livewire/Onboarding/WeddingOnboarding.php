<?php

namespace App\Livewire\Onboarding;

use App\InvitationTemplate;
use App\InvitationTheme;
use App\LinkMode;
use App\Models\Referral;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Support\Locale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.onboarding')]
class WeddingOnboarding extends Component
{
    public int $step = 1;

    public string $groom_name = '';

    public string $bride_name = '';

    public string $wedding_date = '';

    public string $theme = '';

    public string $template = '';

    public string $your_name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        if (! $user->hasVerifiedEmail()) {
            $this->redirectRoute('verification.notice');

            return;
        }

        $this->redirect('/app');
    }

    public function switchLocale(string $locale): void
    {
        Locale::set($locale);
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->step), $this->messagesForStep($this->step));

        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function submit(): void
    {
        $this->validate(array_merge(
            $this->rulesForStep(1),
            $this->rulesForStep(2),
        ), array_merge(
            $this->messagesForStep(1),
            $this->messagesForStep(2),
        ));

        $user = DB::transaction(function () {
            $user = User::query()->create([
                'name' => $this->your_name,
                'email' => $this->email,
                'password' => $this->password,
                'is_admin' => false,
            ]);

            WeddingEvent::query()->create([
                'user_id' => $user->id,
                'groom_name' => $this->groom_name,
                'bride_name' => $this->bride_name,
                'slug' => $this->uniqueSlug(),
                'wedding_date' => Carbon::parse($this->wedding_date)->startOfDay(),
                'theme' => InvitationTheme::from($this->theme),
                'template' => InvitationTemplate::from($this->template),
                'link_mode' => LinkMode::Public,
                'is_active' => false,
            ]);

            $referrerId = null;
            $referralCode = Cookie::get(config('referral.cookie_name'));

            if (is_string($referralCode) && $referralCode !== '') {
                $referrer = Referral::userByReferralCode($referralCode);

                if ($referrer !== null && $referrer->id !== $user->id) {
                    $referrerId = $referrer->id;
                }
            }

            $user->createReferralAccount($referrerId);

            return $user;
        });

        Auth::login($user);
        $user->sendEmailVerificationNotification();

        $this->redirectRoute('verification.notice');
    }

    public function render()
    {
        return view('livewire.onboarding.wedding-onboarding', [
            'themes' => InvitationTheme::cases(),
            'templates' => InvitationTemplate::cases(),
            'selectedTheme' => $this->theme !== ''
                ? InvitationTheme::tryFrom($this->theme)
                : null,
            'selectedTemplate' => $this->template !== ''
                ? InvitationTemplate::tryFrom($this->template)
                : null,
        ])->title(__('onboarding.meta_title'));
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'groom_name' => ['required', 'string', 'max:255'],
                'bride_name' => ['required', 'string', 'max:255'],
                'wedding_date' => ['required', 'date', 'after:today'],
                'theme' => ['required', 'string', Rule::in(array_column(InvitationTheme::cases(), 'value'))],
                'template' => ['required', 'string', Rule::in(array_column(InvitationTemplate::cases(), 'value'))],
            ],
            2 => [
                'your_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    private function messagesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'groom_name.required' => __('onboarding.groom_name_required'),
                'bride_name.required' => __('onboarding.bride_name_required'),
                'wedding_date.required' => __('onboarding.wedding_date_required'),
                'wedding_date.after' => __('onboarding.wedding_date_future'),
                'theme.required' => __('onboarding.theme_required'),
                'template.required' => __('onboarding.template_required'),
            ],
            2 => [
                'your_name.required' => __('onboarding.your_name_required'),
                'email.required' => __('onboarding.email_required'),
                'email.email' => __('onboarding.email_invalid'),
                'email.unique' => __('onboarding.email_taken'),
                'password.required' => __('onboarding.password_required'),
                'password.min' => __('onboarding.password_min'),
                'password.confirmed' => __('onboarding.password_confirmed'),
            ],
            default => [],
        };
    }

    private function uniqueSlug(): string
    {
        $baseSlug = Str::slug($this->groom_name.'-'.$this->bride_name);
        $slug = $baseSlug;
        $counter = 1;

        while (WeddingEvent::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
