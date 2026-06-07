<?php

namespace App\Http\Middleware;

use App\Support\ContentLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = ContentLocale::fromRequest($request);

        return [
            ...parent::share($request),
            'locale' => $locale,
            'locales' => ContentLocale::SUPPORTED,
            'languageUrls' => [
                'fr' => route('home'),
                'en' => route('home.en'),
            ],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'is_admin' => $request->user()->is_admin,
                    'role' => $request->user()->role,
                    'onboarding_completed_at' => $request->user()->onboarding_completed_at?->toISOString(),
                    'profile_photo_url' => $request->user()->profile_photo_path
                        ? Storage::url($request->user()->profile_photo_path)
                        : null,
                ] : null,
            ],
            'canEdit' => (bool) $request->user()?->is_admin,
            'canEditProtected' => (bool) $request->user()?->isSuperAdmin(),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
