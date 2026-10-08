<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\DailyAnswerUsage;
use App\Models\KnowledgeDocument;
use App\Models\PlanAccess;
use App\Models\User;
use App\Services\DocumentSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PlanFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_and_guest_plan_selection_continue_to_registration(): void
    {
        $this->get(route('pricing'))->assertOk()
            ->assertSee('Sandbox')
            ->assertSee('Individual')
            ->assertSee('Studio')
            ->assertSee('7 days');

        $this->get(route('checkout.show', 'sandbox'))
            ->assertRedirect(route('register'))
            ->assertSessionHas('checkout.plan', 'sandbox');

        $this->get(route('checkout.show', 'unknown'))->assertNotFound();
    }

    public function test_selected_plan_survives_registration_and_email_verification(): void
    {
        $this->get(route('checkout.show', 'sandbox'))->assertRedirect(route('register'));

        $this->post(route('register.store'), [
            'name' => 'New Explorer',
            'email' => 'explorer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('checkout.show', 'sandbox'));

        $user = User::where('email', 'explorer@example.com')->firstOrFail();
        $this->actingAs($user)->get(route('checkout.show', 'sandbox'))
            ->assertRedirect(route('verification.notice'));

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->actingAs($user)->get($verificationUrl)->assertRedirect();
        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('checkout.show', 'sandbox'));
    }

    public function test_sandbox_checkout_starts_one_seven_day_trial_and_prepares_starter_source(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('checkout.show', 'sandbox'))
            ->assertOk()->assertSee('4242 4242 4242 4242');

        $this->actingAs($user)->post(route('checkout.complete', 'sandbox'), $this->card())
            ->assertRedirect(route('sandbox.index'));

        $access = PlanAccess::firstOrFail();
        $this->assertSame('sandbox', $access->plan);
        $this->assertSame(7, $access->daysRemaining());
        $this->assertSame('4242', $access->card_last_four);
        $this->assertSame(now()->addDays(7)->toDateTimeString(), $access->trial_ends_at->toDateTimeString());

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('7 days remaining');
        $this->actingAs($user)->get(route('sandbox.index'))->assertOk()->assertSee('Add starter source');

        $this->actingAs($user)->post(route('sandbox.source'))->assertRedirect(route('sandbox.index'));
        $this->assertSame('ready', KnowledgeDocument::firstOrFail()->status);
        $this->assertCount(1, app(DocumentSearch::class)->search($user->id, 'approvals renewal'));

        $this->actingAs($user)->post(route('sandbox.source'))->assertRedirect();
        $this->assertDatabaseCount('knowledge_documents', 1);

        $this->actingAs($user)->post(route('checkout.complete', 'sandbox'), $this->card())
            ->assertRedirect(route('pricing'));
        $this->assertSame(1, PlanAccess::count());
    }

    public function test_invalid_card_details_do_not_start_access_or_flash_sensitive_input(): void
    {
        $user = User::factory()->create();
        $badCard = $this->card(['card_number' => '4111 1111 1111 1111']);

        $this->actingAs($user)->post(route('checkout.complete', 'sandbox'), $badCard)
            ->assertSessionHasErrors('card_number')
            ->assertSessionMissing('_old_input.card_number');

        $this->assertDatabaseCount('plan_accesses', 0);

        $this->actingAs($user)->post(route('checkout.complete', 'sandbox'), $this->card(['expiry' => '01/20']))
            ->assertSessionHasErrors('expiry');

        $this->actingAs($user)->post(route('documents.store'), [])
            ->assertRedirect(route('pricing'));
    }

    public function test_expired_trial_preserves_read_access_and_paid_checkout_restores_workflow(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('checkout.complete', 'sandbox'), $this->card());
        $originalTrialEnd = $user->fresh()->planAccess->trial_ends_at;

        $this->travel(8)->days();

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('access period has ended');
        $this->actingAs($user)->get(route('documents.index'))->assertOk()->assertSee('Choose a plan to add more sources');
        $this->actingAs($user)->get(route('answers.create'))->assertRedirect(route('pricing'));
        $this->actingAs($user)->post(route('answers.store'), ['question' => 'What changed?'])->assertRedirect(route('pricing'));

        $this->actingAs($user)->post(route('checkout.complete', 'individual'), $this->card())
            ->assertRedirect(route('dashboard'));

        $access = $user->fresh()->planAccess;
        $this->assertSame('individual', $access->plan);
        $this->assertSame($originalTrialEnd->toDateTimeString(), $access->trial_ends_at->toDateTimeString());
        $this->assertSame(100, config('plans.'.$access->plan.'.document_limit'));
        $this->actingAs($user)->get(route('answers.create'))->assertOk();
    }

    public function test_individual_plan_uses_its_larger_document_and_question_limits(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('checkout.complete', 'individual'), $this->card())
            ->assertRedirect(route('dashboard'));

        for ($i = 0; $i < 20; $i++) {
            KnowledgeDocument::create([
                'user_id' => $user->id,
                'name' => "existing$i.txt",
                'path' => "existing$i.txt",
                'mime_type' => 'text/plain',
                'size' => 1,
                'status' => 'ready',
            ]);
        }

        $this->actingAs($user)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('new.txt', 'A new policy source.'),
        ])->assertRedirect(route('documents.index'));
        $this->assertDatabaseCount('knowledge_documents', 21);

        $this->actingAs($user)->get(route('documents.index'))->assertOk()->assertSee('21 / 100 files');

        for ($i = 0; $i < 30; $i++) {
            Answer::create(['user_id' => $user->id, 'question' => "Question $i", 'answer' => 'Saved answer']);
        }
        DailyAnswerUsage::create(['user_id' => $user->id, 'usage_date' => today(), 'answer_count' => 30]);

        $this->actingAs($user)->post(route('answers.store'), [
            'question' => 'Where is the lunar base?',
        ])->assertRedirect();
        $this->assertDatabaseCount('answers', 31);

        $this->actingAs($user)->post(route('checkout.complete', 'sandbox'), $this->card())
            ->assertRedirect(route('pricing'));
    }

    /** @param array<string, string> $overrides
     * @return array<string, string>
     */
    private function card(array $overrides = []): array
    {
        return array_merge([
            'cardholder' => 'Taylor Jordan',
            'card_number' => '4242 4242 4242 4242',
            'expiry' => now()->addYears(4)->format('m/y'),
            'cvc' => '123',
        ], $overrides);
    }
}
