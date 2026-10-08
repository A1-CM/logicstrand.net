<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Mail\Markdown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::emailVerification());
    }

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertOk();
    }

    public function test_unverified_users_are_redirected_to_the_email_verification_prompt(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('appearance.edit'));

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_guest_is_redirected_to_sign_in_before_verifying(): void
    {
        $user = User::factory()->unverified()->create();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->get($verificationUrl)->assertRedirect(route('login'));
    }

    public function test_verification_link_for_another_signed_in_account_is_forbidden(): void
    {
        $recipient = User::factory()->unverified()->create();
        $otherUser = User::factory()->create();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $recipient->id, 'hash' => sha1($recipient->email)],
        );

        $this->actingAs($otherUser)->get($verificationUrl)->assertForbidden();
        $this->assertFalse($recipient->fresh()->hasVerifiedEmail());
    }

    public function test_expired_and_modified_verification_links_are_forbidden(): void
    {
        $user = User::factory()->unverified()->create();
        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );
        $validUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->actingAs($user)->get($expiredUrl)->assertForbidden();
        $this->get($validUrl.'&copied=1')->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_notification_contains_a_signed_url_and_html_escapes_query_delimiters(): void
    {
        $user = User::factory()->unverified()->create();
        $message = (new VerifyEmail)->toMail($user);
        $html = (string) $message->render();
        $text = app(Markdown::class)->renderText($message->markdown, $message->data());

        $this->assertStringContainsString('&signature=', $message->actionUrl);
        $this->assertStringContainsString('&amp;signature=', $html);
        $this->assertStringNotContainsString('&amp;amp;signature=', $html);
        $this->assertStringContainsString($message->actionUrl, $text);
        $this->assertStringNotContainsString('&amp;signature=', $text);
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')],
        );

        $this->actingAs($user)->get($verificationUrl)->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_already_verified_user_visiting_verification_link_is_redirected_without_firing_event_again(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->actingAs($user)->get($verificationUrl)
            ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        Event::assertNotDispatched(Verified::class);
    }
}
