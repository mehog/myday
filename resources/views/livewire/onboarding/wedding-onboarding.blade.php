<div class="min-h-screen flex flex-col">
    <header class="border-b border-white/5 bg-[#1a1208]/80 backdrop-blur-md">
        <div class="max-w-2xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="landing-heading text-xl font-semibold text-[#faf6ee]">
                {{ config('app.name', 'NasDan') }}
            </a>
            <div class="flex items-center gap-1 text-sm">
                <button
                    type="button"
                    wire:click="switchLocale('bs')"
                    class="px-2 py-1 rounded transition {{ app()->getLocale() === 'bs' ? 'text-[#c9a227] font-semibold' : 'text-[#d4c4a8] hover:text-[#c9a227]' }}"
                >
                    BS
                </button>
                <span class="text-[#d4c4a8]/50">|</span>
                <button
                    type="button"
                    wire:click="switchLocale('en')"
                    class="px-2 py-1 rounded transition {{ app()->getLocale() === 'en' ? 'text-[#c9a227] font-semibold' : 'text-[#d4c4a8] hover:text-[#c9a227]' }}"
                >
                    EN
                </button>
            </div>
        </div>
    </header>

    <main class="flex-1 px-6 py-10">
        <div class="max-w-2xl mx-auto landing-fade-in">
            {{-- Progress --}}
            <div class="mb-10">
                <div class="flex items-center justify-between mb-3">
                    @foreach ([1 => __('onboarding.step_couple'), 2 => __('onboarding.step_account'), 3 => __('onboarding.step_review')] as $stepNumber => $stepLabel)
                        <div class="flex flex-col items-center flex-1 {{ $stepNumber < 3 ? 'relative' : '' }}">
                            @if ($stepNumber < 3)
                                <div class="absolute top-4 left-1/2 w-full h-px {{ $step > $stepNumber ? 'bg-[#c9a227]' : 'bg-white/10' }}"></div>
                            @endif
                            <div @class([
                                'relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium border-2 transition',
                                'bg-[#c9a227] border-[#c9a227] text-[#1a1208]' => $step >= $stepNumber,
                                'bg-[#1a1208] border-white/20 text-[#d4c4a8]' => $step < $stepNumber,
                            ])>
                                {{ $stepNumber }}
                            </div>
                            <span @class([
                                'mt-2 text-xs text-center hidden sm:block',
                                'text-[#c9a227]' => $step >= $stepNumber,
                                'text-[#d4c4a8]' => $step < $stepNumber,
                            ])>
                                {{ $stepLabel }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Step 1: Couple info --}}
            @if ($step === 1)
                <div>
                    <h1 class="landing-heading text-3xl sm:text-4xl font-semibold text-[#faf6ee] mb-3">
                        {{ __('onboarding.couple_title') }}
                    </h1>
                    <p class="landing-body text-[#d4c4a8] mb-8">
                        {{ __('onboarding.couple_subtitle') }}
                    </p>

                    <form wire:submit="nextStep" class="space-y-5">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="groom_name" class="block text-sm text-[#d4c4a8] mb-2">{{ __('onboarding.groom_name') }} *</label>
                                <input id="groom_name" type="text" wire:model="groom_name" class="landing-input w-full">
                                @error('groom_name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="bride_name" class="block text-sm text-[#d4c4a8] mb-2">{{ __('onboarding.bride_name') }} *</label>
                                <input id="bride_name" type="text" wire:model="bride_name" class="landing-input w-full">
                                @error('bride_name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="wedding_date" class="block text-sm text-[#d4c4a8] mb-2">{{ __('onboarding.wedding_date') }} *</label>
                                <input id="wedding_date" type="date" wire:model="wedding_date" class="landing-input w-full">
                                @error('wedding_date') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="theme" class="block text-sm text-[#d4c4a8] mb-2">{{ __('onboarding.theme') }} *</label>
                                <select id="theme" wire:model="theme" class="landing-input w-full">
                                    <option value="">{{ __('onboarding.theme_placeholder') }}</option>
                                    @foreach ($themes as $themeOption)
                                        <option value="{{ $themeOption->value }}">{{ $themeOption->label() }}</option>
                                    @endforeach
                                </select>
                                @error('theme') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <button type="submit" class="w-full landing-btn-primary py-4 rounded-xl landing-heading text-lg transition">
                            {{ __('onboarding.next') }}
                        </button>
                    </form>
                </div>
            @endif

            {{-- Step 2: Account --}}
            @if ($step === 2)
                <div>
                    <h1 class="landing-heading text-3xl sm:text-4xl font-semibold text-[#faf6ee] mb-3">
                        {{ __('onboarding.account_title') }}
                    </h1>
                    <p class="landing-body text-[#d4c4a8] mb-8">
                        {{ __('onboarding.account_subtitle') }}
                    </p>

                    <form wire:submit="nextStep" class="space-y-5">
                        <div>
                            <label for="your_name" class="block text-sm text-[#d4c4a8] mb-2">{{ __('onboarding.your_name') }} *</label>
                            <input id="your_name" type="text" wire:model="your_name" class="landing-input w-full">
                            @error('your_name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm text-[#d4c4a8] mb-2">{{ __('onboarding.email') }} *</label>
                            <input id="email" type="email" wire:model="email" class="landing-input w-full">
                            @error('email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="password" class="block text-sm text-[#d4c4a8] mb-2">{{ __('onboarding.password') }} *</label>
                                <input id="password" type="password" wire:model="password" class="landing-input w-full">
                                @error('password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-sm text-[#d4c4a8] mb-2">{{ __('onboarding.password_confirmation') }} *</label>
                                <input id="password_confirmation" type="password" wire:model="password_confirmation" class="landing-input w-full">
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="button" wire:click="previousStep" class="flex-1 landing-btn-secondary py-4 rounded-xl landing-heading text-lg transition">
                                {{ __('onboarding.back') }}
                            </button>
                            <button type="submit" class="flex-1 landing-btn-primary py-4 rounded-xl landing-heading text-lg transition">
                                {{ __('onboarding.next') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Step 3: Review --}}
            @if ($step === 3)
                <div>
                    <h1 class="landing-heading text-3xl sm:text-4xl font-semibold text-[#faf6ee] mb-3">
                        {{ __('onboarding.review_title') }}
                    </h1>
                    <p class="landing-body text-[#d4c4a8] mb-8">
                        {{ __('onboarding.review_subtitle') }}
                    </p>

                    <div class="space-y-4 mb-8">
                        <div class="rounded-xl border border-white/10 bg-[#2a1f0f] p-5">
                            <h2 class="text-sm uppercase tracking-wider text-[#c9a227] mb-3">{{ __('onboarding.review_couple') }}</h2>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-[#d4c4a8]">{{ __('onboarding.groom_name') }}</dt>
                                    <dd class="text-[#faf6ee] text-right">{{ $groom_name }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-[#d4c4a8]">{{ __('onboarding.bride_name') }}</dt>
                                    <dd class="text-[#faf6ee] text-right">{{ $bride_name }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-[#d4c4a8]">{{ __('onboarding.review_wedding_date') }}</dt>
                                    <dd class="text-[#faf6ee] text-right">{{ $wedding_date }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-[#d4c4a8]">{{ __('onboarding.review_theme') }}</dt>
                                    <dd class="text-[#faf6ee] text-right">{{ $selectedTheme?->label() }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-[#2a1f0f] p-5">
                            <h2 class="text-sm uppercase tracking-wider text-[#c9a227] mb-3">{{ __('onboarding.review_account') }}</h2>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-[#d4c4a8]">{{ __('onboarding.your_name') }}</dt>
                                    <dd class="text-[#faf6ee] text-right">{{ $your_name }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-[#d4c4a8]">{{ __('onboarding.email') }}</dt>
                                    <dd class="text-[#faf6ee] text-right">{{ $email }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" wire:click="previousStep" class="flex-1 landing-btn-secondary py-4 rounded-xl landing-heading text-lg transition">
                            {{ __('onboarding.back') }}
                        </button>
                        <button
                            type="button"
                            wire:click="submit"
                            wire:loading.attr="disabled"
                            class="flex-1 landing-btn-primary py-4 rounded-xl landing-heading text-lg transition disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="submit">{{ __('onboarding.submit') }}</span>
                            <span wire:loading wire:target="submit">{{ __('onboarding.submitting') }}</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
