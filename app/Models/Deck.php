<?php

namespace App\Models;

use Database\Factories\DeckFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    /** @use HasFactory<DeckFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_system',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    /** @return HasMany<Flashcard, $this> */
    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }
}
