@extends('layouts.landing')

@section('content')
    @php
        $operatorAddress = $legal['operator_address']
            ?: __('legal.operator_address_default', ['website' => $legal['website_url']]);
    @endphp
    <x-legal-page :heading="__('legal.terms.heading')" :legal="$legal">
        <p>{{ __('legal.terms.intro', [
            'operator' => $legal['operator_name'],
        ]) }}</p>

        @foreach (range(1, 12) as $i)
            <div>
                <h2>{{ __('legal.terms.section_'.$i.'_title') }}</h2>
                <p>{{ __('legal.terms.section_'.$i.'_body', [
                    'operator' => $legal['operator_name'],
                    'address' => $operatorAddress,
                    'jurisdiction' => $legal['jurisdiction'],
                    'email' => $legal['support_email'],
                    'website' => $legal['website_url'],
                ]) }}</p>
                @if ($i === 9)
                    <p>
                        <a href="{{ route('legal.refund') }}">{{ __('legal.footer_refund') }}</a>
                    </p>
                @endif
            </div>
        @endforeach
    </x-legal-page>
@endsection
