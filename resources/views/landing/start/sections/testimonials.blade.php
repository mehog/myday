<section class="landing-section px-6 py-20 bg-[#fafaf8]">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12 landing-fade-in">
            <p class="landing-label text-xs uppercase text-[#c9a227] mb-3">
                {{ __('start.testimonials_eyebrow') }}
            </p>
            <x-landing.headline
                :lead="__('start.testimonials_title_lead')"
                :emphasis="__('start.testimonials_title_emphasis')"
                class="text-3xl sm:text-4xl text-[#1a1208] mb-4"
            />
        </div>

        @php
            $quotes = collect(range(1, 3))
                ->map(fn (int $i): array => [
                    'quote' => trim((string) __('start.testimonial_'.$i.'_quote')),
                    'name' => trim((string) __('start.testimonial_'.$i.'_name')),
                    'meta' => trim((string) __('start.testimonial_'.$i.'_meta')),
                ])
                ->filter(fn (array $quote): bool => $quote['quote'] !== '')
                ->values();
        @endphp

        @if ($quotes->isEmpty())
            <p class="landing-body text-[#5c5246] text-center max-w-2xl mx-auto mb-10 landing-fade-in">
                {{ __('start.testimonials_empty') }}
            </p>
            <div class="grid sm:grid-cols-3 gap-6">
                @foreach (range(1, 3) as $i)
                    <blockquote class="landing-card rounded-2xl p-6 sm:p-8 border border-dashed border-[#1a1208]/15 bg-white min-h-[10rem] landing-fade-in">
                        <p class="landing-heading text-4xl text-[#c9a227] leading-none">“</p>
                    </blockquote>
                @endforeach
            </div>
        @else
            <div @class([
                'grid gap-6',
                'sm:grid-cols-2' => $quotes->count() === 2,
                'sm:grid-cols-3' => $quotes->count() >= 3,
            ])>
                @foreach ($quotes as $quote)
                    <blockquote class="landing-card rounded-2xl p-6 sm:p-8 border border-[#1a1208]/10 bg-white landing-fade-in">
                        <p class="landing-heading text-4xl text-[#c9a227] leading-none mb-4">“</p>
                        <p class="landing-body text-[#5c5246] leading-relaxed mb-6">{{ $quote['quote'] }}</p>
                        @if ($quote['name'] !== '')
                            <footer>
                                <p class="landing-heading text-base text-[#1a1208]">{{ $quote['name'] }}</p>
                                @if ($quote['meta'] !== '')
                                    <p class="landing-body text-sm text-[#5c5246]/80">{{ $quote['meta'] }}</p>
                                @endif
                            </footer>
                        @endif
                    </blockquote>
                @endforeach
            </div>
        @endif
    </div>
</section>
