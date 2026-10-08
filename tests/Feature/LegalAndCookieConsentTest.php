<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalAndCookieConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_pages_render_and_link_to_each_other_from_the_footer(): void
    {
        $this->get(route('privacy'))->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('Groq')
            ->assertSee('Cloudflare Turnstile')
            ->assertSee(route('cookie-consent.store'), false)
            ->assertSee(route('terms'), false);

        $this->get(route('terms'))->assertOk()
            ->assertSee('Terms & Conditions')
            ->assertSee('not connected to a payment processor')
            ->assertSee(route('privacy'), false);
    }

    public function test_footer_links_and_cookie_notice_are_available_on_public_pages(): void
    {
        foreach ([route('home'), route('pricing')] as $url) {
            $this->get($url)->assertOk()
                ->assertSee(route('privacy'), false)
                ->assertSee(route('terms'), false)
                ->assertSee('Your cookie choice')
                ->assertSee('Essential only')
                ->assertSee('Allow optional');
        }

        $user = \App\Models\User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->get(route('checkout.show', 'sandbox'))->assertOk()
            ->assertSee(route('privacy'), false)
            ->assertSee(route('terms'), false)
            ->assertSee('Your cookie choice');
    }

    public function test_visitor_can_save_either_cookie_choice_and_the_choice_is_remembered(): void
    {
        foreach (['essential', 'optional'] as $choice) {
            $response = $this->from(route('privacy'))->post(route('cookie-consent.store'), ['choice' => $choice]);

            $response->assertRedirect(route('privacy'))
                ->assertCookie('logicstrand_cookie_consent', $choice);

            $this->withCookie('logicstrand_cookie_consent', $choice)
                ->get(route('privacy'))
                ->assertOk()
                ->assertDontSee('Your cookie choice');
        }
    }

    public function test_invalid_cookie_choice_is_rejected(): void
    {
        $this->post(route('cookie-consent.store'), ['choice' => 'anything'])
            ->assertSessionHasErrors('choice');
    }
}
