<?php

namespace Tests\Feature;

use App\Ai\Agents\KnowledgeAnswerAgent;
use App\Models\Answer;
use App\Models\KnowledgeDocument;
use App\Models\User;
use App\Services\DocumentSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class LogicStrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_and_workspace_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Make every answer')->assertSee('Groq');
        $this->get('/register')->assertOk();

        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Good to see you');
        $this->actingAs($user)->get('/documents')->assertOk()->assertSee('Bring a source into focus');
        $this->actingAs($user)->get('/ask')->assertOk()->assertSee('Ask what matters');
    }

    public function test_text_document_is_indexed_and_other_user_cannot_retrieve_it(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($owner)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('policy.txt', 'Renewals require two approvals before the contract date.'),
        ])->assertRedirect(route('documents.index'));

        $document = KnowledgeDocument::firstOrFail();
        $this->assertSame('ready', $document->status);
        $this->assertCount(1, app(DocumentSearch::class)->search($owner->id, 'What approvals do renewals require?'));
        $this->assertCount(0, app(DocumentSearch::class)->search($other->id, 'What approvals do renewals require?'));

        $this->actingAs($other)->get(route('documents.download', $document))->assertNotFound();
        $this->actingAs($other)->delete(route('documents.destroy', $document))->assertNotFound();
        $this->assertDatabaseHas('knowledge_documents', ['id' => $document->id]);
    }

    public function test_text_based_pdf_is_indexed_and_blank_pdf_shows_extraction_error(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('guide.pdf', $this->pdf('Renewals require two approvals')),
        ])->assertRedirect();

        $this->assertSame('ready', KnowledgeDocument::where('name', 'guide.pdf')->firstOrFail()->status);
        $this->assertCount(1, app(DocumentSearch::class)->search($user->id, 'renewals approvals'));

        $this->actingAs($user)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('scan.pdf', $this->pdf('')),
        ])->assertRedirect();

        $failed = KnowledgeDocument::where('name', 'scan.pdf')->firstOrFail();
        $this->assertSame('failed', $failed->status);
        $this->assertStringContainsString('Scanned PDFs', $failed->error);
    }

    public function test_valid_sources_are_saved_and_invalid_citations_are_rejected(): void
    {
        Storage::fake('local');
        config()->set('ai.providers.groq.key', 'test-key');
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('policy.txt', 'Renewals require two approvals before the contract date.'),
        ]);
        $chunkId = KnowledgeDocument::firstOrFail()->chunks()->firstOrFail()->id;

        KnowledgeAnswerAgent::fake([
            ['supported' => true, 'answer' => 'Two approvals are required.', 'citation_ids' => [$chunkId, 999999]],
            ['supported' => true, 'answer' => 'A made-up answer.', 'citation_ids' => [999999]],
        ]);

        $this->actingAs($user)->post(route('answers.store'), ['question' => 'What do renewals require?'])->assertRedirect();
        $first = Answer::firstOrFail();
        $this->assertSame('Two approvals are required.', $first->answer);
        $this->assertSame([$chunkId], $first->citations()->pluck('document_chunk_id')->all());
        $this->actingAs($user)->get(route('answers.show', $first))->assertOk()->assertSee('policy.txt')->assertSee('Renewals require');

        $this->actingAs($user)->post(route('answers.store'), ['question' => 'What approvals do renewals require?'])->assertRedirect();
        $this->assertStringContainsString('could not find enough evidence', Answer::latest('id')->firstOrFail()->answer);
    }

    public function test_no_matching_source_needs_no_api_key_and_provider_errors_do_not_create_answers(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('policy.txt', 'Renewals require two approvals before the contract date.'),
        ]);

        $this->actingAs($user)->post(route('answers.store'), ['question' => 'Where is the lunar base?'])->assertRedirect();
        $this->assertStringContainsString('could not find enough evidence', Answer::firstOrFail()->answer);

        config()->set('ai.providers.groq.key', 'test-key');
        KnowledgeAnswerAgent::fake(fn () => throw new RuntimeException('Provider failed'));
        $this->actingAs($user)->post(route('answers.store'), ['question' => 'What do renewals require?'])->assertSessionHasErrors('question');
        $this->assertSame(1, Answer::count());
    }

    public function test_document_deletion_removes_index_and_citing_answers(): void
    {
        Storage::fake('local');
        config()->set('ai.providers.groq.key', 'test-key');
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('policy.txt', 'Renewals require two approvals before the contract date.'),
        ]);
        $document = KnowledgeDocument::firstOrFail();
        $chunkId = $document->chunks()->firstOrFail()->id;
        KnowledgeAnswerAgent::fake([['supported' => true, 'answer' => 'Two approvals.', 'citation_ids' => [$chunkId]]]);

        $this->actingAs($user)->post(route('answers.store'), ['question' => 'What approvals do renewals require?']);
        $this->assertSame(1, Answer::count());

        $this->actingAs($user)->delete(route('documents.destroy', $document))->assertRedirect();
        $this->assertSame(0, Answer::count());
        $this->assertCount(0, app(DocumentSearch::class)->search($user->id, 'renewals approvals'));
        Storage::disk('local')->assertMissing($document->path);
    }

    public function test_limits_and_invalid_uploads_are_enforced(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('script.php', '<?php echo 1;'),
        ])->assertSessionHasErrors('document');

        $this->actingAs($user)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->create('oversize.txt', 10241, 'text/plain'),
        ])->assertSessionHasErrors('document');

        $this->actingAs($user)->post(route('answers.store'), [
            'question' => '    ',
        ])->assertSessionHasErrors('question');

        for ($i = 0; $i < 20; $i++) {
            KnowledgeDocument::create([
                'user_id' => $user->id, 'name' => "file$i.txt", 'path' => "file$i.txt",
                'mime_type' => 'text/plain', 'size' => 10, 'status' => 'ready',
            ]);
        }
        $this->actingAs($user)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('extra.txt', 'Extra content'),
        ])->assertSessionHasErrors('document');

        for ($i = 0; $i < 30; $i++) {
            Answer::create(['user_id' => $user->id, 'question' => "Question $i", 'answer' => 'Answer']);
        }
        $this->actingAs($user)->post(route('answers.store'), ['question' => 'Another question'])->assertSessionHasErrors('question');
        $this->assertSame(30, Answer::count());
    }

    public function test_account_deletion_purges_files_and_search_index(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('documents.store'), [
            'document' => UploadedFile::fake()->createWithContent('policy.txt', 'Renewals require two approvals before the contract date.'),
        ]);
        $document = KnowledgeDocument::firstOrFail();
        Storage::disk('local')->assertExists($document->path);

        $user->delete();

        Storage::disk('local')->assertMissing($document->path);
        $this->assertDatabaseCount('knowledge_documents', 0);
        $this->assertSame(0, DB::table('document_chunks_fts')->count());
    }

    private function pdf(string $text): string
    {
        $stream = $text === '' ? '' : "BT /F1 12 Tf 40 760 Td ($text) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($stream)." >>\nstream\n$stream\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n$object\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";

        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        return $pdf."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n$xref\n%%EOF\n";
    }
}
