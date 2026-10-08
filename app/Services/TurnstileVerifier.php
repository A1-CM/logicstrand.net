<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TurnstileVerifier
{
    public function verify(string $token): bool
    {
        $response = Http::asForm()->timeout(5)->post(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $token,
            ],
        );

        if (! $response->successful()) {
            throw new RuntimeException('Turnstile verification is unavailable.');
        }

        $hostname = $response->json('hostname');
        $expectedHostname = parse_url((string) config('app.url'), PHP_URL_HOST);

        return $response->json('success') === true
            && $response->json('action') === 'contact'
            && is_string($hostname)
            && is_string($expectedHostname)
            && $expectedHostname !== ''
            && hash_equals(strtolower($expectedHostname), strtolower($hostname));
    }
}
