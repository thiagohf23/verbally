<?php

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\ReviewLog;
use App\Services\SpacedRepetitionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?int $selectedDeckId = null;

    public ?int $currentCardId = null;

    public bool $isFlipped = false;

    public bool $audioFirst = false;

    public string $mode = 'due'; // 'due' or 'all'

    public ?string $filterTag = null;

    public ?string $feedbackMessage = null;

    // Quick Add Modal state
    public bool $showAddModal = false;

    public string $newFront = '';

    public string $newBack = '';

    public string $newGrammarTag = '';

    public string $newExplanation = '';

    public ?int $newDeckId = null;

    public function mount(): void
    {
        $this->audioFirst = request()->string('mode')->value() === 'audio';
        $this->loadNextCard();
    }

    public function setPromptMode(string $mode): void
    {
        $this->audioFirst = $mode === 'audio';
        $this->isFlipped = false;
    }

    public function loadNextCard(): void
    {
        $query = Flashcard::query();

        if ($this->selectedDeckId !== null) {
            $query->where('deck_id', $this->selectedDeckId);
        }

        if ($this->filterTag !== null) {
            $query->where('grammar_tag', $this->filterTag);
        }

        if ($this->mode === 'due') {
            $query->where('due_at', '<=', now())
                ->orderBy('due_at', 'asc');
        } else {
            // Cram/all mode: cycle through cards
            if ($this->currentCardId !== null) {
                $query->where('id', '!=', $this->currentCardId);
            }
            $query->inRandomOrder();
        }

        $card = $query->first();

        $this->currentCardId = $card?->id;
        $this->isFlipped = false;
    }

    public function flip(): void
    {
        $this->isFlipped = true;
    }

    public function grade(int $rating, SpacedRepetitionService $spacedRepetition): void
    {
        if ($this->currentCardId === null || ! in_array($rating, [1, 3, 5], true)) {
            return;
        }

        $card = Flashcard::find($this->currentCardId);

        if (! $card) {
            $this->loadNextCard();

            return;
        }

        $updated = $spacedRepetition->review($card, $rating);

        $this->feedbackMessage = match ($rating) {
            1 => 'Needs work · Reviewing again soon (1d)',
            3 => "Good job! Next in {$updated->interval_days} ".($updated->interval_days === 1 ? 'day' : 'days'),
            5 => "Easy! Mastered for {$updated->interval_days} days",
            default => null,
        };

        $this->loadNextCard();
    }

    public function selectDeck(?int $deckId): void
    {
        $this->selectedDeckId = $deckId;
        $this->loadNextCard();
    }

    public function setMode(string $mode): void
    {
        $this->mode = in_array($mode, ['due', 'all'], true) ? $mode : 'due';
        $this->loadNextCard();
    }

    public function filterByTag(?string $tag): void
    {
        $this->filterTag = $tag;
        $this->loadNextCard();
    }

    public function resetDueCards(?int $deckId = null): void
    {
        $query = Flashcard::query();
        if ($deckId !== null) {
            $query->where('deck_id', $deckId);
        }

        $query->update(['due_at' => now()]);
        $this->mode = 'due';
        $this->loadNextCard();
    }

    public function openAddModal(): void
    {
        $this->newDeckId = $this->selectedDeckId ?? Deck::where('slug', 'my-corrections')->value('id') ?? Deck::first()?->id;
        $this->newFront = '';
        $this->newBack = '';
        $this->newGrammarTag = '';
        $this->newExplanation = '';
        $this->showAddModal = true;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
    }

    public function saveCustomCard(): void
    {
        $this->validate([
            'newFront' => ['required', 'string', 'max:500'],
            'newBack' => ['required', 'string', 'max:500'],
            'newGrammarTag' => ['nullable', 'string', 'max:50'],
            'newExplanation' => ['nullable', 'string', 'max:1000'],
            'newDeckId' => ['required', 'exists:decks,id'],
        ]);

        $created = Flashcard::create([
            'deck_id' => $this->newDeckId,
            'front' => trim($this->newFront),
            'back' => trim($this->newBack),
            'grammar_tag' => $this->newGrammarTag ? trim($this->newGrammarTag) : 'Custom',
            'explanation' => $this->newExplanation ? trim($this->newExplanation) : null,
            'audio_text' => trim($this->newBack),
            'interval_days' => 0,
            'ease_factor' => 2.50,
            'repetitions' => 0,
            'lapses' => 0,
            'due_at' => now(),
        ]);

        $this->showAddModal = false;
        $this->currentCardId = $created->id;
        $this->isFlipped = false;
    }

    #[Computed]
    public function currentCard(): ?Flashcard
    {
        if ($this->currentCardId === null) {
            return null;
        }

        return Flashcard::with('deck')->find($this->currentCardId);
    }

    #[Computed]
    public function decks(): Collection
    {
        return Deck::query()
            ->withCount([
                'flashcards',
                'flashcards as due_count' => fn ($query) => $query->where('due_at', '<=', now()),
            ])
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function totalDueCount(): int
    {
        $query = Flashcard::where('due_at', '<=', now());
        if ($this->selectedDeckId !== null) {
            $query->where('deck_id', $this->selectedDeckId);
        }

        return $query->count();
    }

    #[Computed]
    public function totalCardsCount(): int
    {
        $query = Flashcard::query();
        if ($this->selectedDeckId !== null) {
            $query->where('deck_id', $this->selectedDeckId);
        }

        return $query->count();
    }

    #[Computed]
    public function dailyStreak(): int
    {
        $dates = ReviewLog::query()
            ->selectRaw('DATE(reviewed_at) as log_date')
            ->distinct()
            ->orderByDesc('log_date')
            ->pluck('log_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->all();

        if (empty($dates)) {
            return 0;
        }

        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        // Streak must be active today or yesterday
        if ($dates[0] !== $today && $dates[0] !== $yesterday) {
            return 0;
        }

        $streak = 0;
        $expectedDate = Carbon::parse($dates[0]);

        foreach ($dates as $dateStr) {
            $date = Carbon::parse($dateStr);
            if ($date->equalTo($expectedDate)) {
                $streak++;
                $expectedDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    #[Computed]
    public function retentionRate(): int
    {
        $recentLogs = ReviewLog::where('reviewed_at', '>=', now()->subDays(30))->get();

        if ($recentLogs->isEmpty()) {
            return 100;
        }

        $passed = $recentLogs->filter(fn ($log) => $log->rating >= 3)->count();

        return (int) round(($passed / $recentLogs->count()) * 100);
    }

    #[Computed]
    public function reviewsTodayCount(): int
    {
        return ReviewLog::whereDate('reviewed_at', Carbon::today())->count();
    }

    #[Computed]
    public function grammarWeaknesses(): Collection
    {
        return Flashcard::query()
            ->selectRaw('grammar_tag, sum(lapses) as total_lapses, count(*) as card_count')
            ->whereNotNull('grammar_tag')
            ->groupBy('grammar_tag')
            ->orderByDesc('total_lapses')
            ->limit(5)
            ->get();
    }
};
