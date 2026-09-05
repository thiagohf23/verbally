<?php

namespace App\Models;

use Database\Factories\FlashcardFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flashcard extends Model
{
    /** @use HasFactory<FlashcardFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'deck_id',
        'front',
        'back',
        'grammar_tag',
        'explanation',
        'audio_text',
        'interval_days',
        'ease_factor',
        'repetitions',
        'lapses',
        'due_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'interval_days' => 'integer',
            'ease_factor' => 'float',
            'repetitions' => 'integer',
            'lapses' => 'integer',
            'due_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Deck, $this> */
    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    /** @return HasMany<ReviewLog, $this> */
    public function reviewLogs(): HasMany
    {
        return $this->hasMany(ReviewLog::class);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query->where('due_at', '<=', now());
    }

    public function isDue(): bool
    {
        return $this->due_at !== null && $this->due_at->isPast();
    }
}
