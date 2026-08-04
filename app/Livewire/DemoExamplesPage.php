<?php

namespace App\Livewire;

use App\Support\DemoInvitationExamples;
use App\Support\DemoInvitationUrl;
use App\Support\Locale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.landing')]
class DemoExamplesPage extends Component
{
    public function switchLocale(string $locale): void
    {
        Locale::set($locale);
    }

    public function render()
    {
        $locale = app()->getLocale();
        $host = DemoInvitationUrl::resolveDemoHost($locale);

        $examples = array_map(
            fn (array $example): array => DemoInvitationUrl::fromExample(
                $example,
                $host['slug'],
                $locale,
                $host['guestToken'],
            ),
            DemoInvitationExamples::gallery(),
        );

        return view('livewire.demo-examples-page', [
            'examples' => $examples,
        ])
            ->title(__('landing.demo_gallery_title'))
            ->layoutData([
                'pageTitle' => __('landing.demo_gallery_title'),
                'pageDescription' => __('landing.demo_gallery_subtitle'),
                'canonicalUrl' => url('/demo-examples'),
            ]);
    }
}
