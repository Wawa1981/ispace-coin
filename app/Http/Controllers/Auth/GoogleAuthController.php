<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Connexion Google non configurée (clé API manquante).']);
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth failed: ' . $e->getMessage());
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Connexion Google annulée ou échouée. Réessayez.']);
        }

        $email = $googleUser->getEmail();
        if (!$email) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google n’a pas fourni d’email.']);
        }

        $user = User::query()->where('google_id', $googleUser->getId())->first();

        if (!$user) {
            $user = User::query()->where('email', $email)->first();

            if ($user) {
                // Lier le compte existant à Google
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            } else {
                $user = User::query()->create([
                    'name' => $googleUser->getName() ?: Str::before($email, '@'),
                    'email' => $email,
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => null,
                    'email_verified_at' => now(),
                ]);
            }
        } else {
            $user->forceFill([
                'avatar' => $googleUser->getAvatar() ?: $user->avatar,
                'name' => $user->name ?: ($googleUser->getName() ?: $user->name),
            ])->save();
        }

        if (!empty($user->blocked)) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Ce compte est bloqué.']);
        }

        Auth::login($user, true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
