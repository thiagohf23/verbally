<?php

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\ReviewLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->deck = Deck::factory()->create([
        'name' => 'General Practice',
        'slug' => 'general-practice',
    ]);

    $this->card = Flashcard::factory()->create([
        'deck_id' => $this->deck->id,
        'front' => 'Como dizer: Eu preciso ir embora.',
        'back' => 'I need to leave.',
        'grammar_tag' => 'Basics',
        'due_at' => now()->subMinute(),
        'interval_days' => 0,
        'repetitions' => 0,
    ]);
});

it('renders the review component and shows current due card', function () {
    Livewire::test('pages::review')
        ->assertSee('Como dizer: Eu preciso ir embora.')
        ->assertSee('General Practice')
        ->assertSee('Basics')
        ->assertSee('Daily Streak')
        ->assertSee('Due for Review');
});

it('flips the card to show English answer and evaluation controls', function () {
    Livewire::test('pages::review')
        ->assertSet('isFlipped', false)
        ->call('flip')
        ->assertSet('isFlipped', true)
        ->assertSee('I need to leave.')
        ->assertSee('Listen (Native TTS)')
        ->assertSee('Again')
        ->assertSee('Good')
        ->assertSee('Easy');
});

it('grades card as Good and updates interval and repetitions', function () {
    Livewire::test('pages::review')
        ->call('flip')
        ->call('grade', 3)
        ->assertSet('isFlipped', false);

    $this->card->refresh();
    expect($this->card->repetitions)->toBe(1)
        ->and($this->card->interval_days)->toBe(1)
        ->and($this->card->due_at->isFuture())->toBeTrue();

    expect(ReviewLog::where('flashcard_id', $this->card->id)->count())->toBe(1);
});

it('grades card as Again and resets repetitions', function () {
    $this->card->update(['repetitions' => 3, 'interval_days' => 10]);

    Livewire::test('pages::review')
        ->call('flip')
        ->call('grade', 1);

    $this->card->refresh();
    expect($this->card->repetitions)->toBe(0)
        ->and($this->card->interval_days)->toBe(1)
        ->and($this->card->lapses)->toBe(1);
});

it('allows filtering by deck', function () {
    $otherDeck = Deck::factory()->create(['name' => 'Advanced Idioms']);
    $otherCard = Flashcard::factory()->create([
        'deck_id' => $otherDeck->id,
        'front' => 'Bite the bullet',
        'back' => 'Enfrentar uma situação difícil',
        'due_at' => now()->subMinute(),
    ]);

    Livewire::test('pages::review')
        ->call('selectDeck', $otherDeck->id)
        ->assertSet('selectedDeckId', $otherDeck->id)
        ->assertSee('Bite the bullet');
});

it('can save a custom flashcard', function () {
    Livewire::test('pages::review')
        ->call('openAddModal')
        ->set('newDeckId', $this->deck->id)
        ->set('newFront', 'Como dizer: Eu concordo com você.')
        ->set('newBack', 'I agree with you.')
        ->set('newGrammarTag', 'Common Phrases')
        ->set('newExplanation', 'Agree takes with, not of.')
        ->call('saveCustomCard')
        ->assertSet('showAddModal', false);

    expect(Flashcard::where('front', 'Como dizer: Eu concordo com você.')->exists())->toBeTrue();
});
