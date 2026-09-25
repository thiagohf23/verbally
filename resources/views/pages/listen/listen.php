<?php

use App\Models\LearningSession;
use Livewire\Component;

new class extends Component
{
    public string $episodeKey = 'tech_catchup';

    public ?string $selectedAnswer = null;

    public bool $answered = false;

    public bool $completed = false;

    public int $replays = 0;

    public function selectEpisode(string $key): void
    {
        if (! array_key_exists($key, self::episodes())) {
            return;
        }

        $this->episodeKey = $key;
        $this->selectedAnswer = null;
        $this->answered = false;
        $this->completed = false;
        $this->replays = 0;
    }

    public function noteReplay(): void
    {
        $this->replays++;
    }

    public function answer(string $answer): void
    {
        if (! array_key_exists($answer, $this->currentEpisode()['options'])) {
            return;
        }

        $this->selectedAnswer = $answer;
        $this->answered = true;
    }

    public function completeEpisode(): void
    {
        if (! $this->answered || $this->completed) {
            return;
        }

        $this->completed = true;
        LearningSession::create([
            'kind' => LearningSession::KIND_LISTENING,
            'scenario_key' => $this->episodeKey,
            'feedback_mode' => 'comprehension',
            'status' => LearningSession::STATUS_COMPLETED,
            'messages' => $this->currentEpisode()['lines'],
            'feedback' => [
                'correct' => $this->selectedAnswer === $this->currentEpisode()['answer'],
                'answer' => $this->selectedAnswer,
            ],
            'replay_count' => $this->replays,
            'completed_at' => now(),
            'started_at' => now(),
        ]);
    }

    /** @return array<string, mixed> */
    public function currentEpisode(): array
    {
        return self::episodes()[$this->episodeKey];
    }

    /** @return array<string, array<string, mixed>> */
    public static function episodes(): array
    {
        return [
            'tech_catchup' => [
                'title' => 'A blocker at standup',
                'level' => 'Intermediate',
                'length' => '42 sec',
                'scenario' => 'tech_standup',
                'description' => 'Two developers clarify a delay without making the meeting awkward.',
                'lines' => [
                    ['speaker' => 'Maya', 'text' => "Morning! How's the checkout fix coming along?"],
                    ['speaker' => 'Ben', 'text' => "I hit a snag with the payment sandbox. It kept timing out, so I couldn't verify the last edge case."],
                    ['speaker' => 'Maya', 'text' => 'Got it. Do you need a hand, or are you unblocked now?'],
                    ['speaker' => 'Ben', 'text' => "I'm good now. I worked around it, and I should have the pull request up before lunch."],
                ],
                'question' => 'Why was the checkout fix delayed?',
                'options' => ['a' => 'Ben forgot to create the pull request.', 'b' => 'The payment sandbox kept timing out.', 'c' => 'Maya changed the requirements.'],
                'answer' => 'b',
                'chunks' => ['hit a snag' => 'encontrar um problema inesperado', 'need a hand' => 'precisar de ajuda', 'have it up' => 'deixar algo publicado/disponível'],
            ],
            'coffee_plan' => [
                'title' => 'Changing the coffee order',
                'level' => 'Easy',
                'length' => '31 sec',
                'scenario' => 'coffee_shop',
                'description' => 'A natural café exchange with a small change of mind.',
                'lines' => [
                    ['speaker' => 'Barista', 'text' => 'What can I get started for you?'],
                    ['speaker' => 'Customer', 'text' => "I'll have a large latte. Actually, could you make that iced?"],
                    ['speaker' => 'Barista', 'text' => 'Of course. Would you like regular milk or oat milk?'],
                    ['speaker' => 'Customer', 'text' => "Oat milk, please. And that's everything."],
                ],
                'question' => 'What did the customer change?',
                'options' => ['a' => 'The size of the drink.', 'b' => 'The type of coffee.', 'c' => 'A hot latte to an iced latte.'],
                'answer' => 'c',
                'chunks' => ['get started for you' => 'começar a preparar seu pedido', 'could you make that…?' => 'poderia mudar para…?', "that's everything" => 'é só isso'],
            ],
            'weekend_story' => [
                'title' => 'A surprisingly quiet weekend',
                'level' => 'Intermediate',
                'length' => '38 sec',
                'scenario' => 'friends_pub',
                'description' => 'Friends react and ask follow-up questions naturally.',
                'lines' => [
                    ['speaker' => 'Leo', 'text' => 'You look well-rested. What did you get up to this weekend?'],
                    ['speaker' => 'Nina', 'text' => 'Honestly, not much. I was going to go hiking, but the weather put me off.'],
                    ['speaker' => 'Leo', 'text' => 'Fair enough. Did you at least do something fun at home?'],
                    ['speaker' => 'Nina', 'text' => 'I ended up cooking a huge dinner and binge-watching that show you recommended.'],
                ],
                'question' => 'Why did Nina stay home?',
                'options' => ['a' => 'She was tired.', 'b' => 'The weather discouraged her.', 'c' => 'She wanted to cook for Leo.'],
                'answer' => 'b',
                'chunks' => ['get up to' => 'fazer/andar fazendo', 'put me off' => 'me desanimar', 'ended up' => 'acabou fazendo'],
            ],
        ];
    }
};
