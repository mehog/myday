@extends('layouts.landing')

@section('content')
    <x-legal-page :heading="__('legal.faq.heading')" :legal="$legal">
        <p>{{ __('legal.faq.intro') }}</p>

        @foreach (range(1, 10) as $i)
            <div>
                <h2>{{ __('legal.faq.q'.$i) }}</h2>
                <p>{{ __('legal.faq.a'.$i, [
                    'days' => $legal['refund_window_days'],
                    'email' => $legal['support_email'],
                ]) }}</p>
                @if ($i === 8)
                    <p>
                        <a href="{{ route('legal.refund') }}">{{ __('legal.footer_refund') }}</a>
                    </p>
                @elseif ($i === 9)
                    <p>
                        <a href="{{ route('legal.privacy') }}">{{ __('legal.footer_privacy') }}</a>
                    </p>
                @endif
            </div>
        @endforeach
    </x-legal-page>
@endsection
