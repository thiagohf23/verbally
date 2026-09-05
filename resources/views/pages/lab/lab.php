<?php

use App\Actions\GenerateLabCommand;
use App\Models\Deck;
use App\Models\Flashcard;
use Livewire\Component;

new class extends Component
{
    public string $text = '';

    public string $command = 'formal';

    public string $customPrompt = '';

    public bool $processing = false;

    public ?string $error = null;

    /** @var array<int, array{id: int, text: string, command: string, label: string, response: string, translation: ?string, translating: bool}> */
    public array $results = [];

    public function selectCommand(string $command): void
    {
        if (in_array($command, ['formal', 'casual', 'vocabulary', 'quiz', 'custom'], true)) {
            $this->command = $command;
            $this->error = null;
        }
    }

    public function runCommand(): void
    {
        if ($this->processing) {
            return;
        }

        $this->text = trim($this->text);
        $this->customPrompt = trim($this->customPrompt);

        $rules = ['text' => ['required', 'string', 'max:2000']];
        if ($this->command === 'custom') {
            $rules['customPrompt'] = ['required', 'string', 'max:300'];
        }

        $this->validate($rules);

        $this->processing = true;
        $this->error = null;

        $action = app(GenerateLabCommand::class);
        $outcome = $action->execute(
            $this->text,
            $this->command,
            $this->command === 'custom' ? $this->customPrompt : null,
        );

        $this->processing = false;

        if (! $outcome['success'] || $outcome['response'] === null) {
            $this->error = $outcome['error'] ?? 'Coaching command failed. Try again.';

            return;
        }

        array_unshift($this->results, [
            'id' => empty($this->results) ? 1 : max(array_column($this->results, 'id')) + 1,
            'text' => $this->text,
            'command' => $this->command,
            'label' => $outcome['label'],
            'response' => $outcome['response'],
            'translation' => null,
            'translating' => false,
            'saved' => false,
        ]);
    }

    public function saveResultToDeck(int $resultId): void
    {
        $index = array_find_key($this->results, fn (array $item): bool => $item['id'] === $resultId);
        if ($index === null || ($this->results[$index]['saved'] ?? false)) {
            return;
        }

        $deck = Deck::firstOrCreate(
            ['slug' => 'my-corrections'],
            [
                'name' => 'My Corrections',
                'description' => 'Personal flashcards saved directly from your live practice and coaching lab.',
                'icon' => 'bookmark',
                'color' => '#7c3aed',
                'is_system' => true,
            ]
        );

        Flashcard::create([
            'deck_id' => $deck->id,
            'front' => 'Original: "'.$this->results[$index]['text'].'"',
            'back' => $this->results[$index]['response'],
            'grammar_tag' => 'Lab: '.ucfirst($this->results[$index]['command']),
            'explanation' => 'Saved from Coaching Lab ('.$this->results[$index]['label'].')',
            'audio_text' => $this->results[$index]['response'],
            'interval_days' => 0,
            'ease_factor' => 2.50,
            'repetitions' => 0,
            'lapses' => 0,
            'due_at' => now(),
        ]);

        $this->results[$index]['saved'] = true;
    }

    public function translateResult(int $resultId): void
    {
        $index = array_find_key($this->results, fn (array $item): bool => $item['id'] === $resultId);
        if ($index === null || $this->results[$index]['translating'] || filled($this->results[$index]['translation'])) {
            return;
        }

        $this->results[$index]['translating'] = true;

        $action = app(GenerateLabCommand::class);
        $outcome = $action->translate($this->results[$index]['response']);

        $this->results[$index]['translating'] = false;

        if ($outcome['success'] && filled($outcome['translation'])) {
            $this->results[$index]['translation'] = $outcome['translation'];
        }
    }

    public function clearLab(): void
    {
        if ($this->processing) {
            return;
        }

        $this->results = [];
        $this->text = '';
        $this->customPrompt = '';
        $this->error = null;
    }
};
