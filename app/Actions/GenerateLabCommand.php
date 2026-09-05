<?php

namespace App\Actions;

use App\Ai\Agents\LabCoachAgent;
use App\Ai\Agents\TranslateAgent;
use App\Ai\GeminiRuntime;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GenerateLabCommand
{
    private const LAB_COMMANDS = [
        'formal' => '👔 Make it Formal',
        'casual' => '☕ Make it Casual',
        'vocabulary' => '💎 Enrich Vocabulary',
        'quiz' => '❓ Practice Quiz',
        'custom' => '⚡ Custom Command',
    ];

    /** @return array{success: bool, label: string, response: string|null, error: string|null} */
    public function execute(string $text, string $command, ?string $customPrompt = null): array
    {
        $label = self::LAB_COMMANDS[$command] ?? '⚡ Coaching Command';
        if ($command === 'custom' && filled($customPrompt)) {
            $label = '⚡ '.mb_substr(trim($customPrompt), 0, 40);
        }

        $model = GeminiRuntime::model();
        $timeout = GeminiRuntime::timeout();

        if (blank($model)) {
            return ['success' => false, 'label' => $label, 'response' => null, 'error' => 'Gemini is not configured. Try again later.'];
        }

        try {
            $agent = new LabCoachAgent($command, $customPrompt);
            $prompt = "User English text:\n{$text}";
            if ($command === 'custom' && filled($customPrompt)) {
                $prompt = "Instruction: {$customPrompt}\n\nUser English text:\n{$text}";
            }

            $response = retry(3, fn () => $agent->prompt(
                $prompt,
                provider: 'gemini',
                model: $model,
                timeout: $timeout,
            ), 800);

            $responseText = trim((string) $response);

            if (blank($responseText)) {
                return ['success' => false, 'label' => $label, 'response' => null, 'error' => 'Gemini returned an empty response. Try again.'];
            }

            return ['success' => true, 'label' => $label, 'response' => $responseText, 'error' => null];
        } catch (Throwable $exception) {
            Log::error('GenerateLabCommand::execute exception: '.$exception->getMessage(), ['exception' => (string) $exception]);

            return ['success' => false, 'label' => $label, 'response' => null, 'error' => GeminiRuntime::errorMessage($exception, 'coaching command')];
        }
    }

    /** @return array{success: bool, translation: string|null, error: string|null} */
    public function translate(string $text): array
    {
        $model = GeminiRuntime::model();
        $timeout = GeminiRuntime::timeout();

        if (blank($model)) {
            return ['success' => false, 'translation' => null, 'error' => 'Gemini is not configured. Try again later.'];
        }

        try {
            $response = retry(3, fn () => TranslateAgent::make()->prompt(
                "Text to translate to natural Brazilian Portuguese:\n{$text}",
                provider: 'gemini',
                model: $model,
                timeout: $timeout,
            ), 800);

            $translation = trim((string) $response);

            if (blank($translation)) {
                return ['success' => false, 'translation' => null, 'error' => 'Gemini returned an empty translation. Try again.'];
            }

            return ['success' => true, 'translation' => $translation, 'error' => null];
        } catch (Throwable $exception) {
            Log::error('GenerateLabCommand::translate exception: '.$exception->getMessage(), ['exception' => (string) $exception]);

            return ['success' => false, 'translation' => null, 'error' => GeminiRuntime::errorMessage($exception, 'translation')];
        }
    }
}
