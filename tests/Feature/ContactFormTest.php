<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://logicstrand.net');
        config()->set('services.turnstile.site_key', 'test-site-key');
        config()->set('services.turnstile.secret_key', 'test-secret-key');
    }

    public function test_contact_section_renders_form_company_details_and_social_links(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('id="contact"', false)
            ->assertSee('support@logicstrand.net')
            ->assertSee('LogicStrand Technologies (Pvt) Ltd')
            ->assertSee('LogicStrand Technologies Inc.')
            ->assertSee('18 September 2023')
            ->assertSee('https://medium.com/@LogicStrand', false)
            ->assertSee('https://www.youtube.com/@LogicStrand-d4t', false)
            ->assertSee('https://www.facebook.com/LogicStrand/', false)
            ->assertSee('data-action="contact"', false);
    }

    public function test_valid_message_is_emailed_with_visitor_as_reply_to(): void
    {
        Mail::fake();
        config()->set('mail.from.address', 'no-reply@logicstrand.net');
        $this->fakeTurnstile();

        $this->post(route('contact.store'), $this->payload())
            ->assertRedirect(route('home').'#contact')
            ->assertSessionHas('toast', ['type' => 'success', 'message' => 'Thanks for reaching out. Your message has been sent.']);

        Http::assertSent(fn ($request) => $request['secret'] === 'test-secret-key'
            && $request['response'] === 'good-token');
        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail): bool {
            $replyTo = $mail->envelope()->replyTo[0];

            return $mail->hasTo('support@logicstrand.net')
                && $replyTo->address === 'jane@example.com'
                && $replyTo->name === 'Jane Smith'
                && $mail->subjectLine === 'Product question'
                && $mail->organization === 'Acme'
                && $mail->envelope()->from === null
                && str_contains($mail->render(), 'Could you tell me how the knowledge workspace works?');
        });
    }

    public function test_invalid_fields_and_missing_token_do_not_send_mail(): void
    {
        Mail::fake();
        Http::fake();

        $this->post(route('contact.store'), [
            'name' => 'A',
            'email' => 'invalid',
            'subject' => '',
            'message' => 'short',
            'cf-turnstile-response' => 'good-token',
        ])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->post(route('contact.store'), array_diff_key($this->payload(), ['cf-turnstile-response' => true]))
            ->assertSessionHasErrors('turnstile');

        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_honeypot_silently_accepts_without_verification_or_mail(): void
    {
        Mail::fake();
        Http::fake();

        $this->post(route('contact.store'), ['website' => 'https://spam.example'])
            ->assertRedirect(route('home').'#contact')
            ->assertSessionHas('toast', ['type' => 'success', 'message' => 'Thanks for reaching out. Your message has been received.']);

        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_missing_keys_disable_the_form_and_reject_direct_posts(): void
    {
        Mail::fake();
        Http::fake();
        config()->set('services.turnstile.secret_key', null);

        $this->get(route('home'))->assertOk()
            ->assertSee('The form is temporarily unavailable.')
            ->assertDontSee('class="cf-turnstile"', false);

        $this->post(route('contact.store'), $this->payload())
            ->assertRedirect(route('home').'#contact')
            ->assertSessionHas('toast', ['type' => 'error', 'message' => 'The contact form is temporarily unavailable. Please email support@logicstrand.net.']);

        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_invalid_expired_wrong_action_and_wrong_hostname_tokens_never_send_mail(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        Mail::fake();

        foreach ([
            ['success' => false, 'error-codes' => ['timeout-or-duplicate']],
            ['success' => true, 'action' => 'login', 'hostname' => 'logicstrand.net'],
            ['success' => true, 'action' => 'contact', 'hostname' => 'other.example'],
        ] as $result) {
            Http::fake(['challenges.cloudflare.com/*' => Http::response($result)]);

            $this->post(route('contact.store'), $this->payload())
                ->assertRedirect(route('home').'#contact')
                ->assertSessionHasErrors('turnstile');
        }

        Mail::assertNothingSent();
    }

    public function test_verification_outage_does_not_send_mail(): void
    {
        Mail::fake();
        Http::fake(fn () => throw new ConnectionException('offline'));

        $this->post(route('contact.store'), $this->payload())
            ->assertSessionHasErrors('turnstile');
        Mail::assertNothingSent();
    }

    public function test_smtp_failure_shows_an_error_instead_of_success(): void
    {
        $this->fakeTurnstile();
        Mail::shouldReceive('to')->once()->with('support@logicstrand.net')
            ->andThrow(new RuntimeException('smtp unavailable'));

        $this->post(route('contact.store'), $this->payload())
            ->assertSessionHasErrors('delivery');
    }

    public function test_contact_endpoint_is_limited_to_five_requests_per_minute_per_ip(): void
    {
        Mail::fake();
        Http::fake();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('contact.store'), ['website' => 'spam'])->assertRedirect();
        }

        $this->post(route('contact.store'), ['website' => 'spam'])->assertStatus(429);

        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    private function fakeTurnstile(): void
    {
        Http::fake(['challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact',
            'hostname' => 'logicstrand.net',
        ])]);
    }

    private function payload(): array
    {
        return [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'organization' => 'Acme',
            'subject' => 'Product question',
            'message' => 'Could you tell me how the knowledge workspace works?',
            'cf-turnstile-response' => 'good-token',
        ];
    }
}
