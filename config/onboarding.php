<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Preset wedding songs (YouTube)
    |--------------------------------------------------------------------------
    |
    | Shown as selectable cards during onboarding. Search filters this list.
    | Users can also paste any YouTube URL.
    |
    */

    'songs' => [
        [
            'id' => '2Vv-BfVoq4g',
            'title' => 'Perfect',
            'artist' => 'Ed Sheeran',
            'url' => 'https://www.youtube.com/watch?v=2Vv-BfVoq4g',
        ],
        [
            'id' => '450p7goxZqg',
            'title' => 'All of Me',
            'artist' => 'John Legend',
            'url' => 'https://www.youtube.com/watch?v=450p7goxZqg',
        ],
        [
            'id' => 'lp-EO5I60KA',
            'title' => 'A Thousand Years',
            'artist' => 'Christina Perri',
            'url' => 'https://www.youtube.com/watch?v=lp-EO5I60KA',
        ],
        [
            'id' => 'hT_nvWreIhg',
            'title' => 'Counting Stars',
            'artist' => 'OneRepublic',
            'url' => 'https://www.youtube.com/watch?v=hT_nvWreIhg',
        ],
        [
            'id' => 'kOkQ4T5WO9E',
            'title' => 'Thinking Out Loud',
            'artist' => 'Ed Sheeran',
            'url' => 'https://www.youtube.com/watch?v=kOkQ4T5WO9E',
        ],
        [
            'id' => 'Pkh8UtuejGw',
            'title' => 'Can\'t Help Falling in Love',
            'artist' => 'Elvis Presley',
            'url' => 'https://www.youtube.com/watch?v=Pkh8UtuejGw',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Day-plan preset chips
    |--------------------------------------------------------------------------
    */

    'schedule_presets' => [
        ['time' => '12:00', 'title' => 'Ceremony', 'label_key' => 'schedule_preset_Ceremony'],
        ['time' => '14:00', 'title' => 'Reception', 'label_key' => 'schedule_preset_Reception'],
        ['time' => '16:00', 'title' => 'Photos', 'label_key' => 'schedule_preset_Photos'],
        ['time' => '19:00', 'title' => 'Dinner', 'label_key' => 'schedule_preset_Dinner'],
        ['time' => '21:00', 'title' => 'Party', 'label_key' => 'schedule_preset_Party'],
    ],

    'draft_session_key' => 'onboarding.invitation_draft',

    'progress_session_key' => 'onboarding.progress',

];
