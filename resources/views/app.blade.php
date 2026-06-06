<!DOCTYPE html>
<html lang="fr">
    <head>
        @php($appUrl = rtrim(config('app.url'), '/'))
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            (() => {
                try {
                    const stored = window.localStorage.getItem('eden_theme');
                    const theme = stored || 'light';
                    document.documentElement.dataset.theme = theme;
                    document.documentElement.style.colorScheme = theme;
                } catch (_) {
                    document.documentElement.dataset.theme = 'light';
                }
            })();
        </script>

        @php($ogImage = \Illuminate\Support\Str::startsWith($seo['ogImage'], 'http') ? $seo['ogImage'] : $appUrl.$seo['ogImage'])
        @php($heroPreload = $performance['heroImage']['preload'] ?? null)
        <title inertia>{{ $seo['title'] }}</title>

        {{-- Référencement (SEO) — modifiable par le superadmin --}}
        <meta name="description" content="{{ $seo['description'] }}">
        <meta name="keywords" content="{{ $seo['keywords'] }}">
        <meta name="author" content="École Doctorale EDEN — Université de Mahajanga">
        <meta name="robots" content="index, follow">
        <meta name="theme-color" content="#0F5132">
        <link rel="canonical" href="{{ $appUrl }}/">

        {{-- Open Graph / réseaux sociaux --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="École Doctorale EDEN — Université de Mahajanga">
        <meta property="og:title" content="{{ $seo['title'] }}">
        <meta property="og:description" content="{{ $seo['description'] }}">
        <meta property="og:url" content="{{ $appUrl }}/">
        <meta property="og:locale" content="fr_FR">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seo['title'] }}">
        <meta name="twitter:description" content="{{ $seo['description'] }}">
        <meta name="twitter:image" content="{{ $ogImage }}">

        {{-- PWA & favicon (favicon modifiable par le superadmin) --}}
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="{{ $seo['favicon'] }}" sizes="any">
        <link rel="apple-touch-icon" href="/pwa/icon-180.png">
        @if($heroPreload)
            <link
                rel="preload"
                as="image"
                href="{{ $heroPreload['href'] }}"
                type="{{ $heroPreload['type'] }}"
                imagesrcset="{{ $heroPreload['srcset'] }}"
                imagesizes="{{ $heroPreload['sizes'] }}"
                fetchpriority="high"
            >
        @endif
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="EDEN-UMG">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body>
        @if (request()->routeIs('home'))
            {{-- Skeleton de premier chargement (retiré au montage de l'app) — réutilise les styles du skeleton de navigation --}}
            <div id="app-skeleton" class="eden-page-loading" role="status" aria-label="Chargement de la page">
                <div class="eden-page-loading-bar" style="width: 42%"></div>
                <div class="eden-page-loading-shell">
                    <div class="eden-page-loading-header">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="eden-page-loading-hero">
                        <div>
                            <span class="eden-page-loading-line short"></span>
                            <span class="eden-page-loading-title"></span>
                            <span class="eden-page-loading-title narrow"></span>
                            <span class="eden-page-loading-line"></span>
                            <span class="eden-page-loading-line medium"></span>
                        </div>
                        <div class="eden-page-loading-media"></div>
                    </div>
                    <div class="eden-page-loading-grid">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
            <script>
                // Filet de sécurité : si le JS de l'app ne se charge pas, on retire le skeleton.
                setTimeout(function () {
                    var s = document.getElementById('app-skeleton');
                    if (s) { s.remove(); }
                }, 10000);
            </script>
        @endif
        @inertia
    </body>
</html>
