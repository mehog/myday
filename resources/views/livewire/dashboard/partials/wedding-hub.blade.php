<div class="space-y-5">
    @if ($wedding)
        <div class="flex justify-end">
            <x-dashboard.button variant="secondary" href="{{ $wedding->public_url }}" target="_blank" rel="noopener noreferrer">
                <x-dashboard.icon name="external" class="h-4 w-4" />
                {{ __('dashboard.wedding_preview') }}
            </x-dashboard.button>
        </div>
    @endif

    @foreach ($hubGroups as $group)
        <x-dashboard.list-group :title="$group['title']">
            @foreach ($group['items'] as $item)
                <x-dashboard.nav-row :item="$item" />
            @endforeach
        </x-dashboard.list-group>
    @endforeach
</div>
