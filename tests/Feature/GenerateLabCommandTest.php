<?php

use App\Actions\GenerateLabCommand;
use App\Ai\Agents\LabCoachAgent;
use App\Ai\Agents\TranslateAgent;

beforeEach(function (): void {
    config(['ai.verbally.gemini_model' => 'test-model']);
});

it('executes a lab command and returns formatted outcome', function () {
    LabCoachAgent::fake(['Dear Sir, Please find the report attached.']);

    $result = app(GenerateLabCommand::class)->execute('I need that report ASAP', 'formal');

    expect($result['success'])->toBeTrue()
        ->and($result['response'])->toBe('Dear Sir, Please find the report attached.')
        ->and($result['error'])->toBeNull();
});

it('translates lab response into Portuguese', function () {
    TranslateAgent::fake(['Prezado senhor, segue o relatório em anexo.']);

    $result = app(GenerateLabCommand::class)->translate('Dear Sir, Please find the report attached.');

    expect($result['success'])->toBeTrue()
        ->and($result['translation'])->toBe('Prezado senhor, segue o relatório em anexo.')
        ->and($result['error'])->toBeNull();
});

it('handles missing configuration gracefully', function () {
    config(['ai.verbally.gemini_model' => null]);
    LabCoachAgent::fake()->preventStrayPrompts();

    $result = app(GenerateLabCommand::class)->execute('Hello', 'formal');

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('not configured');
});
