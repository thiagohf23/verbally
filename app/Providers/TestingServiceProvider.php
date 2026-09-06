<?php

namespace App\Providers;

use App\Ai\Agents\ConversationTurnAgent;
use App\Ai\Agents\CorrectionDetailsAgent;
use App\Ai\Agents\CorrectionTextAgent;
use App\Ai\Agents\ExampleAgent;
use App\Ai\Agents\LabCoachAgent;
use App\Ai\Agents\NaturalRewriteAgent;
use App\Ai\Agents\TranslateAgent;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

final class TestingServiceProvider extends ServiceProvider
{
    /** @var array<string, int> */
    private array $promptCounts = [];

    public function register(): void
    {
        if (! $this->app->environment('testing')) {
            return;
        }

        config([
            'ai.verbally.gemini_model' => config('ai.verbally.gemini_model') ?? 'testing-fixture-model',
        ]);
    }

    public function boot(): void
    {
        if (! $this->app->environment('testing')) {
            return;
        }

        CorrectionTextAgent::fake($this->correctionText(...));
        CorrectionDetailsAgent::fake($this->correctionDetails(...));
        NaturalRewriteAgent::fake($this->naturalRewrite(...));
        ExampleAgent::fake($this->example(...));
        TranslateAgent::fake($this->translate(...));
        LabCoachAgent::fake($this->labCoach(...));
        ConversationTurnAgent::fake($this->conversationTurn(...));
    }

    private function correctionText(string $submission): string
    {
        if ($submission === 'I need a correction retry.' && $this->promptCount('correction-text', $submission) === 1) {
            throw new RuntimeException('timeout');
        }

        return match ($submission) {
            'What is the difference between affect and effect?' => 'Please write an English sentence for correction.',
            'She go home.' => 'She goes home.',
            'I needs a follow-up retry.' => 'I need a follow-up retry.',
            'I needs a details retry.' => 'I need a details retry.',
            default => $submission,
        };
    }

    /** @return array{corrected: string, diff: list<array{type: string, original?: string, replacement?: string}>, explanations: list<array{tag: string, text: string}>, is_off_topic: bool} */
    private function correctionDetails(string $prompt): array
    {
        [$submission, $corrected] = $this->correctionInput($prompt);

        if ($submission === 'I needs a details retry.' && $this->promptCount('correction-details', $submission) === 1) {
            return [
                'corrected' => 'Incorrect fixture response.',
                'diff' => [],
                'explanations' => [],
                'is_off_topic' => false,
            ];
        }

        if ($submission === 'She go home.') {
            return [
                'corrected' => $corrected,
                'diff' => [
                    ['type' => 'removed', 'original' => 'She go'],
                    ['type' => 'added', 'replacement' => 'She goes'],
                    ['type' => 'unchanged', 'original' => ' home.', 'replacement' => ' home.'],
                ],
                'explanations' => [['tag' => 'Agreement', 'text' => 'Use goes with she.']],
                'is_off_topic' => false,
            ];
        }

        if ($submission === 'What is the difference between affect and effect?') {
            return [
                'corrected' => $corrected,
                'diff' => [],
                'explanations' => [],
                'is_off_topic' => true,
            ];
        }

        return [
            'corrected' => $corrected,
            'diff' => [['type' => 'unchanged', 'original' => $corrected, 'replacement' => $corrected]],
            'explanations' => [['tag' => 'Grammar', 'text' => 'The sentence is clear and correct.']],
            'is_off_topic' => false,
        ];
    }

    private function naturalRewrite(string $prompt): string
    {
        [$submission] = $this->followUpInput($prompt);

        if ($submission === 'I needs a follow-up retry.' && $this->promptCount('natural-rewrite', $submission) === 1) {
            throw new RuntimeException('timeout');
        }

        return match ($submission) {
            'She go home.' => 'She went home early.',
            'I needs a follow-up retry.' => 'I need a clearer, more natural follow-up.',
            default => 'This is a natural rewrite.',
        };
    }

    private function example(): string
    {
        return 'She goes home every day.';
    }

    private function translate(string $prompt): string
    {
        [$submission, $corrected] = $this->followUpInput($prompt);

        return match ($corrected) {
            'She goes home.' => 'Ela vai para casa.',
            default => 'Esta é a tradução em português.',
        };
    }

    private function labCoach(string $prompt): string
    {
        return "Coaching Result:\nRevised text formatted by the coach.\n\nTip: Consistent verb forms improve clarity.";
    }

    private function conversationTurn(string $prompt): string
    {
        return json_encode([
            'reply' => "Aww, thank you! I'm really glad we met up tonight. What kind of food or drinks do you usually love?",
            'native_alternative' => 'I come here all the time, and you look lovely tonight by the way.',
            'smooth_variation' => "I'm a regular here! The espresso is fantastic—and you look stunning, by the way.",
            'coaching_tip' => "Adding 'by the way' before a compliment makes it sound spontaneous and confident.",
            'key_vocabulary' => ['regular', 'by the way', 'meet up'],
        ], JSON_THROW_ON_ERROR);
    }

    /** @return array{string, string} */
    private function correctionInput(string $prompt): array
    {
        preg_match('/Original text:\n(.*?)\n\nPreviously streamed corrected text:\n(.*)$/s', $prompt, $matches);

        return [$matches[1] ?? '', $matches[2] ?? ''];
    }

    /** @return array{string, string} */
    private function followUpInput(string $prompt): array
    {
        preg_match('/Original text:\n(.*?)\n\nCorrected text:\n(.*)$/s', $prompt, $matches);

        return [$matches[1] ?? '', $matches[2] ?? ''];
    }

    private function promptCount(string $agent, string $prompt): int
    {
        $key = $agent.'|'.$prompt;

        return $this->promptCounts[$key] = ($this->promptCounts[$key] ?? 0) + 1;
    }
}
