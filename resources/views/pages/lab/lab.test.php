<?php

use App\Ai\Agents\LabCoachAgent;
use App\Ai\Agents\TranslateAgent;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    config(['ai.verbally.gemini_model' => 'gemini-test']);
    LabCoachAgent::fake([
        'Coaching Result: Here is the revised professional text.',
    ]);
    TranslateAgent::fake([
        'Resultado de coaching: Aqui está o texto profissional revisado.',
    ]);
});

it('renders the lab page with empty state and coaching commands', function (): void {
    Livewire::test('pages::lab')
        ->assertSee('Targeted coaching,')
        ->assertSee('No coaching runs yet.')
        ->assertSee('Make it Formal')
        ->assertSee('Make it Casual')
        ->assertSee('Enrich Vocabulary')
        ->assertSee('Practice Quiz')
        ->assertSee('Custom Command');
});

it('validates required text input', function (): void {
    Livewire::test('pages::lab')
        ->set('text', '')
        ->call('runCommand')
        ->assertHasErrors(['text' => 'required']);
});

it('requires customPrompt when command is custom', function (): void {
    Livewire::test('pages::lab')
        ->set('text', 'Hello world')
        ->set('command', 'custom')
        ->set('customPrompt', '')
        ->call('runCommand')
        ->assertHasErrors(['customPrompt' => 'required']);
});

it('executes a coaching command and adds to results', function (): void {
    Livewire::test('pages::lab')
        ->set('text', 'Hey bro I need that report ASAP.')
        ->set('command', 'formal')
        ->call('runCommand')
        ->assertHasNoErrors()
        ->assertSet('processing', false)
        ->assertCount('results', 1)
        ->assertSee('Make it Formal')
        ->assertSee('Coaching Result: Here is the revised professional text.');
});

it('translates a coaching result into Portuguese', function (): void {
    $component = Livewire::test('pages::lab')
        ->set('text', 'Hey bro I need that report ASAP.')
        ->set('command', 'formal')
        ->call('runCommand');

    $component->call('translateResult', 1)
        ->assertSee('Tradução (PT-BR)')
        ->assertSee('Resultado de coaching: Aqui está o texto profissional revisado.');
});

it('clears lab history', function (): void {
    Livewire::test('pages::lab')
        ->set('text', 'Sample text.')
        ->call('runCommand')
        ->assertCount('results', 1)
        ->call('clearLab')
        ->assertCount('results', 0)
        ->assertSee('No coaching runs yet.');
});
