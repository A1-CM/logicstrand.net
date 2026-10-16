<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.google.client_id', 'test-google-client-id');
        config()->set('services.google.client_secret', 'test-google-client-secret');
        config()->set('services.google.redirect', 'http://localhost/auth/google/callback');
    }

    public function test_google_can_create_a_verified_account_and_start_a_session(): void
    {
        $this->mockGoogleIdentity($this->googleUser());

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google.user@example.com',
            'google_id' => 'google-subject-123',
        ]);
        $this->assertNotNull(auth()->user()->email_verified_at);
    }

    public function test_google_sign_in_does_not_auto_link_an_existing_email_account(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'google.user@example.com',
            'email_verified_at' => now(),
        ]);

        $this->mockGoogleIdentity($this->googleUser());

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['id' => $existingUser->id, 'google_id' => null]);
    }

    public function test_google_can_be_linked_to_the_current_account_only_during_the_link_flow(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $googleUser = $this->googleUser(['email' => 'different-address@example.com']);

        $this->mockGoogleIdentity($googleUser);

        $this->actingAs($user)
            ->withSession(['google_link_user_id' => $user->id])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('security.edit'))
            ->assertSessionHas('status', 'google-linked');

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'google_id' => 'google-subject-123']);
    }

    public function test_google_identity_must_report_a_verified_email(): void
    {
        $googleUser = $this->googleUser();
        $googleUser->setRaw(['email_verified' => false]);
        $this->mockGoogleIdentity($googleUser);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_login_still_requires_the_existing_two_factor_challenge(): void
    {
        if (! Features::enabled(Features::twoFactorAuthentication())) {
            $this->markTestSkipped('Fortify two-factor authentication is not enabled.');
        }

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->forceFill([
            'google_id' => 'google-subject-123',
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->mockGoogleIdentity($this->googleUser());

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('two-factor.login'))
            ->assertSessionHas('login.id', $user->id);

        $this->assertGuest();
        $this->get(route('two-factor.login'))->assertOk();
    }

    public function test_google_buttons_are_shown_only_when_credentials_are_configured(): void
    {
        config()->set('services.google.client_id', null);
        config()->set('services.google.client_secret', null);

        $this->get(route('login'))->assertOk()->assertDontSee('Continue with Google');
        $this->get(route('register'))->assertOk()->assertDontSee('Sign up with Google');

        config()->set('services.google.client_id', 'test-google-client-id');
        config()->set('services.google.client_secret', 'test-google-client-secret');

        $this->get(route('login'))->assertOk()->assertSee('Continue with Google');
        $this->get(route('register'))->assertOk()->assertSee('Sign up with Google');
    }

    private function googleUser(array $attributes = []): GoogleUser
    {
        $user = GoogleUser::fake(array_merge([
            'id' => 'google-subject-123',
            'name' => 'Google User',
            'email' => 'google.user@example.com',
        ], $attributes));
        $user->setRaw(['email_verified' => true]);

        return $user;
    }

    private function mockGoogleIdentity(GoogleUser $googleUser): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
    }
}
