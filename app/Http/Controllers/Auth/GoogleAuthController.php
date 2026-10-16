<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController
{
    public function redirect(): SymfonyRedirectResponse
    {
        if (! $this->isConfigured()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in is not available yet.',
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function link(Request $request): SymfonyRedirectResponse
    {
        if (! $this->isConfigured()) {
            return back()->withErrors([
                'email' => 'Google sign-in is not available yet.',
            ]);
        }

        $request->session()->put('google_link_user_id', $request->user()->id);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $linkUserId = $request->session()->pull('google_link_user_id');

        try {
            if (! $this->isConfigured()) {
                return $this->loginError('Google sign-in is not available yet.');
            }

            /** @var GoogleUser $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            Log::notice('Google OAuth callback could not be completed.', [
                'exception' => $exception::class,
            ]);

            return $this->loginError('Google sign-in could not be completed. Please try again.');
        }

        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $googleId = (string) $googleUser->getId();
        $emailVerified = filter_var($googleUser->getRaw()['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $emailVerified || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || $googleId === '') {
            return $this->loginError('Google did not provide a verified email address for this account.');
        }

        if ($linkUserId !== null) {
            return $this->linkGoogleAccount($request, $googleId, (int) $linkUserId);
        }

        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        $user = User::query()->where('google_id', $googleId)->first();

        if (! $user) {
            $emailOwner = User::query()->whereRaw('LOWER(email) = ?', [$email])->exists();

            if ($emailOwner) {
                return $this->loginError('An account already uses this email. Sign in with that account, then connect Google in Security settings.');
            }

            try {
                $user = DB::transaction(function () use ($googleUser, $email, $googleId): User {
                    $user = User::create([
                        'name' => trim((string) $googleUser->getName()) ?: Str::before($email, '@'),
                        'email' => $email,
                        'password' => Str::random(64),
                    ]);

                    $user->forceFill([
                        'google_id' => $googleId,
                        'email_verified_at' => now(),
                    ])->save();

                    return $user;
                });
            } catch (QueryException) {
                // Another callback may have created this Google identity at the same time.
                $user = User::query()->where('google_id', $googleId)->first();

                if (! $user) {
                    return $this->loginError('This Google account could not be connected. Please try again.');
                }
            }
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            $request->session()->put([
                'login.id' => $user->id,
                'login.remember' => false,
            ]);

            return redirect()->route('two-factor.login');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function linkGoogleAccount(Request $request, string $googleId, int $userId): RedirectResponse
    {
        $user = $request->user();

        if (! $user || $user->id !== $userId) {
            return $this->loginError('Your session expired before Google could be connected. Please sign in and try again.');
        }

        $existingLink = User::query()->where('google_id', $googleId)->first();

        if ($existingLink && $existingLink->id !== $user->id) {
            return back()->withErrors([
                'email' => 'That Google account is already connected to another LogicStrand account.',
            ]);
        }

        $user->forceFill(['google_id' => $googleId])->save();

        return redirect()->route('security.edit')->with('status', 'google-linked');
    }

    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    private function loginError(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
