<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\DailyAnswerUsage;
use App\Models\KnowledgeDocument;
use App\Models\PlanAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardUpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_document_retry_is_owner_scoped_and_claimed_once(): void
    {
        Storage::fake('local');
        $owner = $this->workspaceUser();
        $other = $this->workspaceUser();
        $path = 'documents/'.$owner->id.'/failed.txt';
        Storage::disk('local')->put($path, 'Recoverable source text');

        $document = KnowledgeDocument::create([
            'user_id' => $owner->id,
            'name' => 'failed.txt',
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => 23,
            'status' => 'failed',
            'error' => 'Temporary extraction problem.',
        ]);

        $this->actingAs($other)->post(route('documents.retry', $document))->assertNotFound();
        $this->assertSame('failed', $document->fresh()->status);

        $this->actingAs($owner)->get(route('documents.index', ['status' => 'failed']))
            ->assertOk()->assertSee('failed.txt')->assertSee('Retry');

        $this->actingAs($owner)->post(route('documents.retry', $document))
            ->assertRedirect(route('documents.index'));
        $this->assertSame('ready', $document->fresh()->status);
        $this->assertNull($document->fresh()->error);

        $this->actingAs($owner)->post(route('documents.retry', $document))
            ->assertSessionHas('toast');
        $this->actingAs($owner)->get(route('documents.index'))
            ->assertOk()->assertSee('Ready');
    }

    public function test_retry_with_missing_original_keeps_failed_state(): void
    {
        Storage::fake('local');
        $user = $this->workspaceUser();
        $document = KnowledgeDocument::create([
            'user_id' => $user->id,
            'name' => 'gone.txt',
            'path' => 'documents/'.$user->id.'/gone.txt',
            'mime_type' => 'text/plain',
            'size' => 5,
            'status' => 'failed',
            'error' => 'Could not read.',
        ]);

        $this->actingAs($user)->post(route('documents.retry', $document))->assertSessionHas('toast');
        $this->assertSame('failed', $document->fresh()->status);
    }

    public function test_history_search_and_evidence_filters_remain_private(): void
    {
        $owner = $this->workspaceUser();
        $other = $this->workspaceUser();
        $document = KnowledgeDocument::create([
            'user_id' => $owner->id, 'name' => 'policy.txt', 'path' => 'policy.txt',
            'mime_type' => 'text/plain', 'size' => 12, 'status' => 'ready',
        ]);
        $chunk = $document->chunks()->create(['page' => null, 'position' => 0, 'body' => 'Renewal approvals']);
        $cited = Answer::create(['user_id' => $owner->id, 'question' => 'Renewal approvals?', 'answer' => 'Two approvals.']);
        $cited->citations()->create(['document_chunk_id' => $chunk->id]);
        Answer::create(['user_id' => $owner->id, 'question' => 'Unknown topic?', 'answer' => 'Insufficient evidence.']);
        Answer::create(['user_id' => $other->id, 'question' => 'Renewal secret?', 'answer' => 'Private answer.']);

        $this->actingAs($owner)->get(route('answers.index', ['q' => 'Renewal', 'status' => 'cited']))
            ->assertOk()->assertSee('Renewal approvals?')->assertDontSee('Unknown topic?')->assertDontSee('Renewal secret?');
        $this->actingAs($owner)->get(route('answers.index', ['status' => 'insufficient']))
            ->assertOk()->assertSee('Unknown topic?')->assertDontSee('Renewal approvals?');
        $this->actingAs($other)->get(route('answers.show', $cited))->assertNotFound();
    }

    public function test_empty_workspace_cannot_create_an_answer_and_ask_page_explains_why(): void
    {
        $user = $this->workspaceUser();

        $this->actingAs($user)->get(route('answers.create'))->assertOk()
            ->assertSee('No ready documents yet')
            ->assertSee('30');

        $this->actingAs($user)->post(route('answers.store'), ['question' => 'What is the policy?'])
            ->assertRedirect(route('documents.index'));
        $this->assertDatabaseCount('answers', 0);
    }

    public function test_overview_shows_failed_upload_and_saved_answer_allowance(): void
    {
        $user = $this->workspaceUser();
        KnowledgeDocument::create([
            'user_id' => $user->id, 'name' => 'scan.pdf', 'path' => 'scan.pdf',
            'mime_type' => 'application/pdf', 'size' => 100, 'status' => 'failed',
            'error' => 'No readable text found.',
        ]);
        Answer::create(['user_id' => $user->id, 'question' => 'First?', 'answer' => 'One.']);
        DailyAnswerUsage::create(['user_id' => $user->id, 'usage_date' => today(), 'answer_count' => 1]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('A document needs your attention.')
            ->assertSee('Saved answers today')
            ->assertSee('29 available today');
    }

    public function test_processing_and_expired_states_offer_the_correct_next_action(): void
    {
        $user = $this->workspaceUser();
        KnowledgeDocument::create([
            'user_id' => $user->id, 'name' => 'working.txt', 'path' => 'working.txt',
            'mime_type' => 'text/plain', 'size' => 10, 'status' => 'processing',
        ]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('Your source is being prepared.')
            ->assertSee('Open documents');
        $this->actingAs($user)->get(route('answers.create'))->assertOk()
            ->assertSee('Your upload is processing.');

        $user->planAccess->update(['access_ends_at' => now()->subDay()]);
        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('Your access period has ended.');
        $this->actingAs($user)->get(route('documents.index'))->assertOk()
            ->assertSee('Choose a plan to add more sources');
    }

    public function test_full_daily_allowance_disables_question_form_but_keeps_history_accessible(): void
    {
        $user = $this->workspaceUser();
        KnowledgeDocument::create([
            'user_id' => $user->id, 'name' => 'ready.txt', 'path' => 'ready.txt',
            'mime_type' => 'text/plain', 'size' => 10, 'status' => 'ready',
        ]);
        for ($index = 0; $index < 30; $index++) {
            Answer::create(['user_id' => $user->id, 'question' => "Question $index", 'answer' => 'Saved answer']);
        }
        DailyAnswerUsage::create(['user_id' => $user->id, 'usage_date' => today(), 'answer_count' => 30]);

        $this->actingAs($user)->get(route('answers.create'))->assertOk()
            ->assertSee('saved-answer allowance is full')
            ->assertSee('Open answer history')
            ->assertDontSee('data-busy-form');
        $this->actingAs($user)->get(route('answers.index'))->assertOk();
    }

    private function workspaceUser(): User
    {
        $user = User::factory()->create();
        PlanAccess::create([
            'user_id' => $user->id,
            'plan' => 'sandbox',
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(7),
            'access_ends_at' => now()->addDays(7),
            'card_last_four' => '4242',
        ]);

        return $user;
    }
}
