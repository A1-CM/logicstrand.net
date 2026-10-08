<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);
        Features::passkeys([
            'confirmPassword' => true,
        ]);
    }

    public function test_security_settings_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.edit'));

        $response->assertOk();

        $response->assertSee('Passkeys');
        $response->assertSee('No passkeys yet');
        $response->assertSee('Two-factor authentication');
        $response->assertSee('Enable 2FA');
    }

    public function test_security_settings_page_requires_password_confirmation_when_enabled(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('security.edit'));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_security_settings_page_renders_without_two_factor_when_feature_is_disabled(): void
    {
        config(['fortify.features' => []]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.edit'))
            ->assertOk()
            ->assertSee('Update password')
            ->assertDontSee('Manage your passkeys for passwordless sign-in')
            ->assertDontSee('Add a passkey to sign in without a password')
            ->assertDontSee('Two-factor authentication');
    }

    public function test_guest_can_request_passwordless_sign_in_options(): void
    {
        $this->getJson(route('passkey.login-options'))
            ->assertOk()
            ->assertJsonPath('options.rpId', parse_url(config('app.url'), PHP_URL_HOST))
            ->assertJsonStructure(['options' => ['challenge', 'rpId']]);
    }

    public function test_passwordless_sign_in_rejects_an_invalid_credential(): void
    {
        $this->postJson(route('passkey.login'), ['credential' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['credential']);

        $this->assertGuest();
    }

    public function test_password_confirmed_user_can_request_passkey_registration_options(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->getJson(route('passkey.registration-options'))
            ->assertOk()
            ->assertJsonPath('options.rp.id', parse_url(config('app.url'), PHP_URL_HOST))
            ->assertJsonPath('options.user.name', $user->email)
            ->assertJsonStructure(['options' => ['challenge', 'rp', 'user']]);
    }

    public function test_passkey_registration_requires_password_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('passkey.registration-options'))
            ->assertRedirect(route('password.confirm'));
    }

    public function test_a_user_cannot_delete_another_users_passkey(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $passkey = $owner->passkeys()->create([
            'name' => 'Owner device',
            'credential_id' => 'credential-owner-1',
            'credential' => ['id' => 'credential-owner-1'],
        ]);

        $this->actingAs($otherUser)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('passkey.destroy', $passkey))
            ->assertForbidden();

        $this->assertDatabaseHas('passkeys', ['id' => $passkey->id, 'user_id' => $owner->id]);
    }

    public function test_owner_can_remove_their_passkey_after_password_confirmation(): void
    {
        $owner = User::factory()->create();
        $passkey = $owner->passkeys()->create([
            'name' => 'Owner device',
            'credential_id' => 'credential-owner-delete',
            'credential' => ['id' => 'credential-owner-delete'],
        ]);

        $this->actingAs($owner)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('passkey.destroy', $passkey))
            ->assertRedirect();

        $this->assertDatabaseMissing('passkeys', ['id' => $passkey->id]);
    }

    public function test_passkey_origin_configuration_uses_the_application_origin(): void
    {
        $appUrl = config('app.url');

        $this->assertSame([$appUrl], config('fortify.passkeys.allowed_origins'));
        $this->assertSame(parse_url($appUrl, PHP_URL_HOST), config('fortify.passkeys.relying_party_id'));
    }

    public function test_two_factor_authentication_disabled_when_confirmation_abandoned_between_requests(): void
    {
        $user = User::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->actingAs($user);

        $component = Livewire::test('pages::settings.security');

        $component->assertSet('twoFactorEnabled', false);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.security')
            ->set('current_password', 'password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $response->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.security')
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $response->assertHasErrors(['current_password']);
    }
}
