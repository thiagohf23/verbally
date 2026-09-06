<?php

use App\Actions\GenerateConversationTurn;
use App\Models\Deck;
use App\Models\Flashcard;
use Livewire\Component;

new class extends Component
{
    public string $scenarioKey = 'first_date';

    public string $userMessage = '';

    public bool $processing = false;

    public ?string $error = null;

    /**
     * @var array<int, array{
     *   id: int,
     *   role: string,
     *   text: string,
     *   partner_reply?: string,
     *   native_alternative?: string,
     *   smooth_variation?: string,
     *   coaching_tip?: string,
     *   key_vocabulary?: list<string>,
     *   saved_to_deck?: bool
     * }>
     */
    public array $messages = [];

    public function mount(): void
    {
        $this->initScenario();
    }

    public function switchScenario(string $scenarioKey): void
    {
        if (! in_array($scenarioKey, ['first_date', 'coffee_shop', 'tech_standup', 'airport_travel', 'friends_pub', 'free_chat'], true)) {
            return;
        }

        $this->scenarioKey = $scenarioKey;
        $this->error = null;
        $this->initScenario();
    }

    public function initScenario(): void
    {
        $starter = $this->currentScenario()['starter'];

        $this->messages = [
            [
                'id' => 1,
                'role' => 'partner',
                'text' => $starter,
            ],
        ];
        $this->userMessage = '';
    }

    public function sendMessage(GenerateConversationTurn $turnAction): void
    {
        if ($this->processing) {
            return;
        }

        $text = trim($this->userMessage);
        $this->validate(['userMessage' => ['required', 'string', 'max:1000']]);

        $this->processing = true;
        $this->error = null;

        // Build dialogue history
        $history = array_map(fn ($m) => [
            'role' => $m['role'],
            'text' => $m['role'] === 'partner' ? ($m['text'] ?? $m['partner_reply'] ?? '') : $m['text'],
        ], $this->messages);

        $nextId = count($this->messages) + 1;

        $turn = $turnAction->execute(
            scenarioKey: $this->scenarioKey,
            history: $history,
            userMessage: $text
        );

        $this->processing = false;

        if (! $turn['success'] || $turn['reply'] === null) {
            $this->error = $turn['error'] ?? 'Could not generate conversational turn. Try again.';

            return;
        }

        // Add user turn with coaching feedback
        $this->messages[] = [
            'id' => $nextId,
            'role' => 'user',
            'text' => $text,
            'native_alternative' => $turn['native_alternative'],
            'smooth_variation' => $turn['smooth_variation'],
            'coaching_tip' => $turn['coaching_tip'],
            'key_vocabulary' => $turn['key_vocabulary'],
            'saved_to_deck' => false,
        ];

        // Add partner reply
        $this->messages[] = [
            'id' => $nextId + 1,
            'role' => 'partner',
            'text' => $turn['reply'],
        ];

        $this->userMessage = '';
    }

    public function saveToDeck(int $messageId, string $phraseType = 'native'): void
    {
        $index = array_find_key($this->messages, fn ($m) => $m['id'] === $messageId);
        if ($index === null) {
            return;
        }

        $msg = $this->messages[$index];
        $phraseToSave = match ($phraseType) {
            'smooth' => $msg['smooth_variation'] ?? $msg['text'],
            default => $msg['native_alternative'] ?? $msg['text'],
        };

        $deck = Deck::firstOrCreate(
            ['slug' => 'real-conversations'],
            [
                'name' => 'Real Conversations & Dating',
                'description' => 'Smooth, natural conversational lines, icebreakers, and charming phrases for dates and social hangouts.',
                'icon' => 'chat',
                'color' => '#ec4899',
                'is_system' => true,
            ]
        );

        $scenarioTitle = $this->currentScenario()['title'];

        Flashcard::create([
            'deck_id' => $deck->id,
            'front' => 'Diálogo ('.$scenarioTitle.'): "'.$msg['text'].'"',
            'back' => $phraseToSave,
            'grammar_tag' => 'Conversation: '.$scenarioTitle,
            'explanation' => $msg['coaching_tip'] ?? 'Natural phrasing in real-life dialogue.',
            'audio_text' => $phraseToSave,
            'interval_days' => 0,
            'ease_factor' => 2.50,
            'repetitions' => 0,
            'lapses' => 0,
            'due_at' => now(),
        ]);

        $this->messages[$index]['saved_to_deck'] = true;
    }

    public function clearConversation(): void
    {
        if ($this->processing) {
            return;
        }

        $this->initScenario();
    }

    /** @return array{title: string, partner: string, avatar: string, setting: string, starter: string} */
    public function currentScenario(): array
    {
        return self::scenarios()[$this->scenarioKey] ?? self::scenarios()['first_date'];
    }

    /** @return array<string, array{title: string, partner: string, avatar: string, setting: string, starter: string}> */
    public static function scenarios(): array
    {
        return [
            'first_date' => [
                'title' => 'First Date with Sarah',
                'partner' => 'Sarah',
                'avatar' => '🍷',
                'setting' => 'Cozy candlelit Italian café downtown on Friday evening',
                'starter' => "Hey! It's so nice to finally meet you in person. Sorry if you waited, parking around here was crazy! Have you ever been to this place before?",
            ],
            'coffee_shop' => [
                'title' => 'At the Coffee Shop',
                'partner' => 'Alex (Barista)',
                'avatar' => '☕',
                'setting' => 'Busy artisan coffee roastery in New York',
                'starter' => "Hey there! How's your morning going? What can I get started for you today?",
            ],
            'tech_standup' => [
                'title' => 'Daily Tech Standup',
                'partner' => 'Mark (Tech Lead)',
                'avatar' => '💼',
                'setting' => 'Remote team morning engineering sync',
                'starter' => "Morning team! Let's do a quick round. How did yesterday wrap up for you, and what are you tackling today? Any blockers?",
            ],
            'friends_pub' => [
                'title' => 'Casual Pub Hangout',
                'partner' => 'Leo (Friend)',
                'avatar' => '🍻',
                'setting' => 'Relaxed neighborhood pub on Saturday afternoon',
                'starter' => 'Hey man! Grab a seat! How was your week? It feels like forever since we caught up!',
            ],
            'airport_travel' => [
                'title' => 'Airport & Travel Desk',
                'partner' => 'Emma (Service Agent)',
                'avatar' => '✈️',
                'setting' => 'International terminal service counter',
                'starter' => 'Hello! Welcome to Customer Service. How may I assist you with your travels today?',
            ],
            'free_chat' => [
                'title' => 'Free-Form English Immersion',
                'partner' => 'Sam (Language Partner)',
                'avatar' => '🌐',
                'setting' => 'Casual conversation on any topic',
                'starter' => "Hi there! I'm excited to practice English with you today. What's on your mind? Tell me about your day or anything you're curious about!",
            ],
        ];
    }
};
