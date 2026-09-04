<div class="min-h-screen flex flex-col">
    <header class="border-b border-[#1a1208]/10 bg-white/80 backdrop-blur-md">
        <div class="max-w-2xl mx-auto px-6 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="landing-heading text-xl font-semibold text-[#1a1208]">
                {{ config('app.name') }}
            </a>
            <x-locale-picker compact />
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-6 py-10">
        <div class="max-w-md w-full landing-fade-in">
            @if ($errorMessage)
                <div class="text-center">
                    <h1 class="landing-heading text-3xl font-semibold text-[#1a1208] mb-4">{{ __('dashboard.partner_invite_title') }}</h1>
                    <p class="landing-body text-[#5c5246]">{{ $errorMessage }}</p>
                </div>
            @elseif ($wedding)
                <div class="text-center mb-8">
                    <h1 class="landing-heading text-3xl font-semibold text-[#1a1208] mb-3">{{ __('dashboard.partner_invite_title') }}</h1>
                    <p class="landing-body text-[#5c5246]">
                        {{ __('dashboard.partner_invite_intro', [
                            'inviter' => $inviter?->name ?? config('app.name'),
                            'couple' => $wedding->couple_names,
                        ]) }}
                    </p>
                </div>

                @if ($mode === 'accept' && auth()->check())
                    <div class="space-y-4">
                        <p class="text-sm text-[#5c5246] text-center">{{ __('dashboard.partner_invite_logged_in_as', ['email' => auth()->user()->email]) }}</p>
                        <button type="button" wire:click="accept" wire:loading.attr="disabled" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition disabled:opacity-50">
                            {{ __('dashboard.partner_invite_accept') }}
                        </button>
                        <button type="button" wire:click="logout" class="w-full landing-btn-secondary py-4 rounded-xl landing-heading text-lg transition">
                            {{ __('dashboard.partner_invite_use_other_account') }}
                        </button>
                    </div>
                @else
                    <div class="mb-6 flex gap-2">
                        <button type="button" wire:click="setMode('register')" @class([
                            'flex-1 rounded-xl py-2 text-sm font-medium transition',
                            'bg-[#c9a227] text-[#1a1208]' => $mode === 'register',
                            'bg-[#1a1208]/5 text-[#5c5246]' => $mode !== 'register',
                        ])>{{ __('dashboard.partner_invite_register') }}</button>
                        <button type="button" wire:click="setMode('login')" @class([
                            'flex-1 rounded-xl py-2 text-sm font-medium transition',
                            'bg-[#c9a227] text-[#1a1208]' => $mode === 'login',
                            'bg-[#1a1208]/5 text-[#5c5246]' => $mode !== 'login',
                        ])>{{ __('dashboard.partner_invite_login') }}</button>
                    </div>

                    @if ($mode === 'register')
                        <form wire:submit="register" class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-[#1a1208]">{{ __('dashboard.profile_name') }}</label>
                                <input type="text" wire:model="name" class="w-full rounded-xl border border-[#1a1208]/15 bg-white px-4 py-3 text-sm" required>
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-[#1a1208]">{{ __('dashboard.profile_email') }}</label>
                                <input type="email" wire:model="email" @disabled(filled($invite?->email)) class="w-full rounded-xl border border-[#1a1208]/15 bg-white px-4 py-3 text-sm disabled:opacity-70" required>
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-[#1a1208]">{{ __('dashboard.partner_invite_password') }}</label>
                                <input type="password" wire:model="password" class="w-full rounded-xl border border-[#1a1208]/15 bg-white px-4 py-3 text-sm" required>
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-[#1a1208]">{{ __('dashboard.profile_password_confirmation') }}</label>
                                <input type="password" wire:model="password_confirmation" class="w-full rounded-xl border border-[#1a1208]/15 bg-white px-4 py-3 text-sm" required>
                            </div>
                            <button type="submit" wire:loading.attr="disabled" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition disabled:opacity-50">
                                {{ __('dashboard.partner_invite_register_and_join') }}
                            </button>
                        </form>
                    @else
                        <form wire:submit="login" class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-[#1a1208]">{{ __('dashboard.profile_email') }}</label>
                                <input type="email" wire:model="email" @disabled(filled($invite?->email)) class="w-full rounded-xl border border-[#1a1208]/15 bg-white px-4 py-3 text-sm disabled:opacity-70" required>
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-[#1a1208]">{{ __('dashboard.partner_invite_password') }}</label>
                                <input type="password" wire:model="password" class="w-full rounded-xl border border-[#1a1208]/15 bg-white px-4 py-3 text-sm" required>
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" wire:loading.attr="disabled" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition disabled:opacity-50">
                                {{ __('dashboard.partner_invite_login_and_join') }}
                            </button>
                        </form>
                    @endif
                @endif
            @endif
        </div>
    </main>

    <x-onboarding-footer />
</div>
