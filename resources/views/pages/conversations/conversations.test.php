<?php

use App\Ai\Agents\ConversationTurnAgent;
use App\Ai\Agents\PronunciationCoachAgent;
use App\Ai\Agents\SessionFeedbackAgent;
use App\Models\Flashcard;
use App\Models\LearningSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    config(['ai.verbally.gemini_model' => 'gemini-test']);

    ConversationTurnAgent::fake([json_encode([
        'reply' => "Aww, thank you! I'm really glad we met up tonight. What kind of food or drinks do you usually love?",
        'native_alternative' => 'I come here all the time, and you look lovely tonight by the way.',
        'smooth_variation' => "I'm a regular here! The espresso is fantastic—and you look stunning, by the way.",
        'coaching_tip' => "Adding 'by the way' before a compliment makes it sound spontaneous and confident.",
        'key_vocabulary' => ['regular', 'by the way', 'meet up'],
    ], JSON_THROW_ON_ERROR)]);

    SessionFeedbackAgent::fake([json_encode([
        'summary_pt' => 'Você sustentou a conversa com clareza.',
        'wins' => ['Você respondeu diretamente.', 'Você fez a conversa avançar.'],
        'corrections' => [['original' => 'I come here always', 'better' => 'I come here all the time', 'reason_pt' => 'Essa combinação soa mais natural.']],
        'phrases_to_keep' => ['all the time', 'by the way'],
        'next_focus' => ['Praticar ritmo em perguntas.'],
    ], JSON_THROW_ON_ERROR)]);

    PronunciationCoachAgent::fake([json_encode([
        'transcript' => 'I think this place is really nice.',
        'clarity_score' => 84,
        'summary_pt' => 'A mensagem ficou clara; trabalhe o TH de think.',
        'strengths' => ['Bom ritmo geral.'],
        'sound_focus' => [[
            'word' => 'think',
            'sound' => '/θ/',
            'ipa' => '/θɪŋk/',
            'mouth_tip_pt' => 'Encoste suavemente a ponta da língua entre os dentes e deixe o ar passar.',
            'practice_pt' => 'Repita think três vezes sem transformar o som em F.',
        ]],
    ], JSON_THROW_ON_ERROR)]);
});

it('renders an audio-first conversation by default', function (): void {
    Livewire::test('pages::conversations')
        ->assertSee('First Date with Sarah')
        ->assertSee('transcript hidden by default')
        ->assertSee('Show transcript')
        ->assertSee('Start speaking');
});

it('allows switching to a different roleplay scenario', function (): void {
    Livewire::test('pages::conversations')
        ->call('switchScenario', 'coffee_shop')
        ->assertSet('scenarioKey', 'coffee_shop')
        ->assertSee('At the Coffee Shop')
        ->assertSee('Alex (Barista)');
});

it('validates typed input before sending a message', function (): void {
    Livewire::test('pages::conversations')
        ->set('userMessage', '')
        ->call('sendMessage')
        ->assertHasErrors(['userMessage' => 'required']);
});

it('keeps turn coaching hidden in fluency mode and persists progress', function (): void {
    Livewire::test('pages::conversations')
        ->set('userMessage', 'Yes I come here always and you are very pretty today.')
        ->call('sendMessage')
        ->assertHasNoErrors()
        ->assertSet('messages.1.native_alternative', 'I come here all the time, and you look lovely tonight by the way.')
        ->assertDontSee('Turn feedback');

    expect(LearningSession::first()->turns_count)->toBe(1);
});

it('shows coaching after every turn in study mode', function (): void {
    Livewire::test('pages::conversations')
        ->call('setFeedbackMode', 'study')
        ->set('userMessage', 'Yes I come here always.')
        ->call('sendMessage')
        ->assertSee('Turn feedback')
        ->assertSee('I come here all the time, and you look lovely tonight by the way.');
});

it('saves a recommended phrase to the conversation deck', function (): void {
    Livewire::test('pages::conversations')
        ->set('userMessage', 'Yes I come here always.')
        ->call('sendMessage')
        ->call('saveToDeck', 2, 'native')
        ->assertSet('messages.1.saved_to_deck', true);

    $card = Flashcard::where('back', 'I come here all the time, and you look lovely tonight by the way.')->first();
    expect($card)->not->toBeNull()->and($card->deck->slug)->toBe('real-conversations');
});

it('restarts the dialogue in a fresh persisted session', function (): void {
    Livewire::test('pages::conversations')
        ->set('userMessage', 'Hello Sarah!')
        ->call('sendMessage')
        ->call('clearConversation')
        ->assertCount('messages', 1)
        ->assertSet('sessionCompleted', false);

    expect(LearningSession::count())->toBe(2);
});

it('finishes a fluency session with focused feedback', function (): void {
    Livewire::test('pages::conversations')
        ->set('userMessage', 'I come here always.')
        ->call('sendMessage')
        ->call('finishSession')
        ->assertSet('sessionCompleted', true)
        ->assertSee('Você sustentou a conversa com clareza.')
        ->assertSee('I come here all the time');

    expect(LearningSession::first()->status)->toBe(LearningSession::STATUS_COMPLETED);
});

it('analyzes a voice recording and stores pronunciation guidance', function (): void {
    Livewire::test('pages::conversations')
        ->set('voiceRecording', UploadedFile::fake()->create('response.webm', 120, 'audio/webm'))
        ->call('sendVoiceMessage', 'I think this place is really nice.', 8)
        ->assertHasNoErrors()
        ->assertSet('messages.1.source', 'voice')
        ->assertSet('messages.1.pronunciation.clarity_score', 84)
        ->assertSee('Pronunciation coach')
        ->assertSee('ponta da língua entre os dentes');

    expect(LearningSession::first()->speaking_seconds)->toBe(8);
});
