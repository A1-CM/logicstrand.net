<?php

namespace Tests\Feature;

use App\Models\PlanAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        PlanAccess::create([
            'user_id' => $user->id,
            'plan' => 'individual',
            'period_started_at' => now(),
            'access_ends_at' => now()->addMonth(),
            'card_last_four' => '4242',
        ]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }
}
