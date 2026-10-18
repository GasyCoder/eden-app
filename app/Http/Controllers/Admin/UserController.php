<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Paginated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAdmin($request);

        return Inertia::render('Admin/Users/Index', [
            'users' => Paginated::from(User::query()->orderBy('name')->paginate(15), fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role === 'superadmin' ? 'admin' : 'editor',
                'email_verified' => $user->email_verified_at !== null,
                'invitation_pending' => $user->invitation_token !== null,
                'created_at' => $user->created_at?->format('d/m/Y'),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'lowercase', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(['admin', 'editor'])],
        ]);

        $token = Str::random(64);
        $user = User::create([
            'name' => $data['name'],
            'email' => mb_strtolower($data['email']),
            'password' => Hash::make(Str::random(64)),
            'is_admin' => true,
            'role' => $data['role'] === 'admin' ? 'superadmin' : 'editor',
        ]);
        $user->forceFill([
            'invitation_token' => hash('sha256', $token),
            'invitation_expires_at' => now()->addHours(48),
        ])->save();

        $this->sendInvitation($user, $token);

        return back(303)->with('success', 'Invitation envoyée à '.$user->email.'.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'lowercase', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'editor'])],
        ]);
        $newRole = $data['role'] === 'admin' ? 'superadmin' : 'editor';

        if ($request->user()->is($user) && $newRole !== 'superadmin') {
            throw ValidationException::withMessages(['role' => 'Vous ne pouvez pas retirer votre propre rôle Admin.']);
        }

        if ($user->isSuperAdmin() && $newRole !== 'superadmin' && User::query()->where('role', 'superadmin')->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'Il doit rester au moins un compte Admin.']);
        }

        $emailChanged = $user->email !== mb_strtolower($data['email']);
        $user->forceFill([
            'name' => $data['name'],
            'email' => mb_strtolower($data['email']),
            'role' => $newRole,
            'is_admin' => true,
            ...($emailChanged ? [
                'email_verified_at' => null,
                'invitation_token' => null,
                'invitation_expires_at' => null,
            ] : []),
        ])->save();

        if ($emailChanged) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back(303)->with('success', $emailChanged
            ? 'Informations mises à jour. Un lien de confirmation et de choix du mot de passe a été envoyé à la nouvelle adresse.'
            : 'Informations et rôle mis à jour.');
    }

    public function sendPasswordReset(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back(303)->with('error', 'Le lien ne peut pas encore être renvoyé. Veuillez patienter avant de réessayer.');
        }

        return back(303)->with('success', 'Lien de réinitialisation envoyé à '.$user->email.'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_if($request->user()->is($user), 422, 'Vous ne pouvez pas supprimer votre propre compte.');

        if ($user->isSuperAdmin() && User::query()->where('role', 'superadmin')->count() <= 1) {
            throw ValidationException::withMessages(['user' => 'Il doit rester au moins un compte Admin.']);
        }

        $user->delete();

        return back(303)->with('success', 'Utilisateur supprimé.');
    }

    public function acceptForm(string $token): Response
    {
        $user = $this->invitedUser($token);

        return Inertia::render('Auth/AcceptInvitation', [
            'token' => $token,
            'email' => $user?->email,
            'valid' => $user !== null,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'confirmed', 'string', 'min:12'],
        ]);
        $user = $this->invitedUser($token);

        if (! $user) {
            throw ValidationException::withMessages(['token' => 'Ce lien d’invitation est expiré ou déjà utilisé.']);
        }

        $user->forceFill([
            'password' => $data['password'],
            'email_verified_at' => now(),
            'invitation_token' => null,
            'invitation_expires_at' => null,
        ])->save();

        return redirect()->route('login')->with('success', 'Votre adresse est confirmée. Vous pouvez vous connecter.');
    }

    public function showPasswordReset(string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => request()->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'string', 'min:12'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'email_verified_at' => now(),
                    'invitation_token' => null,
                    'invitation_expires_at' => null,
                ])->setRememberToken(Str::random(60));
                $user->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'Le lien est invalide ou expiré. Demandez un nouvel e-mail de réinitialisation.']);
        }

        return redirect()->route('login')->with('success', 'Mot de passe réinitialisé. Vous pouvez vous connecter.');
    }

    private function invitedUser(string $token): ?User
    {
        return User::query()
            ->where('invitation_token', hash('sha256', $token))
            ->where('invitation_expires_at', '>', now())
            ->first();
    }

    private function sendInvitation(User $user, string $token): void
    {
        $url = route('invitation.accept.form', ['token' => $token]);
        $role = $user->isSuperAdmin() ? 'Admin' : 'Éditeur';
        Mail::raw("Bonjour {$user->name},\n\nUn compte {$role} EDEN a été créé pour vous. Confirmez votre adresse et choisissez votre mot de passe via ce lien (valable 48 heures) :\n\n{$url}\n\nSi vous n’attendiez pas cette invitation, ignorez ce message.", function ($message) use ($user): void {
            $message->to($user->email)->subject('Votre invitation à EDEN-UMG');
        });
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Réservé aux Admins.');
    }
}
