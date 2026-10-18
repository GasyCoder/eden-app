<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHtmlForDocumentNavigation
{
    public function handle(Request $request, Closure $next): Response
    {
        // Après une reprise de veille, certains navigateurs restaurent l’en-tête
        // X-Inertia sur une navigation complète et affichent alors le JSON brut.
        // Une navigation document doit toujours recevoir le HTML d’amorçage Inertia.
        if (
            $request->headers->has('X-Inertia')
            && $request->header('Sec-Fetch-Mode') === 'navigate'
            && $request->header('Sec-Fetch-Dest') === 'document'
        ) {
            $request->headers->remove('X-Inertia');
        }

        return $next($request);
    }
}
