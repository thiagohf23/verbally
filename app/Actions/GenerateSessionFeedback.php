<?php

namespace App\Actions;

use App\Ai\Agents\SessionFeedbackAgent;
use App\Ai\GeminiRuntime;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GenerateSessionFeedback
{
    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @return array<string, mixed>
     */
    public function execute(array $messages): array
    {
        $transcript = collect($messages)
            ->map(function (array $message): string {
                $line = ($message['role'] === 'partner' ? 'Partner' : 'Learner').': '.$message['text'];
                $pronunciation = $message['pronunciation'] ?? null;

                if (is_array($pronunciation) && filled($pronunciation['summary_pt'] ?? null)) {
                    $sounds = collect($pronunciation['sound_focus'] ?? [])->pluck('sound')->filter()->implode(', ');
                    $line .= "\nPronunciation observation: {$pronunciation['summary_pt']}".($sounds ? " Sounds: {$sounds}" : '');
                }

                return $line;
            })
            ->implode("\n");

        try {
            $response = (new SessionFeedbackAgent)->prompt(
                "Review this conversation:\n{$transcript}",
                provider: 'gemini',
                model: GeminiRuntime::model(),
                timeout: GeminiRuntime::timeout(),
            );
            $cleanJson = trim((string) preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim((string) $response)));
            $data = json_decode($cleanJson, true, 512, JSON_THROW_ON_ERROR);

            return [
                'summary_pt' => (string) Arr::get($data, 'summary_pt', 'Você manteve a conversa em inglês até o fim.'),
                'wins' => array_values(array_slice(Arr::wrap(Arr::get($data, 'wins', [])), 0, 2)),
                'corrections' => array_values(array_slice(Arr::wrap(Arr::get($data, 'corrections', [])), 0, 3)),
                'phrases_to_keep' => array_values(array_slice(Arr::wrap(Arr::get($data, 'phrases_to_keep', [])), 0, 2)),
                'next_focus' => array_values(array_slice(Arr::wrap(Arr::get($data, 'next_focus', [])), 0, 2)),
            ];
        } catch (Throwable $exception) {
            Log::warning('Session feedback generation failed.', ['exception' => $exception->getMessage()]);

            return [
                'summary_pt' => 'Sessão concluída. Você praticou compreensão e resposta em contexto real.',
                'wins' => ['Você sustentou a conversa em inglês.'],
                'corrections' => [],
                'phrases_to_keep' => [],
                'next_focus' => ['Faça outra sessão curta amanhã.'],
            ];
        }
    }
}
