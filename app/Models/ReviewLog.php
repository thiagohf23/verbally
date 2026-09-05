<?php

namespace App\Models;

use Database\Factories\ReviewLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewLog extends Model
{
    /** @use HasFactory<ReviewLogFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'flashcard_id',
        'rating',
        'interval_before',
        'interval_after',
        'ease_factor_before',
        'ease_factor_after',
        'reviewed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'interval_before' => 'integer',
            'interval_after' => 'integer',
            'ease_factor_before' => 'float',
            'ease_factor_after' => 'float',
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Flashcard, $this> */
    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }
}
