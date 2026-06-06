<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\SeoSettings;
use App\Support\ThemeSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $editors = $request->user()?->isSuperAdmin()
            ? User::query()
                ->where('role', 'editor')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'created_at'])
                ->map(fn (User $editor): array => [
                    'id' => $editor->id,
                    'name' => $editor->name,
                    'email' => $editor->email,
                    'created_at' => $editor->created_at?->format('d/m/Y'),
                ])
                ->values()
            : [];

        return Inertia::render('Auth/Profile', [
            'seo' => SeoSettings::all(),
            'theme' => [
                'sections' => ThemeSettings::SECTIONS,
                'colors' => ThemeSettings::all(),
            ],
            'editors' => $editors,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
        ]);

        $request->user()->update($data);

        return back(303)->with('success', 'Profil mis à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back(303)->with('success', 'Mot de passe mis à jour.');
    }

    public function storeEditor(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Réservé au superadmin.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => true,
            'role' => 'editor',
        ]);

        return back(303)->with('success', 'Éditeur créé.');
    }
}
