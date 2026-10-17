<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConcealPrivateRoutes
{
    public function handle(Request $request, Closure $next): Response
    {
        $private = $request->is('admin', 'admin/*', 'me', 'me/*', 'logout');
        $sensitive = $private || $request->is('newsletter', 'newsletter/*');

        // Aucune redirection vers la connexion, même sur un POST ou un identifiant inconnu.
        $loginFlow = $request->routeIs(
            'login',
            'login.otp.verify',
            'login.otp.resend',
            'login.otp.cancel',
        );

        if ($private && ! $loginFlow) {
            abort_unless($request->user()?->is_admin, 404);
        }

        $response = $next($request);

        if ($sensitive || $request->user()) {
            $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        }

        if ($sensitive) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
            $response->headers->set('Referrer-Policy', 'no-referrer');
        }

        return $response;
    }
}
