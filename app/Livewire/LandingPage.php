<?php

namespace App\Livewire;

use App\Models\WeddingEvent;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.landing')]
class LandingPage extends Component
{
    public function mount(): void
    {
        app()->setLocale('bs');
    }

    public function render()
    {
        $demos = $this->loadDemos();

        return view('livewire.landing-page', compact('demos'))
            ->title(__('landing.meta_title'));
    }

    private function loadDemos(): array
    {
        $configs = [
            'islamic' => ['slug' => 'demo-islamsko'],
            'christian' => ['slug' => 'demo-krscansko'],
        ];

        $demos = [];

        foreach ($configs as $key => $config) {
            $event = WeddingEvent::query()
                ->where('slug', $config['slug'])
                ->where('is_active', true)
                ->with(['guests' => fn ($q) => $q->limit(1)])
                ->first();

            if (! $event) {
                continue;
            }

            $guest = $event->guests->first();

            $demos[$key] = [
                'slug' => $event->slug,
                'couple' => $event->couple_names,
                'theme' => $event->theme->label(),
                'publicUrl' => route('invitation.show', $event->slug),
                'personalUrl' => $guest
                    ? route('invitation.guest', [$event->slug, $guest->token])
                    : null,
            ];
        }

        return $demos;
    }
}
