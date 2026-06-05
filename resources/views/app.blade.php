<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="École Doctorale Écosystèmes Naturels EDEN — Université de Mahajanga, Madagascar.">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>EDEN-UMG — École Doctorale Écosystèmes Naturels</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
