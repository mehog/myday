@extends('layouts.landing')

@section('content')
    <x-legal-page :heading="__('legal.refund.heading')" :legal="$legal">
        <p>{{ __('legal.refund.intro', [
            'operator' => $legal['operator_name'],
            'email' => $legal['support_email'],
        ]) }}</p>

        @foreach (range(1, 6) as $i)
            <div>
                <h2>{{ __('legal.refund.section_'.$i.'_title') }}</h2>
                <p>{{ __('legal.refund.section_'.$i.'_body', [
                    'days' => $legal['refund_window_days'],
                    'email' => $legal['support_email'],
                ]) }}</p>
            </div>
        @endforeach
    </x-legal-page>
@endsection
