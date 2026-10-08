<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use App\Services\TurnstileVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request, TurnstileVerifier $turnstile): RedirectResponse
    {
        $redirect = route('home').'#contact';

        if ($request->filled('website')) {
            return redirect()->to($redirect)->with('toast', [
                'type' => 'success',
                'message' => 'Thanks for reaching out. Your message has been received.',
            ]);
        }

        if (! filled(config('services.turnstile.site_key')) || ! filled(config('services.turnstile.secret_key'))) {
            return redirect()->to($redirect)->with('toast', [
                'type' => 'error',
                'message' => 'The contact form is temporarily unavailable. Please email support@logicstrand.net.',
            ]);
        }

        $input = $request->only('name', 'email', 'organization', 'subject', 'message');
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:254'],
            'organization' => ['nullable', 'string', 'max:120'],
            'subject' => ['required', 'string', 'min:3', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return redirect()->to($redirect)->withErrors($validator)->withInput($input);
        }

        $token = $request->input('cf-turnstile-response');

        if (! is_string($token) || $token === '' || strlen($token) > 2048) {
            return $this->withError('turnstile', 'Complete the verification before sending your message.', $input);
        }

        try {
            $verified = $turnstile->verify($token);
        } catch (Throwable $exception) {
            Log::warning('Contact form verification unavailable', ['exception' => $exception::class]);

            return $this->withError('turnstile', 'Verification is temporarily unavailable. Please try again.', $input);
        }

        if (! $verified) {
            return $this->withError('turnstile', 'Verification failed or expired. Please try again.', $input);
        }

        $data = $validator->validated();

        try {
            Mail::to('support@logicstrand.net')->send(new ContactMessage(
                $data['name'],
                $data['email'],
                $data['organization'] ?? null,
                $data['subject'],
                $data['message'],
            ));
        } catch (Throwable $exception) {
            Log::error('Contact email delivery failed', ['exception' => $exception::class]);

            return $this->withError('delivery', 'Your message could not be sent right now. Please try again or email support@logicstrand.net.', $input);
        }

        return redirect()->to($redirect)->with('toast', [
            'type' => 'success',
            'message' => 'Thanks for reaching out. Your message has been sent.',
        ]);
    }

    /** @param array<string, mixed> $input */
    private function withError(string $field, string $message, array $input): RedirectResponse
    {
        return redirect()->to(route('home').'#contact')
            ->withErrors([$field => $message])
            ->withInput($input);
    }
}
