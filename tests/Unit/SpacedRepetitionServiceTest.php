<?php

use App\Models\Flashcard;
use App\Models\ReviewLog;
use App\Services\SpacedRepetitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = new SpacedRepetitionService;
});

it('throws exception for invalid rating', function () {
    $this->service->calculate(rating: 4, repetitions: 0, easeFactor: 2.5, intervalDays: 0);
})->throws(InvalidArgumentException::class);

it('handles failed recall with rating 1 (Again)', function () {
    $result = $this->service->calculate(
        rating: SpacedRepetitionService::RATING_AGAIN,
        repetitions: 3,
        easeFactor: 2.50,
        intervalDays: 15,
        lapses: 1
    );

    expect($result['repetitions'])->toBe(0)
        ->and($result['interval_days'])->toBe(1)
        ->and($result['lapses'])->toBe(2)
        ->and($result['ease_factor'])->toBeLessThan(2.50);
});

it('handles initial good recall with rating 3 (Good)', function () {
    $firstPass = $this->service->calculate(
        rating: SpacedRepetitionService::RATING_GOOD,
        repetitions: 0,
        easeFactor: 2.50,
        intervalDays: 0,
        lapses: 0
    );

    expect($firstPass['repetitions'])->toBe(1)
        ->and($firstPass['interval_days'])->toBe(1)
        ->and($firstPass['lapses'])->toBe(0);

    $secondPass = $this->service->calculate(
        rating: SpacedRepetitionService::RATING_GOOD,
        repetitions: 1,
        easeFactor: $firstPass['ease_factor'],
        intervalDays: $firstPass['interval_days'],
        lapses: 0
    );

    expect($secondPass['repetitions'])->toBe(2)
        ->and($secondPass['interval_days'])->toBe(6);
});

it('handles easy recall with rating 5 (Easy)', function () {
    $firstPass = $this->service->calculate(
        rating: SpacedRepetitionService::RATING_EASY,
        repetitions: 0,
        easeFactor: 2.50,
        intervalDays: 0,
        lapses: 0
    );

    expect($firstPass['repetitions'])->toBe(1)
        ->and($firstPass['interval_days'])->toBe(3)
        ->and($firstPass['ease_factor'])->toBeGreaterThan(2.50);
});

it('maintains minimum ease factor floor of 1.30', function () {
    $result = $this->service->calculate(
        rating: SpacedRepetitionService::RATING_AGAIN,
        repetitions: 0,
        easeFactor: 1.30,
        intervalDays: 1,
        lapses: 5
    );

    expect($result['ease_factor'])->toBe(1.30);
});

it('persists review log and updates card state on review', function () {
    $card = Flashcard::factory()->create([
        'repetitions' => 0,
        'interval_days' => 0,
        'ease_factor' => 2.50,
        'lapses' => 0,
    ]);

    $updatedCard = $this->service->review($card, SpacedRepetitionService::RATING_GOOD);

    expect($updatedCard->repetitions)->toBe(1)
        ->and($updatedCard->interval_days)->toBe(1)
        ->and(ReviewLog::count())->toBe(1);

    $log = ReviewLog::first();
    expect($log->flashcard_id)->toBe($card->id)
        ->and($log->rating)->toBe(3)
        ->and($log->interval_before)->toBe(0)
        ->and($log->interval_after)->toBe(1);
});
