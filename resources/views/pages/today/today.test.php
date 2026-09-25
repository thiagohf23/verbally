<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('renders the guided daily routine', function (): void {
    Livewire::test('pages::today')
        ->assertSee('Listen. Respond. Notice. Repeat.')
        ->assertSee('Audio flashcard warm-up')
        ->assertSee('Understand a short scene')
        ->assertSee('Take your turn');
});
