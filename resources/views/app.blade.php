<!DOCTYPE html>
<html lang="fr">
    <head>
        @php($appUrl = rtrim(config('app.url'), '/'))
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>EDEN-UMG — École Doctorale Écosystèmes Naturels</title>

        {{-- Référencement (SEO) --}}
        <meta name="description" content="École Doctorale Écosystèmes Naturels (EDEN) de l'Université de Mahajanga : formation doctorale et recherche sur la biodiversité de Madagascar — équipes d'accueil, parcours doctoral, thématiques, projets et partenariats.">
        <meta name="keywords" content="EDEN, École Doctorale, Université de Mahajanga, UMG, Écosystèmes Naturels, doctorat Madagascar, recherche biodiversité, thèse, Boeny, formation doctorale, écologie, conservation, récifs coralliens, mangroves">
        <meta name="author" content="École Doctorale EDEN — Université de Mahajanga">
        <meta name="robots" content="index, follow">
        <meta name="theme-color" content="#0F5132">
        <link rel="canonical" href="{{ $appUrl }}/">

        {{-- Open Graph / réseaux sociaux --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="École Doctorale EDEN — Université de Mahajanga">
        <meta property="og:title" content="École Doctorale EDEN — Écosystèmes Naturels">
        <meta property="og:description" content="Formation doctorale et recherche sur la biodiversité de Madagascar, à l'Université de Mahajanga (Région Boeny).">
        <meta property="og:url" content="{{ $appUrl }}/">
        <meta property="og:locale" content="fr_FR">
        <meta property="og:image" content="{{ $appUrl }}/icons/og-image.png">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="École Doctorale EDEN — Écosystèmes Naturels">
        <meta name="twitter:description" content="Formation doctorale et recherche sur la biodiversité de Madagascar, Université de Mahajanga.">
        <meta name="twitter:image" content="{{ $appUrl }}/icons/og-image.png">

        {{-- PWA --}}
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32.png">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="EDEN-UMG">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
