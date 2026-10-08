<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);

        foreach ([401, 403, 404, 419, 429, 500, 503] as $status) {
            Route::get("/__test-errors/{$status}", fn () => abort($status));
        }
    }

    public function test_common_http_errors_render_branded_pages_with_their_status_codes(): void
    {
        foreach ([401, 403, 404, 419, 429, 500, 503] as $status) {
            $this->get("/__test-errors/{$status}")
                ->assertStatus($status)
                ->assertSee('LogicStrand')
                ->assertSee(route('home'), false);
        }
    }

    public function test_forbidden_page_explains_verification_link_requirements(): void
    {
        $this->get('/__test-errors/403')
            ->assertForbidden()
            ->assertSee('sign in as the account that received the link')
            ->assertSee('replace &amp;amp; with &amp; before opening it', false);
    }

    public function test_json_clients_keep_json_error_responses(): void
    {
        $this->getJson('/__test-errors/403')
            ->assertForbidden()
            ->assertJsonStructure(['message'])
            ->assertDontSee('THREAD INTERRUPTED');
    }
}
