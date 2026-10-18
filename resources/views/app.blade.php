@php($pageLocale = \App\Support\ContentLocale::fromRequest(request()))
@php($pageUrl = url()->current())
@php($pageProps = $page['props'] ?? [])
@php($pageLanguageUrls = $pageProps['languageUrls'] ?? ['fr' => route('home'), 'en' => route('home.en')])
@php($pageSeo = $pageProps['pageSeo'] ?? [])
@php($privatePage = request()->is('admin', 'admin/*', 'me', 'me/*', 'logout', 'newsletter', 'newsletter/*'))
<!DOCTYPE html>
<html lang="{{ $pageLocale }}">
    <head>
        @php($appUrl = rtrim(config('app.url'), '/'))
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            (() => {
                try {
                    const stored = window.localStorage.getItem('eden_theme');
                    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const theme = stored || (prefersDark ? 'dark' : 'light');
                    document.documentElement.dataset.theme = theme;
                    document.documentElement.style.colorScheme = theme;
                } catch (_) {
                    document.documentElement.dataset.theme = 'light';
                }
            })();
        </script>

        @php($seoTitle = filled($pageSeo['title'] ?? null) ? $pageSeo['title'].' | EDEN-UMG' : $seo['title'])
        @php($seoDescription = filled($pageSeo['description'] ?? null) ? $pageSeo['description'] : $seo['description'])
        @php($ogSource = filled($pageSeo['image'] ?? null) ? $pageSeo['image'] : $seo['ogImage'])
        @php($ogImage = \Illuminate\Support\Str::startsWith($ogSource, 'http') ? $ogSource : $appUrl.$ogSource)
        @php($heroPreload = $performance['heroImage']['preload'] ?? null)
        <title inertia>{{ $seoTitle }}</title>

        {{-- Référencement (SEO), modifiable par le superadmin --}}
        <meta name="description" content="{{ $seoDescription }}">
        <meta name="keywords" content="{{ $seo['keywords'] }}">
        <meta name="author" content="École Doctorale EDEN, Université de Mahajanga">
        <meta name="robots" content="{{ $privatePage ? 'noindex, nofollow, noarchive' : ($seo['robots'] ?? 'index, follow') }}">
        @if (! empty($seo['googleVerification']))
            <meta name="google-site-verification" content="{{ $seo['googleVerification'] }}">
        @endif
        @if (! $privatePage && ! empty($seo['analyticsId']))
            <meta name="eden-analytics" content="{{ $seo['analyticsId'] }}">
        @endif
        <meta name="theme-color" content="#FFFFFF">
        @if (! $privatePage)
        <link rel="canonical" href="{{ $pageUrl }}">
        <link rel="alternate" hreflang="fr" href="{{ $pageLanguageUrls['fr'] }}">
        <link rel="alternate" hreflang="en" href="{{ $pageLanguageUrls['en'] }}">
        <link rel="alternate" hreflang="x-default" href="{{ $pageLanguageUrls['fr'] }}">
        @endif

        {{-- Open Graph / réseaux sociaux --}}
        <meta property="og:type" content="{{ $pageSeo['type'] ?? 'website' }}">
        <meta property="og:site_name" content="École Doctorale EDEN, Université de Mahajanga">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:url" content="{{ $pageUrl }}">
        <meta property="og:locale" content="{{ $pageLocale === 'en' ? 'en_US' : 'fr_FR' }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
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
        @if (!empty($themeColors))
            <style id="eden-theme-colors">:root{@foreach ($themeColors as $var => $val){{ $var }}:{{ $val }};@endforeach}</style>
        @endif
        @inertiaHead
    </head>
    <body>
        @if (request()->is('admin', 'admin/*'))
            {{-- Skeleton initial calé sur la structure du panneau d’administration. --}}
            <div id="app-skeleton" class="fixed inset-0 z-[300] overflow-hidden bg-background transition-opacity duration-300" role="status" aria-label="Chargement de l’administration">
                <div class="absolute top-0 left-0 h-0.5 w-2/5 bg-eden-600"></div>
                <div class="grid min-h-dvh lg:grid-cols-[16rem_minmax(0,1fr)]">
                    <aside class="fixed inset-y-0 left-0 hidden w-64 flex-col gap-5 border-r bg-muted px-3 pb-4 lg:flex" aria-hidden="true">
                        <div class="-mx-3 h-1 bg-eden-600"></div>
                        <div class="flex items-center gap-2.5 px-1.5"><span class="size-9 animate-pulse rounded-lg bg-secondary"></span><span class="grid gap-2"><span class="h-3 w-12 animate-pulse rounded bg-secondary"></span><span class="h-2.5 w-24 animate-pulse rounded bg-secondary"></span></span></div>
                        <span class="h-9 animate-pulse rounded-md bg-secondary"></span>
                        <div class="grid gap-5">
                            @foreach ([2, 5, 1, 3] as $count)
                                <div class="grid gap-2"><span class="ml-2 h-2 w-16 animate-pulse rounded bg-secondary"></span>@for ($i = 0; $i < $count; $i++)<span class="flex h-9 items-center gap-2.5 rounded-md px-2.5"><span class="size-4 animate-pulse rounded bg-secondary"></span><span class="h-3 w-28 animate-pulse rounded bg-secondary"></span></span>@endfor</div>
                            @endforeach
                        </div>
                        <span class="mt-auto h-10 animate-pulse rounded-md bg-secondary"></span>
                    </aside>
                    <div class="min-w-0 lg:col-start-2">
                        <header class="flex h-14 items-center gap-3 border-b px-4 sm:px-6" aria-hidden="true"><span class="size-8 animate-pulse rounded-md bg-secondary lg:hidden"></span><span class="h-3 w-40 animate-pulse rounded bg-secondary"></span><span class="ml-auto h-8 w-24 animate-pulse rounded-md bg-secondary"></span><span class="size-8 animate-pulse rounded-md bg-secondary"></span></header>
                        <main class="mx-auto w-full max-w-7xl px-4 py-6 pb-16 sm:px-6" aria-hidden="true">
                            <div class="mb-6 flex flex-wrap items-center justify-between gap-4"><div class="grid gap-2.5"><span class="h-7 w-48 animate-pulse rounded bg-secondary"></span><span class="h-3 w-64 max-w-full animate-pulse rounded bg-secondary"></span></div><span class="h-9 w-36 animate-pulse rounded-md bg-secondary"></span></div>
                            <div class="mb-4 flex flex-wrap gap-2"><span class="h-9 w-full animate-pulse rounded-md bg-secondary sm:max-w-sm"></span><span class="h-9 w-40 animate-pulse rounded-md bg-secondary"></span><span class="h-9 w-40 animate-pulse rounded-md bg-secondary"></span></div>
                            <div class="overflow-hidden rounded-xl border bg-card"><div class="flex h-10 items-center gap-4 border-b bg-muted px-4">@for ($i = 0; $i < 5; $i++)<span class="h-3 flex-1 animate-pulse rounded bg-secondary"></span>@endfor</div>@for ($i = 0; $i < 6; $i++)<div class="flex h-14 items-center gap-4 border-b px-4 last:border-0"><span class="size-4 animate-pulse rounded bg-secondary"></span><span class="h-3 flex-[2] animate-pulse rounded bg-secondary"></span><span class="h-3 flex-1 animate-pulse rounded bg-secondary"></span><span class="h-5 w-20 animate-pulse rounded-full bg-secondary"></span><span class="h-3 w-24 animate-pulse rounded bg-secondary"></span></div>@endfor</div>
                        </main>
                    </div>
                </div>
            </div>
            <script>
                setTimeout(function () { var s = document.getElementById('app-skeleton'); if (s) { s.remove(); } }, 10000);
            </script>
        @elseif (! request()->is('me/*', 'password/reset', 'password/reset/*'))
            {{-- Skeleton de premier chargement, retiré au montage de l'application --}}
            <div id="app-skeleton" class="fixed inset-0 z-[300] overflow-hidden bg-background transition-opacity duration-300" role="status" aria-label="Chargement de la page">
                <div class="absolute top-0 left-0 h-0.5 w-2/5 bg-eden-600"></div>
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center gap-4 border-b">
                        <span class="h-9 w-36 animate-pulse rounded-lg bg-secondary"></span>
                        <span class="ml-auto hidden h-3 w-1/3 animate-pulse rounded bg-secondary md:block"></span>
                        <span class="h-9 w-28 animate-pulse rounded-md bg-secondary"></span>
                    </div>
                    <div class="grid items-center gap-10 py-16 md:grid-cols-2">
                        <div class="grid gap-4">
                            <span class="h-10 w-full animate-pulse rounded-md bg-secondary"></span>
                            <span class="h-10 w-3/4 animate-pulse rounded-md bg-secondary"></span>
                            <span class="h-3 w-11/12 animate-pulse rounded bg-secondary"></span>
                            <span class="h-3 w-2/3 animate-pulse rounded bg-secondary"></span>
                        </div>
                        <div class="aspect-[4/3] animate-pulse rounded-2xl bg-secondary"></div>
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
