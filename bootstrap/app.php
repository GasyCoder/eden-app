<?php

use App\Http\Middleware\ConcealPrivateRoutes;
use App\Http\Middleware\EnsureHtmlForDocumentNavigation;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ConcealPrivateRoutes::class,
            EnsureHtmlForDocumentNavigation::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->appendToPriorityList(StartSession::class, ConcealPrivateRoutes::class);

        // Désinscription « en un clic » envoyée par les messageries (en-tête List-Unsubscribe-Post)
        $middleware->validateCsrfTokens(except: ['newsletter/desabonnement/*']);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            if ($response->getStatusCode() !== 404) {
                return $response;
            }

            $headers = ['Cache-Control' => 'private, no-store, max-age=0', 'X-Robots-Tag' => 'noindex, nofollow, noarchive', 'Referrer-Policy' => 'no-referrer'];

            return ($request->is('api/*') || ($request->wantsJson() && ! $request->header('X-Inertia')))
                ? response()->json(['message' => 'Not Found.'], 404, $headers)
                : response()->view('errors.404', [], 404, $headers);
        });
    })->create();
