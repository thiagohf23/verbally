<?php

use App\Models\Flashcard;
use App\Models\LearningSession;
use App\Models\ReviewLog;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function reviewsToday(): int
    {
        return ReviewLog::whereDate('reviewed_at', today())->count();
    }

    #[Computed]
    public function listeningDone(): bool
    {
        return LearningSession::where('kind', LearningSession::KIND_LISTENING)->where('status', LearningSession::STATUS_COMPLETED)->whereDate('completed_at', today())->exists();
    }

    #[Computed]
    public function conversationDone(): bool
    {
        return LearningSession::where('kind', LearningSession::KIND_CONVERSATION)->where('status', LearningSession::STATUS_COMPLETED)->whereDate('completed_at', today())->where('turns_count', '>', 0)->exists();
    }

    #[Computed]
    public function dueCards(): int
    {
        return Flashcard::due()->count();
    }

    #[Computed]
    public function speakingMinutes(): int
    {
        return (int) ceil(LearningSession::whereDate('started_at', today())->sum('speaking_seconds') / 60);
    }

    #[Computed]
    public function recentFocus(): Collection
    {
        return LearningSession::whereNotNull('feedback')->latest('completed_at')->limit(10)->get()->flatMap(fn (LearningSession $session): array => $session->feedback['next_focus'] ?? [])->countBy()->sortDesc()->take(4);
    }

    #[Computed]
    public function recentSessions(): Collection
    {
        return LearningSession::where('status', LearningSession::STATUS_COMPLETED)->latest('completed_at')->limit(4)->get();
    }
};
