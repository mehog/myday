@if ($id = config('services.meta_pixel.id'))
    @production
        @php
            $event = session('meta_pixel_event');
            $eventName = is_array($event) ? ($event['name'] ?? null) : null;
            $eventParams = is_array($event) ? ($event['params'] ?? null) : null;
        @endphp
        <!-- Meta Pixel Code -->
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $id }}');
        fbq('track', 'PageView');
        @if (is_string($eventName) && $eventName !== '')
            @if (is_array($eventParams) && $eventParams !== [])
                fbq('track', @json($eventName), @json($eventParams));
            @else
                fbq('track', @json($eventName));
            @endif
        @endif
        </script>
        <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $id }}&ev=PageView&noscript=1"
        /></noscript>
        <!-- End Meta Pixel Code -->
    @endproduction
@endif
