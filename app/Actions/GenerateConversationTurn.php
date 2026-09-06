<?php

namespace App\Actions;

use App\Ai\Agents\ConversationTurnAgent;
use App\Ai\GeminiRuntime;
use Illuminate\Support\Facades\Log;
use JsonException;
use Throwable;

final class GenerateConversationTurn
{
    /**
     * @param  array<int, array{role: string, text: string}>  $history
     * @return array{
     *   success: bool,
     *   reply: string|null,
     *   native_alternative: string|null,
     *   smooth_variation: string|null,
     *   coaching_tip: string|null,
     *   key_vocabulary: list<string>,
     *   error: string|null
     * }
     */
    public function execute(string $scenarioKey, array $history, string $userMessage): array
    {
        $model = GeminiRuntime::model();
        $timeout = GeminiRuntime::timeout();

        if (blank($model)) {
            return [
                'success' => false,
                'reply' => null,
                'native_alternative' => null,
                'smooth_variation' => null,
                'coaching_tip' => null,
                'key_vocabulary' => [],
                'error' => 'Gemini is not configured. Try again later.',
            ];
        }

        try {
            $agent = new ConversationTurnAgent($scenarioKey);

            $formattedHistory = '';
            foreach ($history as $msg) {
                $speaker = $msg['role'] === 'partner' ? 'Partner' : 'User';
                $formattedHistory .= "{$speaker}: {$msg['text']}\n";
            }

            $prompt = <<<PROMPT
Conversation so far:
{$formattedHistory}
User: {$userMessage}

Respond with the JSON object as instructed.
PROMPT;

            $attempts = app()->environment('testing') ? 1 : 3;
            $response = retry($attempts, fn () => $agent->prompt(
                $prompt,
                provider: 'gemini',
                model: $model,
                timeout: $timeout,
            ), 800);

            $rawText = trim((string) $response);

            // Strip potential markdown JSON codeblocks if emitted
            $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $rawText);
            $cleanJson = trim($cleanJson);

            $data = json_decode($cleanJson, true, 512, JSON_THROW_ON_ERROR);

            return [
                'success' => true,
                'reply' => $data['reply'] ?? 'That sounds interesting! Tell me more.',
                'native_alternative' => $data['native_alternative'] ?? $userMessage,
                'smooth_variation' => $data['smooth_variation'] ?? $userMessage,
                'coaching_tip' => $data['coaching_tip'] ?? 'Great conversation starter! Keep it going.',
                'key_vocabulary' => is_array($data['key_vocabulary'] ?? null) ? $data['key_vocabulary'] : [],
                'error' => null,
            ];
        } catch (JsonException $e) {
            Log::error('GenerateConversationTurn JSON error: '.$e->getMessage(), ['raw' => $rawText ?? '']);

            return [
                'success' => true,
                'reply' => $rawText ?? 'That is really cool! What made you think of that?',
                'native_alternative' => $userMessage,
                'smooth_variation' => $userMessage,
                'coaching_tip' => 'Spoken naturally and kept the conversation flowing.',
                'key_vocabulary' => [],
                'error' => null,
            ];
        } catch (Throwable $exception) {
            Log::error('GenerateConversationTurn exception: '.$exception->getMessage(), ['exception' => (string) $exception]);

            return [
                'success' => false,
                'reply' => null,
                'native_alternative' => null,
                'smooth_variation' => null,
                'coaching_tip' => null,
                'key_vocabulary' => [],
                'error' => GeminiRuntime::errorMessage($exception, 'conversation turn'),
            ];
        }
    }
}
