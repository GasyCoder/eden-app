<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function complete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $request->user()->forceFill([
            'onboarding_completed_at' => now(),
        ])->save();

        return back()->with('success', 'Guide terminé.');
    }
}
