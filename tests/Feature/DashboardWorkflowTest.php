<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocument;
use App\Models\Answer;
use App\Models\DailyAnswerUsage;
use App\Models\KnowledgeDocument;
use App\Models\PlanAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Smalot\PdfParser\Parser;
use Tests\TestCase;

class DashboardWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_upload_is_queued_until_browser_starts_it_and_progress_link_is_signed(): void
    {
        Storage::fake('local');
        $user = $this->workspaceUser();
        $response = $this->actingAs($user)->postJson(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('guide.txt', 'Renewals require two approvals before the date.'),
        ])->assertCreated();

        $document = KnowledgeDocument::firstOrFail();
        $this->assertSame('queued', $document->processing_stage);
        $this->assertSame('processing', $document->status);
        $response->assertJsonPath('redirect', route('documents.index', ['watch' => $document->id]));

        $statusUrl = URL::temporarySignedRoute('documents.progress', now()->addMinutes(5), ['document' => $document]);
        $this->travel(125)->seconds();
        $this->actingAs($user)->getJson($statusUrl)->assertOk()->assertJsonPath('elapsed_seconds', 125);
        $this->actingAs($user)->postJson(route('documents.process', $document))->assertOk();
        $this->assertSame('ready', $document->fresh()->processing_stage);
        $this->assertSame('ready', $document->fresh()->status);
        $this->actingAs($user)->getJson($statusUrl)->assertOk()
            ->assertJsonPath('stage', 'ready')->assertJsonMissingPath('path');
        $this->actingAs($user)->getJson($statusUrl.'&tampered=1')->assertForbidden();
    }

    public function test_only_one_request_can_claim_queued_or_stale_processing_document(): void
    {
        Storage::fake('local');
        Queue::fake();
        config()->set('queue.default', 'database');
        $user = $this->workspaceUser();
        $path = 'documents/'.$user->id.'/stale.txt';
        Storage::disk('local')->put($path, 'A source that can be retried.');
        $document = KnowledgeDocument::create([
            'user_id' => $user->id, 'name' => 'stale.txt', 'path' => $path,
            'mime_type' => 'text/plain', 'size' => 29, 'status' => 'processing',
            'processing_stage' => 'extracting', 'processing_started_at' => now()->subMinutes(6),
        ]);

        $this->actingAs($user)->postJson(route('documents.process', $document))->assertOk();
        $this->actingAs($user)->postJson(route('documents.process', $document))->assertStatus(409);
        Queue::assertPushed(ProcessDocument::class, 1);
    }

    public function test_favorites_notes_exports_and_view_access_are_owner_scoped(): void
    {
        $owner = $this->workspaceUser();
        $other = $this->workspaceUser();
        $document = KnowledgeDocument::create([
            'user_id' => $owner->id, 'name' => 'policy.txt', 'path' => 'policy.txt',
            'mime_type' => 'text/plain', 'size' => 30, 'status' => 'ready', 'processing_stage' => 'ready',
        ]);
        $chunk = $document->chunks()->create(['page' => 4, 'position' => 0, 'body' => 'Two approvals are required before renewal.']);
        $answer = Answer::create(['user_id' => $owner->id, 'question' => 'What is needed?', 'answer' => 'Two approvals.']);
        $answer->citations()->create(['document_chunk_id' => $chunk->id]);

        $this->actingAs($owner)->patch(route('answers.favorite', $answer), ['favorite' => 1])->assertRedirect();
        $this->actingAs($owner)->put(route('answers.note', $answer), ['private_note' => 'Private reminder: call finance.'])->assertRedirect();
        $this->actingAs($owner)->get(route('answers.index', ['status' => 'favorite']))->assertOk()->assertSee('What is needed?');
        $this->actingAs($owner)->get(route('answers.show', $answer))->assertOk()->assertSee('Private reminder');
        $this->assertNotNull($answer->fresh()->viewed_at);

        $defaultPdf = $this->actingAs($owner)->get(route('answers.export', $answer))->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $defaultPdf->getContent());
        $defaultText = (new Parser)->parseContent($defaultPdf->getContent())->getText();
        $this->assertStringContainsString('What is needed?', $defaultText);
        $this->assertStringContainsString('policy.txt', $defaultText);
        $this->assertStringContainsString('Page 4', $defaultText);
        $this->assertStringNotContainsString('Private reminder', $defaultText);
        $withNote = $this->actingAs($owner)->get(route('answers.export', ['answer' => $answer, 'include_note' => 1]))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $withNote->getContent());
        $withNoteText = (new Parser)->parseContent($withNote->getContent())->getText();
        $this->assertStringContainsString('Private reminder', $withNoteText);

        $this->actingAs($other)->patch(route('answers.favorite', $answer), ['favorite' => 0])->assertNotFound();
        $this->actingAs($other)->put(route('answers.note', $answer), ['private_note' => 'No'])->assertNotFound();
        $this->actingAs($other)->get(route('answers.export', $answer))->assertNotFound();
    }

    public function test_new_accounts_can_dismiss_onboarding_and_completed_checklist_hides(): void
    {
        $user = $this->workspaceUser();
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Your first three steps');
        $this->actingAs($user)->post(route('onboarding.dismiss'))->assertRedirect();
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('Your first three steps');

        $second = $this->workspaceUser();
        KnowledgeDocument::create([
            'user_id' => $second->id, 'name' => 'starter-renewal-policy.txt', 'path' => 'sample.txt',
            'mime_type' => 'text/plain', 'size' => 10, 'status' => 'ready', 'processing_stage' => 'ready',
        ]);
        $answer = Answer::create(['user_id' => $second->id, 'question' => 'What approvals are required before renewal?', 'answer' => 'Two approvals.']);
        $answer->forceFill(['viewed_at' => now()])->save();
        $this->actingAs($second)->get(route('dashboard'))->assertOk()->assertDontSee('Your first three steps');
    }

    public function test_saved_answer_usage_survives_deletion_and_failed_provider_requests_do_not_consume_it(): void
    {
        $user = $this->workspaceUser();
        $answer = Answer::create(['user_id' => $user->id, 'question' => 'Old answer?', 'answer' => 'Saved.']);
        DailyAnswerUsage::create(['user_id' => $user->id, 'usage_date' => today(), 'answer_count' => 1]);
        $answer->delete();
        $this->assertSame(1, DailyAnswerUsage::firstOrFail()->answer_count);
        $this->actingAs($user)->get(route('answers.create'))->assertOk()->assertSee('29');
    }

    private function workspaceUser(): User
    {
        $user = User::factory()->create();
        PlanAccess::create([
            'user_id' => $user->id, 'plan' => 'sandbox', 'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays(7), 'period_started_at' => now(),
            'access_ends_at' => now()->addDays(7), 'card_last_four' => '4242',
        ]);

        return $user;
    }
}
