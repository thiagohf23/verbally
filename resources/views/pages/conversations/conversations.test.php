<?php

use App\Ai\Agents\ConversationTurnAgent;
use App\Models\Flashcard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    config(['ai.verbally.gemini_model' => 'gemini-test']);

    ConversationTurnAgent::fake(function (): string {
        return json_encode([
            'reply' => "Aww, thank you! I'm really glad we met up tonight. What kind of food or drinks do you usually love?",
            'native_alternative' => 'I come here all the time, and you look lovely tonight by the way.',
            'smooth_variation' => "I'm a regular here! The espresso is fantastic—and you look stunning, by the way.",
            'coaching_tip' => "Adding 'by the way' before a compliment makes it sound spontaneous and confident.",
            'key_vocabulary' => ['regular', 'by the way', 'meet up'],
        ], JSON_THROW_ON_ERROR);
    });
});

it('renders the conversations page with the first date scenario by default', function (): void {
    Livewire::test('pages::conversations')
        ->assertSee('First Date with Sarah')
        ->assertSee('Sarah')
        ->assertSee('Cozy candlelit Italian café')
        ->assertSee('Have you ever been to this place before?')
        ->assertSee('Date Tips & Starters');
});

it('allows switching to a different roleplay scenario', function (): void {
    Livewire::test('pages::conversations')
        ->call('switchScenario', 'coffee_shop')
        ->assertSet('scenarioKey', 'coffee_shop')
        ->assertSee('At the Coffee Shop')
        ->assertSee('Alex (Barista)')
        ->assertSee('What can I get started for you today?');
});

it('validates user input before sending message', function (): void {
    Livewire::test('pages::conversations')
        ->set('userMessage', '')
        ->call('sendMessage')
        ->assertHasErrors(['userMessage' => 'required']);
});

it('sends user message, returns partner reply, and displays coach feedback', function (): void {
    Livewire::test('pages::conversations')
        ->set('userMessage', 'Yes I come here always and you are very pretty today.')
        ->call('sendMessage')
        ->assertHasNoErrors()
        ->assertSet('userMessage', '')
        ->assertSee('Yes I come here always and you are very pretty today.')
        ->assertSee("Aww, thank you! I'm really glad we met up tonight.")
        ->assertSee('I come here all the time, and you look lovely tonight by the way.')
        ->assertSee("Adding 'by the way' before a compliment makes it sound spontaneous and confident.")
        ->assertSee('📌 Save Phrase to Deck');
});

it('saves a recommended phrase from the date conversation to the flashcards deck', function (): void {
    $component = Livewire::test('pages::conversations')
        ->set('userMessage', 'Yes I come here always.')
        ->call('sendMessage')
        ->assertSee('📌 Save Phrase to Deck');

    // The user turn is message with ID 2
    $component->call('saveToDeck', 2, 'native')
        ->assertSee('✓ Saved to Deck');

    $card = Flashcard::where('back', 'I come here all the time, and you look lovely tonight by the way.')->first();
    expect($card)->not->toBeNull()
        ->and($card->grammar_tag)->toContain('Conversation')
        ->and($card->deck->slug)->toBe('real-conversations');
});

it('restarts the dialogue back to initial state', function (): void {
    Livewire::test('pages::conversations')
        ->set('userMessage', 'Hello Sarah!')
        ->call('sendMessage')
        ->assertCount('messages', 3)
        ->call('clearConversation')
        ->assertCount('messages', 1)
        ->assertSee('Have you ever been to this place before?');
});
