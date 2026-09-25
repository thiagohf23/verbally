<?php

use App\Models\LearningSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('renders an audio-first listening episode', function (): void {
    Livewire::test('pages::listen')->assertSee('A blocker at standup')->assertSee('Play conversation')->assertSee('Reveal transcript')->assertSee('Why was the checkout fix delayed?');
});

it('checks comprehension and records the listening session', function (): void {
    Livewire::test('pages::listen')
        ->call('noteReplay')
        ->call('answer', 'b')
        ->assertSee('Correct')
        ->call('completeEpisode')
        ->assertSet('completed', true)
        ->assertSee('Useful chunks');

    $session = LearningSession::first();
    expect($session->kind)->toBe(LearningSession::KIND_LISTENING)
        ->and($session->feedback['correct'])->toBeTrue()
        ->and($session->replay_count)->toBe(1);
});
