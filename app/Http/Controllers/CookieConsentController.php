<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CookieConsentController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'choice' => ['required', 'string', Rule::in(['optional', 'essential'])],
        ]);

        $previous = url()->previous();
        $previousHost = parse_url($previous, PHP_URL_HOST);
        $destination = $previousHost === $request->getHost() ? $previous : route('home');

        return redirect()->to($destination)
            ->withCookie(cookie(
                'logicstrand_cookie_consent',
                $validated['choice'],
                60 * 24 * 180,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'lax',
            ));
    }
}
