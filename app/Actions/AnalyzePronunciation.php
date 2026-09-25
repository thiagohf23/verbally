<?php

namespace App\Actions;

use App\Ai\Agents\PronunciationCoachAgent;
use App\Ai\GeminiRuntime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Files\Audio;
use Throwable;

final class AnalyzePronunciation
{
    /** @return array<string, mixed> */
    public function execute(UploadedFile $recording, string $browserTranscript = ''): array
    {
        try {
            $audio = Audio::fromPath($recording->getRealPath(), $recording->getMimeType());
            $response = (new PronunciationCoachAgent)->prompt(
                'Analyze this spoken English response. Browser speech recognition guessed: '.($browserTranscript ?: '[not available]').'.',
                attachments: [$audio],
                provider: 'gemini',
                model: GeminiRuntime::model(),
                timeout: GeminiRuntime::timeout(),
            );

            $data = json_decode($this->cleanJson((string) $response), true, 512, JSON_THROW_ON_ERROR);

            return [
                'transcript' => trim((string) Arr::get($data, 'transcript', $browserTranscript)),
                'clarity_score' => max(0, min(100, (int) Arr::get($data, 'clarity_score', 0))),
                'summary_pt' => (string) Arr::get($data, 'summary_pt', ''),
                'strengths' => array_values(array_slice(Arr::wrap(Arr::get($data, 'strengths', [])), 0, 2)),
                'sound_focus' => array_values(array_slice(Arr::wrap(Arr::get($data, 'sound_focus', [])), 0, 2)),
                'audio_analyzed' => true,
            ];
        } catch (Throwable $exception) {
            Log::warning('Pronunciation audio analysis failed.', ['exception' => $exception->getMessage()]);

            return [
                'transcript' => trim($browserTranscript),
                'clarity_score' => null,
                'summary_pt' => $browserTranscript
                    ? 'A transcrição do navegador foi usada. Configure um modelo Gemini com suporte a áudio para receber análise articulatória.'
                    : 'Não consegui transcrever esta gravação. Tente novamente falando um pouco mais perto do microfone.',
                'strengths' => [],
                'sound_focus' => [],
                'audio_analyzed' => false,
            ];
        }
    }

    private function cleanJson(string $response): string
    {
        return trim((string) preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($response)));
    }
}
