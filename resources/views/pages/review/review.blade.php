<div class="verbally-shell min-h-screen bg-[#fcfdfa] text-[#17352a] dark:bg-[#0e1712] dark:text-[#ecf4ee]" x-data="{
    isSpeaking: false,
    speak(text) {
        if (!text) return;
        this.isSpeaking = true;
        window.verballySpeak(text, () => { this.isSpeaking = true; }, () => { this.isSpeaking = false; }, { rate: Number(localStorage.getItem('verbally_playback_rate') || 0.9) });
    }
}" @keydown.window="
    if ($event.target.tagName !== 'INPUT' && $event.target.tagName !== 'TEXTAREA') {
        if ($event.code === 'Space' && !$wire.isFlipped) {
            $event.preventDefault();
            $wire.flip();
        } else if ($wire.isFlipped) {
            if ($event.key === '1') { $event.preventDefault(); $wire.grade(1); }
            if ($event.key === '2') { $event.preventDefault(); $wire.grade(3); }
            if ($event.key === '3') { $event.preventDefault(); $wire.grade(5); }
        }
    }
">
    @island(name: 'header', always: true)
        <x-nav active="review" class="verbally-learning-nav">
            <span class="verbally-session">🔥 {{ $this->dailyStreak }}d streak · {{ $this->totalDueCount }} due</span>
            <x-theme-toggle />
            <button type="button" wire:click="openAddModal" class="verbally-clear" aria-label="Add new flashcard">
                + New Card
            </button>
        </x-nav>
    @endisland

    <!-- Full-Width Main Workspace Container -->
    <main class="mx-auto w-full max-w-[1440px] px-4 py-6 sm:px-6 sm:py-8 lg:px-10">
        <!-- Top Stats Row: Uses full width across 4 responsive columns -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <div class="flex items-center gap-3.5 rounded-xl border border-[#e2e6de] bg-white p-4 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                <div class="flex size-11 flex-none items-center justify-center rounded-xl bg-[#fef3c7] text-xl text-[#d97706] dark:bg-[#362a15] dark:text-[#f59e0b]">🔥</div>
                <div class="min-w-0">
                    <div class="truncate text-xs font-medium text-[#737c6d] dark:text-[#8ba393]">Daily Streak</div>
                    <div class="text-xl font-bold tracking-tight text-[#1a2e22] dark:text-[#e4efe7]">{{ $this->dailyStreak }} {{ $this->dailyStreak === 1 ? 'day' : 'days' }}</div>
                </div>
            </div>

            <div class="flex items-center gap-3.5 rounded-xl border border-[#e2e6de] bg-white p-4 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                <div class="flex size-11 flex-none items-center justify-center rounded-xl bg-[#edf6ef] text-xl text-[#2f7a55] dark:bg-[#1c3325] dark:text-[#52b788]">🎯</div>
                <div class="min-w-0">
                    <div class="truncate text-xs font-medium text-[#737c6d] dark:text-[#8ba393]">Due for Review</div>
                    <div class="text-xl font-bold tracking-tight text-[#1a2e22] dark:text-[#e4efe7]">{{ $this->totalDueCount }} {{ $this->totalDueCount === 1 ? 'card' : 'cards' }}</div>
                </div>
            </div>

            <div class="flex items-center gap-3.5 rounded-xl border border-[#e2e6de] bg-white p-4 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                <div class="flex size-11 flex-none items-center justify-center rounded-xl bg-[#e0f2fe] text-xl text-[#0284c7] dark:bg-[#152e3d] dark:text-[#38bdf8]">📈</div>
                <div class="min-w-0">
                    <div class="truncate text-xs font-medium text-[#737c6d] dark:text-[#8ba393]">Retention Rate</div>
                    <div class="text-xl font-bold tracking-tight text-[#1a2e22] dark:text-[#e4efe7]">{{ $this->retentionRate }}%</div>
                </div>
            </div>

            <div class="flex items-center gap-3.5 rounded-xl border border-[#e2e6de] bg-white p-4 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                <div class="flex size-11 flex-none items-center justify-center rounded-xl bg-[#f3e8ff] text-xl text-[#9333ea] dark:bg-[#2e1d3d] dark:text-[#c084fc]">⚡</div>
                <div class="min-w-0">
                    <div class="truncate text-xs font-medium text-[#737c6d] dark:text-[#8ba393]">Reviewed Today</div>
                    <div class="text-xl font-bold tracking-tight text-[#1a2e22] dark:text-[#e4efe7]">{{ $this->reviewsTodayCount }} {{ $this->reviewsTodayCount === 1 ? 'card' : 'cards' }}</div>
                </div>
            </div>
        </div>

        <!-- Feedback Notification -->
        @if ($feedbackMessage)
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition class="mb-6 flex items-center justify-between rounded-xl border border-[#cde5d7] bg-[#edf8f1] px-5 py-3 text-xs font-medium text-[#1e5337] dark:border-[#234d36] dark:bg-[#132c1e] dark:text-[#74cca0]">
                <div class="flex items-center gap-2">
                    <span>✨</span>
                    <span>{{ $feedbackMessage }}</span>
                </div>
                <button type="button" @click="show = false" class="text-[#3b7754] hover:text-[#1e5337] dark:text-[#74cca0]">✕</button>
            </div>
        @endif

        <!-- Responsive Two-Column Studio Layout -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8">
            <!-- Left Sidebar: Decks Directory & Grammar Weakness Radar (4 Cols on Desktop) -->
            <div class="flex flex-col gap-6 lg:col-span-4">
                <!-- Decks Selection Panel -->
                <div class="rounded-2xl border border-[#e2e6de] bg-white p-5 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                    <div class="mb-3 flex items-center justify-between border-b border-[#edf1ea] pb-3 dark:border-[#223528]">
                        <h2 class="font-display text-sm font-bold uppercase tracking-wider text-[#172c21] dark:text-[#ecf4ee]">Decks</h2>
                        <button
                            type="button"
                            wire:click="openAddModal"
                            class="text-xs font-semibold text-[#2f7a55] hover:underline dark:text-[#52b788]"
                        >
                            + New Card
                        </button>
                    </div>

                    <!-- Decks List -->
                    <div class="space-y-1.5">
                        <button
                            type="button"
                            wire:click="selectDeck(null)"
                            class="flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-semibold transition-all {{ $selectedDeckId === null ? 'bg-[#2f7a55] text-white shadow-xs dark:bg-[#235c40]' : 'text-[#4c5647] hover:bg-[#f3f6f1] dark:text-[#a0b5a6] dark:hover:bg-[#1a2c20]' }}"
                        >
                            <span class="flex items-center gap-2">
                                <span>📚</span>
                                <span>All Decks</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                @if ($this->decks->sum('due_count') > 0)
                                    <span class="rounded-full bg-[#e35050] px-2 py-0.5 text-[10px] font-bold text-white">{{ $this->decks->sum('due_count') }} due</span>
                                @endif
                                <span class="opacity-70">({{ $this->decks->sum('flashcards_count') }})</span>
                            </span>
                        </button>

                        @foreach ($this->decks as $deck)
                            <button
                                type="button"
                                wire:key="sidebar-deck-{{ $deck->id }}"
                                wire:click="selectDeck({{ $deck->id }})"
                                class="flex w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-xs font-semibold transition-all {{ $selectedDeckId === $deck->id ? 'bg-[#2f7a55] text-white shadow-xs dark:bg-[#235c40]' : 'text-[#4c5647] hover:bg-[#f3f6f1] dark:text-[#a0b5a6] dark:hover:bg-[#1a2c20]' }}"
                            >
                                <span class="truncate pr-2 text-left">
                                    {{ $deck->name }}
                                </span>
                                <span class="flex flex-none items-center gap-1.5">
                                    @if ($deck->due_count > 0)
                                        <span class="rounded-full bg-[#e35050] px-2 py-0.5 text-[10px] font-bold text-white">{{ $deck->due_count }} due</span>
                                    @endif
                                    <span class="opacity-70">({{ $deck->flashcards_count }})</span>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <!-- Study Mode Selector -->
                    <div class="mt-4 border-t border-[#edf1ea] pt-3.5 dark:border-[#223528]">
                        <div class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-[#828c7d] dark:text-[#7f9888]">Practice Mode</div>
                        <div class="grid grid-cols-2 gap-1.5 rounded-xl bg-[#f4f7f2] p-1 dark:bg-[#101b14]">
                            <button
                                type="button"
                                wire:click="setMode('due')"
                                class="rounded-lg py-1.5 text-xs font-semibold transition-all {{ $mode === 'due' ? 'bg-white text-[#1a2e22] shadow-xs dark:bg-[#1d3023] dark:text-[#ecf4ee]' : 'text-[#636c5f] hover:text-[#1a2e22] dark:text-[#8ba393]' }}"
                            >
                                Due Only
                            </button>
                            <button
                                type="button"
                                wire:click="setMode('all')"
                                class="rounded-lg py-1.5 text-xs font-semibold transition-all {{ $mode === 'all' ? 'bg-white text-[#1a2e22] shadow-xs dark:bg-[#1d3023] dark:text-[#ecf4ee]' : 'text-[#636c5f] hover:text-[#1a2e22] dark:text-[#8ba393]' }}"
                            >
                                Continuous Cram
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 border-t border-[#edf1ea] pt-3.5 dark:border-[#223528]">
                        <div class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-[#828c7d] dark:text-[#7f9888]">Prompt style</div>
                        <div class="grid grid-cols-2 gap-1.5 rounded-xl bg-[#f4f7f2] p-1 dark:bg-[#101b14]">
                            <button type="button" wire:click="setPromptMode('audio')" class="rounded-lg py-1.5 text-xs font-semibold {{ $audioFirst ? 'bg-white text-[#1a2e22] shadow-xs dark:bg-[#1d3023] dark:text-[#ecf4ee]' : 'text-[#636c5f] dark:text-[#8ba393]' }}">🎧 Audio first</button>
                            <button type="button" wire:click="setPromptMode('classic')" class="rounded-lg py-1.5 text-xs font-semibold {{ ! $audioFirst ? 'bg-white text-[#1a2e22] shadow-xs dark:bg-[#1d3023] dark:text-[#ecf4ee]' : 'text-[#636c5f] dark:text-[#8ba393]' }}">Text prompt</button>
                        </div>
                    </div>
                </div>

                <!-- Grammar Weakness Radar -->
                @if ($this->grammarWeaknesses->isNotEmpty())
                    <div class="rounded-2xl border border-[#e2e6de] bg-white p-5 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                        <div class="mb-3 flex items-center justify-between border-b border-[#edf1ea] pb-3 dark:border-[#223528]">
                            <div>
                                <h3 class="font-display text-sm font-bold uppercase tracking-wider text-[#172c21] dark:text-[#ecf4ee]">Weakness Radar</h3>
                                <p class="text-[11px] text-[#717b6d] dark:text-[#8ba393]">Most frequent memory lapses</p>
                            </div>
                            <a href="{{ url('/lab') }}" class="text-xs font-semibold text-[#2f7a55] hover:underline dark:text-[#52b788]">
                                Lab →
                            </a>
                        </div>

                        <div class="space-y-2">
                            @foreach ($this->grammarWeaknesses as $weakness)
                                <div class="flex items-center justify-between rounded-xl border border-[#edf1ea] bg-[#f9faf8] px-3.5 py-2.5 dark:border-[#243a2c] dark:bg-[#121f16]">
                                    <div>
                                        <div class="text-xs font-bold text-[#1f3729] dark:text-[#d7e5dc]">{{ $weakness->grammar_tag }}</div>
                                        <div class="text-[11px] text-[#717b6d] dark:text-[#8ba393]">{{ $weakness->card_count }} cards</div>
                                    </div>
                                    <span class="rounded-full bg-[#fee2e2] px-2 py-0.5 text-[11px] font-bold text-[#991b1b] dark:bg-[#3d1c1c] dark:text-[#f87171]">
                                        {{ $weakness->total_lapses }} {{ $weakness->total_lapses === 1 ? 'lapse' : 'lapses' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Flashcard Study Stage (8 Cols on Desktop) -->
            <div class="flex flex-col lg:col-span-8">
                @if ($this->currentCard)
                    <div class="flex flex-1 flex-col justify-between rounded-2xl border border-[#dde3d8] bg-white p-6 shadow-sm sm:p-10 dark:border-[#243a2c] dark:bg-[#15231a]">
                        <!-- Card Header Meta -->
                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#edf0ea] pb-4 dark:border-[#223528]">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-[#edf6ef] px-2.5 py-1 text-xs font-bold text-[#2f7a55] dark:bg-[#1a3324] dark:text-[#52b788]">
                                        {{ $this->currentCard->deck->name }}
                                    </span>
                                    @if ($this->currentCard->grammar_tag)
                                        <span class="rounded-md border border-[#d8e2d4] bg-[#f7f9f6] px-2.5 py-1 text-xs font-semibold text-[#4e5b4b] dark:border-[#263e2e] dark:bg-[#132218] dark:text-[#9bb3a2]">
                                            {{ $this->currentCard->grammar_tag }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-[#889284] dark:text-[#799182]">
                                    Interval: <strong class="font-semibold text-[#172c21] dark:text-[#e4efe7]">{{ $this->currentCard->interval_days }}d</strong> · Reps: <strong class="font-semibold text-[#172c21] dark:text-[#e4efe7]">{{ $this->currentCard->repetitions }}</strong> · Lapses: <strong class="font-semibold text-[#172c21] dark:text-[#e4efe7]">{{ $this->currentCard->lapses }}</strong>
                                </div>
                            </div>

                            <!-- Card Stimulus / Prompt Front -->
                            <div class="mt-8">
                                @if ($audioFirst && ! $isFlipped)
                                    <div class="flex flex-col items-center rounded-3xl bg-[#17352a] px-6 py-10 text-center text-white dark:bg-[#0c1710]">
                                        <span class="flex size-16 items-center justify-center rounded-full bg-white/10 text-3xl">🎧</span>
                                        <div class="mt-4 text-xs font-bold uppercase tracking-[.14em] text-[#72d19b]">Listen before reading</div>
                                        <p class="mt-2 max-w-md text-sm text-white/65">Say the phrase aloud or explain what you understood, then reveal the answer.</p>
                                        <button type="button" @click="speak('{{ addslashes($this->currentCard->audio_text ?? $this->currentCard->back) }}')" class="mt-5 rounded-xl bg-[#65cc91] px-5 py-3 text-sm font-bold text-[#113421]">🔊 Play phrase</button>
                                    </div>
                                @else
                                    <div class="mb-3 text-xs font-bold uppercase tracking-wider text-[#8e988a] dark:text-[#7a9283]">Prompt / Em Português:</div>
                                    <div class="font-display text-2xl font-medium leading-snug text-[#172c21] sm:text-3xl lg:text-4xl dark:text-[#ecf4ee]">{{ $this->currentCard->front }}</div>
                                @endif
                            </div>

                            <!-- Card Back Revelation (when flipped) -->
                            @if ($isFlipped)
                                <div class="mt-8 border-t border-[#edf0ea] pt-8 dark:border-[#223528]">
                                    <div class="mb-3 flex items-center justify-between">
                                        <div class="text-xs font-bold uppercase tracking-wider text-[#2f7a55] dark:text-[#52b788]">
                                            English Target:
                                        </div>
                                        <button
                                            type="button"
                                            @click="speak('{{ addslashes($this->currentCard->audio_text ?? $this->currentCard->back) }}')"
                                            class="inline-flex items-center gap-2 rounded-xl border border-[#d8e0d4] bg-[#f8faf7] px-3.5 py-1.5 text-xs font-semibold text-[#2d5741] transition-all hover:bg-[#edf6ef] dark:border-[#284131] dark:bg-[#1c2d22] dark:text-[#9bc2ac] dark:hover:bg-[#233a2c]"
                                        >
                                            <span :class="isSpeaking ? 'animate-bounce' : ''">🔊</span>
                                            <span>Listen (Native TTS)</span>
                                        </button>
                                    </div>

                                    <div class="font-display text-3xl sm:text-4xl lg:text-5xl font-semibold leading-tight text-[#163527] dark:text-[#5fe1a4]">
                                        {{ $this->currentCard->back }}
                                    </div>

                                    @if ($this->currentCard->explanation)
                                        <div class="mt-6 rounded-2xl border border-[#e2e9df] bg-[#f6f9f5] p-4 text-sm text-[#4c5c50] dark:border-[#263c2e] dark:bg-[#132218] dark:text-[#a0b8a8]">
                                            <div class="mb-1 font-semibold text-[#27533c] dark:text-[#71c397]">💡 Why this works:</div>
                                            <div class="leading-relaxed">{{ $this->currentCard->explanation }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer / Controls -->
                        <div class="mt-12">
                            @if (! $isFlipped)
                                <div class="flex flex-col items-center justify-center border-t border-[#f0f3ed] pt-8 dark:border-[#223528]">
                                    <button
                                        type="button"
                                        wire:click="flip"
                                        class="inline-flex items-center gap-3 rounded-2xl bg-[#2f7a55] px-8 py-3.5 text-base font-semibold text-white shadow-md transition-all hover:bg-[#256345] hover:shadow-lg active:scale-98 dark:bg-[#276445] dark:hover:bg-[#307c56]"
                                    >
                                        <span>Show English Answer</span>
                                        <kbd class="rounded-md bg-[#20533a] px-2 py-0.5 text-xs font-mono text-white/90">Space</kbd>
                                    </button>
                                    <p class="mt-2.5 text-xs text-[#8c9688] dark:text-[#7a9283]">Press Space on keyboard or click button</p>
                                </div>
                            @else
                                <div class="border-t border-[#f0f3ed] pt-6 dark:border-[#223528]">
                                    <div class="mb-3 text-center text-xs font-semibold text-[#717c6c] dark:text-[#889f90]">
                                        How well did you recall this phrase?
                                    </div>
                                    <div class="grid grid-cols-3 gap-3 sm:gap-4">
                                        <button
                                            type="button"
                                            wire:click="grade(1)"
                                            class="flex flex-col items-center justify-center rounded-2xl border border-[#fca5a5] bg-[#fff5f5] p-3.5 text-center transition-all hover:bg-[#fee2e2] hover:shadow-xs active:scale-98 dark:border-[#522525] dark:bg-[#2d1616] dark:hover:bg-[#3d1c1c]"
                                        >
                                            <div class="flex items-center gap-1.5 text-sm font-bold text-[#b91c1c] dark:text-[#f87171]">
                                                <span>Again</span>
                                                <kbd class="rounded bg-black/10 px-1.5 py-0.5 text-[10px] font-mono dark:bg-white/10">1</kbd>
                                            </div>
                                            <div class="mt-0.5 text-[11px] text-[#dc2626] dark:text-[#fca5a5]">Reset (1d)</div>
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="grade(3)"
                                            class="flex flex-col items-center justify-center rounded-2xl border border-[#86efac] bg-[#f0fdf4] p-3.5 text-center transition-all hover:bg-[#dcfce7] hover:shadow-xs active:scale-98 dark:border-[#1e4a2d] dark:bg-[#14281b] dark:hover:bg-[#1b3624]"
                                        >
                                            <div class="flex items-center gap-1.5 text-sm font-bold text-[#15803d] dark:text-[#4ade80]">
                                                <span>Good</span>
                                                <kbd class="rounded bg-black/10 px-1.5 py-0.5 text-[10px] font-mono dark:bg-white/10">2</kbd>
                                            </div>
                                            <div class="mt-0.5 text-[11px] text-[#16a34a] dark:text-[#86efac]">Recall pass</div>
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="grade(5)"
                                            class="flex flex-col items-center justify-center rounded-2xl border border-[#93c5fd] bg-[#f0f9ff] p-3.5 text-center transition-all hover:bg-[#e0f2fe] hover:shadow-xs active:scale-98 dark:border-[#1e3a5f] dark:bg-[#13253a] dark:hover:bg-[#1a3350]"
                                        >
                                            <div class="flex items-center gap-1.5 text-sm font-bold text-[#1d4ed8] dark:text-[#60a5fa]">
                                                <span>Easy</span>
                                                <kbd class="rounded bg-black/10 px-1.5 py-0.5 text-[10px] font-mono dark:bg-white/10">3</kbd>
                                            </div>
                                            <div class="mt-0.5 text-[11px] text-[#2563eb] dark:text-[#93c5fd]">Fast bonus</div>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- All Caught Up Screen -->
                    <div class="flex flex-1 flex-col items-center justify-center rounded-2xl border border-[#dde3d8] bg-white p-8 text-center shadow-xs sm:p-14 dark:border-[#243a2c] dark:bg-[#15231a]">
                        <div class="flex size-20 items-center justify-center rounded-3xl bg-[#edf7f0] text-4xl dark:bg-[#1d3527]">🎉</div>
                        <h2 class="mt-6 font-display text-3xl font-bold text-[#172c21] dark:text-[#ecf4ee]">All caught up for today!</h2>
                        <p class="mt-3 max-w-md text-sm leading-relaxed text-[#667262] dark:text-[#90a697]">
                            You have completed all due flashcards in this deck. Come back tomorrow to reinforce long-term memory, or continue in cram mode.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                            <button
                                type="button"
                                wire:click="setMode('all')"
                                class="rounded-xl border border-[#d2dbce] bg-white px-5 py-3 text-xs font-semibold text-[#374438] transition-all hover:bg-[#f4f7f2] dark:border-[#284131] dark:bg-[#18281e] dark:text-[#c4d6cb] dark:hover:bg-[#203327]"
                            >
                                🔄 Continuous Cram Mode
                            </button>
                            <button
                                type="button"
                                wire:click="resetDueCards({{ $selectedDeckId ?? 'null' }})"
                                class="rounded-xl border border-[#d2dbce] bg-white px-5 py-3 text-xs font-semibold text-[#374438] transition-all hover:bg-[#f4f7f2] dark:border-[#284131] dark:bg-[#18281e] dark:text-[#c4d6cb] dark:hover:bg-[#203327]"
                            >
                                ⚡ Review All Again Now
                            </button>
                            <a
                                href="{{ url('/lab') }}"
                                class="rounded-xl bg-[#2f7a55] px-5 py-3 text-xs font-semibold text-white transition-all hover:bg-[#256345] dark:bg-[#276445]"
                            >
                                Practice in Coaching Lab →
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Quick Add Custom Flashcard Modal -->
    @if ($showAddModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs" x-data @keydown.window.escape="$wire.closeAddModal()">
            <div class="w-full max-w-lg rounded-2xl border border-[#dce3d7] bg-white p-6 shadow-2xl dark:border-[#2a4433] dark:bg-[#16251c]">
                <div class="flex items-center justify-between border-b border-[#edf1ea] pb-3.5 dark:border-[#23382a]">
                    <h3 class="font-display text-lg font-bold text-[#172c21] dark:text-[#ecf4ee]">Add New Flashcard</h3>
                    <button type="button" wire:click="closeAddModal" class="text-sm font-semibold text-[#717b6d] hover:text-[#172c21] dark:text-[#8ba393]">✕</button>
                </div>

                <form wire:submit="saveCustomCard" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-[#404a3d] dark:text-[#b4c9bc]">Deck</label>
                        <select wire:model="newDeckId" class="w-full rounded-xl border border-[#d2dbcd] bg-white px-3.5 py-2 text-xs text-[#1e2d24] dark:border-[#2b4434] dark:bg-[#18291e] dark:text-[#e4efe7]">
                            @foreach ($this->decks as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('newDeckId') <span class="text-[11px] text-[#dc2626]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-[#404a3d] dark:text-[#b4c9bc]">Front (Prompt / Portuguese phrase)</label>
                        <textarea wire:model="newFront" rows="2" placeholder="e.g. Como dizer: Eu costumava trabalhar até tarde." class="w-full rounded-xl border border-[#d2dbcd] bg-white px-3.5 py-2 text-xs text-[#1e2d24] dark:border-[#2b4434] dark:bg-[#18291e] dark:text-[#e4efe7]"></textarea>
                        @error('newFront') <span class="text-[11px] text-[#dc2626]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-[#404a3d] dark:text-[#b4c9bc]">Back (Correct English target)</label>
                        <textarea wire:model="newBack" rows="2" placeholder="e.g. I used to work late." class="w-full rounded-xl border border-[#d2dbcd] bg-white px-3.5 py-2 text-xs text-[#1e2d24] dark:border-[#2b4434] dark:bg-[#18291e] dark:text-[#e4efe7]"></textarea>
                        @error('newBack') <span class="text-[11px] text-[#dc2626]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-[#404a3d] dark:text-[#b4c9bc]">Grammar Tag</label>
                            <input type="text" wire:model="newGrammarTag" placeholder="e.g. Past Habits" class="w-full rounded-xl border border-[#d2dbcd] bg-white px-3.5 py-2 text-xs text-[#1e2d24] dark:border-[#2b4434] dark:bg-[#18291e] dark:text-[#e4efe7]">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-[#404a3d] dark:text-[#b4c9bc]">Rule Explanation</label>
                            <input type="text" wire:model="newExplanation" placeholder="e.g. Used to expresses past routines" class="w-full rounded-xl border border-[#d2dbcd] bg-white px-3.5 py-2 text-xs text-[#1e2d24] dark:border-[#2b4434] dark:bg-[#18291e] dark:text-[#e4efe7]">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-[#edf1ea] pt-4 dark:border-[#23382a]">
                        <button type="button" wire:click="closeAddModal" class="rounded-xl px-4 py-2 text-xs font-medium text-[#6b7668] hover:bg-[#f2f5ef] dark:text-[#90a697] dark:hover:bg-[#1b2b21]">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#2f7a55] px-5 py-2 text-xs font-semibold text-white hover:bg-[#256345] dark:bg-[#286647]">Save Card</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
