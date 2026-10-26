@php($appearance = \App\Support\Appearance::effective(auth()->user()))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full"
    @foreach ($appearance as $key => $value) data-{{ $key }}="{{ $value }}" @endforeach>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        {{-- Apply the saved light/dark mode before first paint to avoid a light flash in dark mode. --}}
        <script>
            (function () {
                try {
                    var mode = localStorage.getItem('theme') || 'system';
                    var dark = mode === 'dark' || (mode === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', dark);
                } catch (e) {}
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="h-full font-sans antialiased">
        @inertia
    </body>
</html>
