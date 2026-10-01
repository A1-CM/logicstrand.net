<?php

namespace App\Services;

use App\Ai\Agents\KnowledgeAnswerAgent;
use App\Contracts\AnswerGenerator;
use Illuminate\Support\Collection;
use Laravel\Ai\Responses\StructuredAgentResponse;
use RuntimeException;

class GroqAnswerGenerator implements AnswerGenerator
{
    public function generate(string $question, Collection $chunks): array
    {
        $sources = $chunks->map(fn ($chunk) => sprintf(
            "[Source ID %d | %s%s]\n%s",
            $chunk->id,
            $chunk->document->name,
            $chunk->page ? ', page '.$chunk->page : '',
            $chunk->body
        ))->implode("\n\n");

        $agentResponse = (new KnowledgeAnswerAgent)->prompt(
            "Question: {$question}\n\nSource passages:\n{$sources}",
            provider: 'groq',
            model: config('logicstrand.groq_model'),
            timeout: 40,
        );

        if (! $agentResponse instanceof StructuredAgentResponse) {
            throw new RuntimeException('Groq did not return a structured answer.');
        }

        $response = $agentResponse->toArray();
        $allowed = $chunks->pluck('id')->all();
        $citations = array_values(array_unique(array_filter(
            array_map('intval', $response['citation_ids'] ?? []),
            fn (int $id) => in_array($id, $allowed, true)
        )));

        if (! ($response['supported'] ?? false) || trim($response['answer'] ?? '') === '' || $citations === []) {
            return ['answer' => 'I could not find enough evidence in your documents to answer that question.', 'citation_ids' => []];
        }

        return ['answer' => trim($response['answer']), 'citation_ids' => $citations];
    }
}
