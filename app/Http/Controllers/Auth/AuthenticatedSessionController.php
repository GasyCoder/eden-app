<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginOtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): Response
    {
        if ($request->session()->get('login_otp_expires_at', 0) < now()->timestamp) {
            $this->clearOtpChallenge($request);
        }

        return Inertia::render('Auth/Login', [
            'otpPending' => $request->session()->has('login_otp_user_id'),
            'otpEmail' => $request->session()->get('login_otp_email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $credentials['email'] = mb_strtolower(trim($credentials['email']));

        $this->clearOtpChallenge($request);

        if (! Auth::validate([...$credentials, 'is_admin' => true])) {
            throw ValidationException::withMessages([
                'email' => 'Les identifiants sont incorrects.',
            ]);
        }

        $user = User::query()->where('email', $credentials['email'])->where('is_admin', true)->first();

        if (! $user) {
            throw ValidationException::withMessages(['email' => 'Les identifiants sont incorrects.']);
        }

        $code = (string) random_int(0, 999999);
        $code = str_pad($code, 6, '0', STR_PAD_LEFT);
        $request->session()->regenerate();
        $request->session()->put([
            'login_otp_user_id' => $user->id,
            'login_otp_email' => $user->email,
            'login_otp_hash' => Hash::make($code),
            'login_otp_expires_at' => now()->addMinutes(10)->timestamp,
            'login_otp_attempts' => 0,
            'login_otp_remember' => $request->boolean('remember'),
        ]);

        try {
            Mail::to($user->email, $user->name)->send(new LoginOtpMail($user->name, $code));
        } catch (Throwable $exception) {
            $this->clearOtpChallenge($request);
            report($exception);

            throw ValidationException::withMessages([
                'email' => 'Le code de sécurité n’a pas pu être envoyé. Vérifiez la configuration e-mail ou réessayez plus tard.',
            ]);
        }

        return redirect()->route('login')->with('success', 'Un code de vérification a été envoyé à votre adresse e-mail.');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $userId = $request->session()->get('login_otp_user_id');

        if (! $userId || $request->session()->get('login_otp_expires_at', 0) < now()->timestamp) {
            $this->clearOtpChallenge($request);
            throw ValidationException::withMessages(['code' => 'Le code a expiré. Reconnectez-vous pour en recevoir un nouveau.']);
        }

        $attempts = (int) $request->session()->get('login_otp_attempts', 0);

        if ($attempts >= 5) {
            $this->clearOtpChallenge($request);
            throw ValidationException::withMessages(['code' => 'Trop de tentatives. Recommencez la connexion pour recevoir un nouveau code.']);
        }

        if (! Hash::check($data['code'], (string) $request->session()->get('login_otp_hash'))) {
            $request->session()->increment('login_otp_attempts');
            throw ValidationException::withMessages(['code' => 'Le code saisi est incorrect.']);
        }

        $user = User::query()->whereKey($userId)->where('is_admin', true)->first();

        if (! $user) {
            $this->clearOtpChallenge($request);
            throw ValidationException::withMessages(['code' => 'Ce compte n’est plus disponible.']);
        }

        $remember = (bool) $request->session()->get('login_otp_remember', false);
        $this->clearOtpChallenge($request);
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('login_otp_user_id');
        $user = $userId ? User::query()->whereKey($userId)->where('is_admin', true)->first() : null;

        if (! $user || $request->session()->get('login_otp_expires_at', 0) < now()->timestamp) {
            $this->clearOtpChallenge($request);
            throw ValidationException::withMessages(['email' => 'La session a expiré. Recommencez la connexion.']);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $request->session()->put([
            'login_otp_hash' => Hash::make($code),
            'login_otp_expires_at' => now()->addMinutes(10)->timestamp,
            'login_otp_attempts' => 0,
        ]);

        try {
            Mail::to($user->email, $user->name)->send(new LoginOtpMail($user->name, $code, resent: true));
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['code' => 'Le code n’a pas pu être envoyé. Réessayez plus tard.']);
        }

        return back(303)->with('success', 'Un nouveau code vient d’être envoyé.');
    }

    public function cancelOtp(Request $request): RedirectResponse
    {
        $this->clearOtpChallenge($request);

        return redirect()->route('login');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function clearOtpChallenge(Request $request): void
    {
        $request->session()->forget([
            'login_otp_user_id',
            'login_otp_email',
            'login_otp_hash',
            'login_otp_expires_at',
            'login_otp_attempts',
            'login_otp_remember',
        ]);
    }
}
