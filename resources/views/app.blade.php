<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1976d2">
    <link rel="apple-touch-icon" href="/192x192.png">

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(() => console.log('Service Worker registered'))
                    .catch(err => console.log('SW registration failed:', err));
            });
        }
        
        // スキャンツール設定をLaravelの環境変数から取得
        window.SCAN_TOOL_IP = @json(env('SCAN_TOOL_IP', '192.168.210.90'));
        window.SCAN_TOOL_PORT = @json(env('SCAN_TOOL_PORT', 5001));
        window.SCAN_TOOL_PROTOCOL = @json(env('SCAN_TOOL_PROTOCOL', null));
        window.APP_ENV = @json(config('app.env', 'production'));
    </script>
    

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>