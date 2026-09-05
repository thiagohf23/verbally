<?php

namespace App\Services;

use App\Models\Flashcard;
use App\Models\ReviewLog;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class SpacedRepetitionService
{
    public const RATING_AGAIN = 1;

    public const RATING_GOOD = 3;

    public const RATING_EASY = 5;

    public const MINIMUM_EASE_FACTOR = 1.30;

    /**
     * Calculate the new SM-2 parameters for a flashcard given a rating.
     *
     * @return array{interval_days: int, ease_factor: float, repetitions: int, lapses: int, due_at: Carbon}
     */
    public function calculate(int $rating, int $repetitions, float $easeFactor, int $intervalDays, int $lapses = 0): array
    {
        if (! in_array($rating, [self::RATING_AGAIN, self::RATING_GOOD, self::RATING_EASY], true)) {
            throw new InvalidArgumentException("Invalid review rating: {$rating}. Expected 1 (Again), 3 (Good), or 5 (Easy).");
        }

        // Calculate new Ease Factor: EF' = EF + (0.1 - (5 - q) * (0.08 + (5 - q) * 0.02))
        $newEaseFactor = $easeFactor + (0.1 - (5 - $rating) * (0.08 + (5 - $rating) * 0.02));
        $newEaseFactor = max(self::MINIMUM_EASE_FACTOR, round($newEaseFactor, 2));

        if ($rating < self::RATING_GOOD) {
            // Failed recall (Again)
            $newRepetitions = 0;
            $newIntervalDays = 1;
            $newLapses = $lapses + 1;
        } else {
            // Successful recall (Good or Easy)
            if ($repetitions === 0) {
                $newIntervalDays = ($rating === self::RATING_EASY) ? 3 : 1;
            } elseif ($repetitions === 1) {
                $newIntervalDays = ($rating === self::RATING_EASY) ? 8 : 6;
            } else {
                $multiplier = ($rating === self::RATING_EASY) ? ($newEaseFactor * 1.3) : $newEaseFactor;
                $newIntervalDays = (int) round($intervalDays * $multiplier);
            }

            $newRepetitions = $repetitions + 1;
            $newLapses = $lapses;
        }

        $dueAt = Carbon::now()->addDays($newIntervalDays);

        return [
            'interval_days' => $newIntervalDays,
            'ease_factor' => $newEaseFactor,
            'repetitions' => $newRepetitions,
            'lapses' => $newLapses,
            'due_at' => $dueAt,
        ];
    }

    /**
     * Apply SM-2 review calculation, persist ReviewLog, and update Flashcard.
     */
    public function review(Flashcard $flashcard, int $rating): Flashcard
    {
        $calculated = $this->calculate(
            rating: $rating,
            repetitions: $flashcard->repetitions,
            easeFactor: (float) $flashcard->ease_factor,
            intervalDays: $flashcard->interval_days,
            lapses: $flashcard->lapses
        );

        ReviewLog::create([
            'flashcard_id' => $flashcard->id,
            'rating' => $rating,
            'interval_before' => $flashcard->interval_days,
            'interval_after' => $calculated['interval_days'],
            'ease_factor_before' => $flashcard->ease_factor,
            'ease_factor_after' => $calculated['ease_factor'],
            'reviewed_at' => Carbon::now(),
        ]);

        $flashcard->update([
            'interval_days' => $calculated['interval_days'],
            'ease_factor' => $calculated['ease_factor'],
            'repetitions' => $calculated['repetitions'],
            'lapses' => $calculated['lapses'],
            'due_at' => $calculated['due_at'],
        ]);

        return $flashcard;
    }
}
