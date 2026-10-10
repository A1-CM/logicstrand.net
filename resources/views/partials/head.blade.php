<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="{{ asset('favicon.png') }}" type="image/png" sizes="64x64" />

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@unless($forceLight ?? false)
    @fluxAppearance
@endunless
