@extends('layouts.landing')

@section('content')
    @php
        $operatorAddress = $legal['operator_address']
            ?: __('legal.operator_address_default', ['website' => $legal['website_url']]);
    @endphp
    <x-legal-page :heading="__('legal.privacy.heading')" :legal="$legal">
        <p>{{ __('legal.privacy.intro', [
            'operator' => $legal['operator_name'],
            'email' => $legal['support_email'],
        ]) }}</p>

        @foreach (range(1, 11) as $i)
            <div>
                <h2>{{ __('legal.privacy.section_'.$i.'_title') }}</h2>
                <p>{{ __('legal.privacy.section_'.$i.'_body', [
                    'operator' => $legal['operator_name'],
                    'address' => $operatorAddress,
                    'email' => $legal['support_email'],
                    'website' => $legal['website_url'],
                    'months' => $legal['data_retention_months'],
                ]) }}</p>
            </div>
        @endforeach
    </x-legal-page>
@endsection
