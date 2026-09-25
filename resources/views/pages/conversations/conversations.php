<?php

use App\Actions\AnalyzePronunciation;
use App\Actions\GenerateConversationTurn;
use App\Actions\GenerateSessionFeedback;
use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\LearningSession;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $scenarioKey = 'first_date';

    public string $userMessage = '';

    public string $feedbackMode = 'fluency';

    public bool $processing = false;

    public ?string $error = null;

    public ?int $learningSessionId = null;

    public bool $sessionCompleted = false;

    /** @var array<string, mixed> */
    public array $sessionFeedback = [];

    public mixed $voiceRecording = null;

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
     *   saved_to_deck?: bool,
     *   source?: string,
     *   pronunciation?: array<string, mixed>
     * }>
     */
    public array $messages = [];

    public function mount(): void
    {
        $sessionId = session('verbally.learning_session_id');
        $learningSession = $sessionId ? LearningSession::find($sessionId) : null;

        if ($learningSession?->status === LearningSession::STATUS_ACTIVE && $learningSession->kind === LearningSession::KIND_CONVERSATION) {
            $this->learningSessionId = $learningSession->id;
            $this->scenarioKey = $learningSession->scenario_key ?? 'first_date';
            $this->feedbackMode = $learningSession->feedback_mode;
            $this->messages = $learningSession->messages ?: [];

            return;
        }

        $this->startSession();
    }

    public function switchScenario(string $scenarioKey): void
    {
        if (! in_array($scenarioKey, ['first_date', 'coffee_shop', 'tech_standup', 'airport_travel', 'friends_pub', 'free_chat'], true)) {
            return;
        }

        $this->scenarioKey = $scenarioKey;
        $this->error = null;
        $this->completeStoredSession();
        $this->startSession();
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

    public function setFeedbackMode(string $mode): void
    {
        if (! in_array($mode, ['fluency', 'study'], true)) {
            return;
        }

        $this->feedbackMode = $mode;
        $this->persistSession();
    }

    public function sendMessage(GenerateConversationTurn $turnAction): void
    {
        if ($this->processing) {
            return;
        }

        $this->validate(['userMessage' => ['required', 'string', 'max:1000']]);

        $text = trim($this->userMessage);
        $this->processMessage($turnAction, $text, 'text');
    }

    public function sendVoiceMessage(
        AnalyzePronunciation $pronunciationAction,
        GenerateConversationTurn $turnAction,
        string $browserTranscript = '',
        int $durationSeconds = 0,
    ): void {
        if ($this->processing) {
            return;
        }

        $this->validate([
            'voiceRecording' => ['required', 'file', 'mimetypes:audio/webm,audio/ogg,audio/wav,audio/mpeg,audio/mp4,video/webm', 'max:10240'],
        ]);

        $this->processing = true;
        $this->error = null;
        $pronunciation = $pronunciationAction->execute($this->voiceRecording, trim($browserTranscript));
        $text = trim((string) ($pronunciation['transcript'] ?? $browserTranscript));

        if ($text === '') {
            $this->processing = false;
            $this->voiceRecording = null;
            $this->error = 'Não consegui entender a gravação. Tente novamente falando mais perto do microfone.';

            return;
        }

        $this->incrementSpeakingTime($durationSeconds);
        $this->processMessage($turnAction, $text, 'voice', $pronunciation, alreadyProcessing: true);
        $this->voiceRecording = null;
    }

    /** @param array<string, mixed>|null $pronunciation */
    private function processMessage(
        GenerateConversationTurn $turnAction,
        string $text,
        string $source,
        ?array $pronunciation = null,
        bool $alreadyProcessing = false,
    ): void {
        if (! $alreadyProcessing) {
            $this->processing = true;
            $this->error = null;
        }

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
            'source' => $source,
            'pronunciation' => $pronunciation,
        ];

        // Add partner reply
        $this->messages[] = [
            'id' => $nextId + 1,
            'role' => 'partner',
            'text' => $turn['reply'],
        ];

        $this->userMessage = '';
        $this->persistSession();
        $this->dispatch('verbally-play-partner', text: $turn['reply'], messageId: $nextId + 1);
    }

    public function noteReplay(): void
    {
        if ($this->learningSessionId === null) {
            return;
        }

        LearningSession::whereKey($this->learningSessionId)->increment('replay_count');
    }

    public function finishSession(GenerateSessionFeedback $feedbackAction): void
    {
        if ($this->processing || $this->sessionCompleted) {
            return;
        }

        $this->processing = true;
        $this->sessionFeedback = $feedbackAction->execute($this->messages);
        $this->sessionCompleted = true;
        $this->processing = false;

        LearningSession::whereKey($this->learningSessionId)->update([
            'status' => LearningSession::STATUS_COMPLETED,
            'feedback' => $this->sessionFeedback,
            'completed_at' => now(),
        ]);
        session()->forget('verbally.learning_session_id');
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

        $deck = Deck::updateOrCreate(
            ['slug' => 'real-conversations'],
            [
                'name' => 'Real Conversations',
                'description' => 'Natural phrases collected from your own conversations, work, travel, and social practice.',
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

    public function saveSessionPhrase(string $phrase): void
    {
        $phrase = trim($phrase);
        if ($phrase === '' || mb_strlen($phrase) > 250 || ! in_array($phrase, $this->sessionFeedback['phrases_to_keep'] ?? [], true)) {
            return;
        }

        $deck = Deck::updateOrCreate(
            ['slug' => 'real-conversations'],
            [
                'name' => 'Real Conversations',
                'description' => 'Natural conversational phrases collected from your own practice.',
                'icon' => 'chat',
                'color' => '#ec4899',
                'is_system' => true,
            ],
        );

        Flashcard::firstOrCreate(
            ['deck_id' => $deck->id, 'back' => $phrase],
            [
                'front' => 'Ouça e use esta expressão em uma resposta natural.',
                'grammar_tag' => 'Conversation chunk',
                'explanation' => 'Expressão selecionada do feedback da sua conversa.',
                'audio_text' => $phrase,
                'due_at' => now(),
            ],
        );
    }

    public function clearConversation(): void
    {
        if ($this->processing) {
            return;
        }

        $this->completeStoredSession();
        $this->startSession();
    }

    private function startSession(): void
    {
        $this->initScenario();
        $this->sessionCompleted = false;
        $this->sessionFeedback = [];

        $learningSession = LearningSession::create([
            'kind' => LearningSession::KIND_CONVERSATION,
            'scenario_key' => $this->scenarioKey,
            'feedback_mode' => $this->feedbackMode,
            'status' => LearningSession::STATUS_ACTIVE,
            'messages' => $this->messages,
            'started_at' => now(),
        ]);

        $this->learningSessionId = $learningSession->id;
        session(['verbally.learning_session_id' => $learningSession->id]);
    }

    private function persistSession(): void
    {
        LearningSession::whereKey($this->learningSessionId)->update([
            'scenario_key' => $this->scenarioKey,
            'feedback_mode' => $this->feedbackMode,
            'messages' => $this->messages,
            'turns_count' => $this->learnerTurns(),
        ]);
    }

    private function completeStoredSession(): void
    {
        if ($this->learningSessionId !== null) {
            LearningSession::whereKey($this->learningSessionId)->update([
                'status' => LearningSession::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        }
    }

    private function incrementSpeakingTime(int $durationSeconds): void
    {
        if ($this->learningSessionId !== null) {
            LearningSession::whereKey($this->learningSessionId)
                ->increment('speaking_seconds', max(0, min(300, $durationSeconds)));
        }
    }

    public function learnerTurns(): int
    {
        return count(array_filter($this->messages, fn (array $message): bool => $message['role'] === 'user'));
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
