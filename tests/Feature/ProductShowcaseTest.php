<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_ls_atlas_product_page_describes_current_features_and_future_model_direction(): void
    {
        $this->get(route('product'))
            ->assertOk()
            ->assertSee('LS Atlas')
            ->assertSee('Upload text files and text-based PDFs')
            ->assertSee('private notes')
            ->assertSee('NVIDIA integrations are not available in the current workspace')
            ->assertSee(route('register'), false)
            ->assertSee(route('pricing'), false);
    }

    public function test_home_and_pricing_ctas_lead_to_the_ls_atlas_page_without_public_sign_in_button(): void
    {
        foreach ([route('home'), route('pricing')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Explore LS Atlas')
                ->assertSee(route('product'), false)
                ->assertDontSee('>Sign in</a>', false);
        }

        $this->get(route('home'))->assertSee('href="'.route('product').'" class="button button-blue"', false);
    }

    public function test_signed_in_visitors_keep_the_dashboard_navigation_action(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertDontSee('Sign in');
    }
}
