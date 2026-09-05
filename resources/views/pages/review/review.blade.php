<div class="verbally-shell" x-data="{
    isSpeaking: false,
    speak(text) {
        if (!text) return;
        this.isSpeaking = true;
        window.verballySpeak(text, () => { this.isSpeaking = true; }, () => { this.isSpeaking = false; });
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
        <header class="verbally-nav" aria-label="Primary navigation">
            <div class="flex items-center gap-3 sm:gap-6">
                <a href="{{ url('/') }}" class="verbally-brand" aria-label="Verbally home">
                    <span class="verbally-logo-wrap">
                        <img src="{{ asset('images/verbally-logo-lockup.png') }}" class="dark:hidden" alt="Verbally, English corrections and coaching" width="207" height="178">
                        <img src="{{ asset('images/verbally-logo-lockup-dark.png') }}" class="hidden dark:block" alt="Verbally, English corrections and coaching" width="207" height="178">
                    </span>
                </a>
                <nav class="flex items-center gap-1" aria-label="Sections">
                    <a href="{{ url('/') }}" class="rounded-lg px-2.5 py-1 text-xs font-medium text-[#5c6357] transition-colors hover:bg-[#edf6ef] dark:text-[#a3b8aa] dark:hover:bg-[#1b2b21]">Practice</a>
                    <a href="{{ url('/lab') }}" class="rounded-lg px-2.5 py-1 text-xs font-medium text-[#5c6357] transition-colors hover:bg-[#edf6ef] dark:text-[#a3b8aa] dark:hover:bg-[#1b2b21]">Coaching Lab</a>
                    <a href="{{ url('/review') }}" class="rounded-lg bg-[#2f7a55] px-2.5 py-1 text-xs font-semibold text-white transition-colors dark:bg-[#235c40]" aria-current="page">Flashcards & Decks</a>
                </nav>
            </div>
            <div class="verbally-nav-meta">
                <span class="verbally-session">🔥 {{ $this->dailyStreak }}d streak · {{ $this->totalDueCount }} due</span>
                <button
                    type="button"
                    x-on:click="window.toggleVerballyTheme ? window.toggleVerballyTheme() : document.documentElement.classList.toggle('dark')"
                    class="verbally-theme-toggle"
                    aria-label="Toggle dark mode"
                    title="Toggle dark mode"
                >
                    <svg class="block size-4 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    <svg class="hidden size-4 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>
                <button type="button" wire:click="openAddModal" class="verbally-clear" aria-label="Add new flashcard">
                    + New Card
                </button>
            </div>
        </header>
    @endisland

    <main class="verbally-main">
        <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 py-6 sm:px-6 sm:py-8">
            <!-- Top Spaced Repetition Stats Bar -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                <div class="flex items-center gap-3 rounded-xl border border-[#e2e6de] bg-white p-3.5 shadow-xs dark:border-[#243a2c] dark:bg-[#16241b]">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-[#fef3c7] text-lg text-[#d97706] dark:bg-[#362a15] dark:text-[#f59e0b]">🔥</div>
                    <div>
                        <div class="text-xs font-medium text-[#737c6d] dark:text-[#8ba393]">Daily Streak</div>
                        <div class="text-lg font-bold text-[#1a2e22] dark:text-[#e4efe7]">{{ $this->dailyStreak }} {{ $this->dailyStreak === 1 ? 'day' : 'days' }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-[#e2e6de] bg-white p-3.5 shadow-xs dark:border-[#243a2c] dark:bg-[#16241b]">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-[#edf6ef] text-lg text-[#2f7a55] dark:bg-[#1c3325] dark:text-[#52b788]">🎯</div>
                    <div>
                        <div class="text-xs font-medium text-[#737c6d] dark:text-[#8ba393]">Due for Review</div>
                        <div class="text-lg font-bold text-[#1a2e22] dark:text-[#e4efe7]">{{ $this->totalDueCount }} {{ $this->totalDueCount === 1 ? 'card' : 'cards' }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-[#e2e6de] bg-white p-3.5 shadow-xs dark:border-[#243a2c] dark:bg-[#16241b]">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-[#e0f2fe] text-lg text-[#0284c7] dark:bg-[#152e3d] dark:text-[#38bdf8]">📈</div>
                    <div>
                        <div class="text-xs font-medium text-[#737c6d] dark:text-[#8ba393]">Retention Rate</div>
                        <div class="text-lg font-bold text-[#1a2e22] dark:text-[#e4efe7]">{{ $this->retentionRate }}%</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-[#e2e6de] bg-white p-3.5 shadow-xs dark:border-[#243a2c] dark:bg-[#16241b]">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-[#f3e8ff] text-lg text-[#9333ea] dark:bg-[#2e1d3d] dark:text-[#c084fc]">⚡</div>
                    <div>
                        <div class="text-xs font-medium text-[#737c6d] dark:text-[#8ba393]">Reviewed Today</div>
                        <div class="text-lg font-bold text-[#1a2e22] dark:text-[#e4efe7]">{{ $this->reviewsTodayCount }}</div>
                    </div>
                </div>
            </div>

            <!-- Deck Selector Filter -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e5e9e2] pb-3 dark:border-[#263a2e]">
                <div class="flex flex-wrap items-center gap-1.5">
                    <button
                        type="button"
                        wire:click="selectDeck(null)"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all {{ $selectedDeckId === null ? 'bg-[#2f7a55] text-white shadow-xs dark:bg-[#286647]' : 'border border-[#dee3da] bg-white text-[#525b4e] hover:bg-[#f4f7f2] dark:border-[#283f30] dark:bg-[#16231c] dark:text-[#9bb3a3] dark:hover:bg-[#1c2c23]' }}"
                    >
                        All Decks ({{ $this->decks->sum('flashcards_count') }})
                    </button>
                    @foreach ($this->decks as $deck)
                        <button
                            type="button"
                            wire:key="deck-btn-{{ $deck->id }}"
                            wire:click="selectDeck({{ $deck->id }})"
                            class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all {{ $selectedDeckId === $deck->id ? 'bg-[#2f7a55] text-white shadow-xs dark:bg-[#286647]' : 'border border-[#dee3da] bg-white text-[#525b4e] hover:bg-[#f4f7f2] dark:border-[#283f30] dark:bg-[#16231c] dark:text-[#9bb3a3] dark:hover:bg-[#1c2c23]' }}"
                        >
                            <span>{{ $deck->name }}</span>
                            @if ($deck->due_count > 0)
                                <span class="rounded-full bg-[#e35050] px-1.5 py-0.2 text-[10px] font-bold text-white">{{ $deck->due_count }}</span>
                            @else
                                <span class="text-[11px] opacity-60">({{ $deck->flashcards_count }})</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="setMode('due')"
                        class="rounded-md px-2.5 py-1 text-xs font-medium {{ $mode === 'due' ? 'bg-[#1a2e22] text-white dark:bg-[#2a4534]' : 'text-[#636c5f] hover:text-[#1a2e22] dark:text-[#8ba393]' }}"
                    >
                        Due Only
                    </button>
                    <button
                        type="button"
                        wire:click="setMode('all')"
                        class="rounded-md px-2.5 py-1 text-xs font-medium {{ $mode === 'all' ? 'bg-[#1a2e22] text-white dark:bg-[#2a4534]' : 'text-[#636c5f] hover:text-[#1a2e22] dark:text-[#8ba393]' }}"
                    >
                        Continuous Cram
                    </button>
                </div>
            </div>

            <!-- Feedback Toast Notification -->
            @if ($feedbackMessage)
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition class="flex items-center justify-between rounded-lg border border-[#cde5d7] bg-[#edf8f1] px-4 py-2.5 text-xs font-medium text-[#1e5337] dark:border-[#234d36] dark:bg-[#132c1e] dark:text-[#74cca0]">
                    <span>{{ $feedbackMessage }}</span>
                    <button type="button" @click="show = false" class="text-[#3b7754] hover:text-[#1e5337] dark:text-[#74cca0]">✕</button>
                </div>
            @endif

            <!-- Main Study Card Area -->
            @if ($this->currentCard)
                <div class="mx-auto w-full max-w-2xl">
                    <div class="relative overflow-hidden rounded-2xl border border-[#dde3d8] bg-white shadow-sm transition-all duration-200 dark:border-[#243a2c] dark:bg-[#16241b]">
                        <!-- Card Top Meta -->
                        <div class="flex items-center justify-between border-b border-[#edf0ea] px-6 py-3.5 dark:border-[#223528]">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-[#667262] dark:text-[#8ba393]">
                                    {{ $this->currentCard->deck->name }}
                                </span>
                                @if ($this->currentCard->grammar_tag)
                                    <span class="rounded-md bg-[#eef5ee] px-2 py-0.5 text-[11px] font-semibold text-[#2f7a55] dark:bg-[#1c3325] dark:text-[#6ec59c]">
                                        {{ $this->currentCard->grammar_tag }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-[#889284] dark:text-[#799182]">
                                Interval: {{ $this->currentCard->interval_days }}d · Reps: {{ $this->currentCard->repetitions }} · Lapses: {{ $this->currentCard->lapses }}
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 sm:p-8">
                            <!-- Front Prompt / Stimulus -->
                            <div class="mb-4">
                                <div class="mb-2 text-[11px] font-bold uppercase tracking-wider text-[#8e988a] dark:text-[#7a9283]">
                                    Prompt / Em Português:
                                </div>
                                <div class="font-display text-xl sm:text-2xl font-medium leading-snug text-[#172c21] dark:text-[#e4efe7]">
                                    {{ $this->currentCard->front }}
                                </div>
                            </div>

                            <!-- Back Answer (Hidden until flipped) -->
                            @if (! $isFlipped)
                                <div class="mt-8 flex flex-col items-center justify-center border-t border-[#f0f3ed] pt-8 dark:border-[#203126]">
                                    <button
                                        type="button"
                                        wire:click="flip"
                                        class="group inline-flex items-center gap-2 rounded-xl bg-[#2f7a55] px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-[#256345] hover:shadow-md active:scale-98 dark:bg-[#276445] dark:hover:bg-[#307c56]"
                                    >
                                        <span>Show English Answer</span>
                                        <kbd class="rounded bg-[#20533a] px-1.5 py-0.5 text-[10px] text-white/80 font-mono">Space</kbd>
                                    </button>
                                    <p class="mt-2 text-xs text-[#8c9688] dark:text-[#7a9283]">or press Space to flip</p>
                                </div>
                            @else
                                <div class="mt-6 border-t border-[#edf0ea] pt-6 dark:border-[#203126]">
                                    <div class="mb-2 flex items-center justify-between">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-[#2f7a55] dark:text-[#52b788]">
                                            English Target:
                                        </div>
                                        <button
                                            type="button"
                                            @click="speak('{{ addslashes($this->currentCard->audio_text ?? $this->currentCard->back) }}')"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-[#d8e0d4] bg-[#f8faf7] px-2.5 py-1 text-xs font-medium text-[#2d5741] transition-colors hover:bg-[#edf6ef] dark:border-[#284131] dark:bg-[#1a2d21] dark:text-[#9bc2ac] dark:hover:bg-[#22392b]"
                                        >
                                            <span :class="isSpeaking ? 'animate-bounce' : ''">🔊</span>
                                            <span>Listen (Native TTS)</span>
                                        </button>
                                    </div>

                                    <div class="font-display text-2xl sm:text-3xl font-semibold leading-tight text-[#163527] dark:text-[#5fe1a4]">
                                        {{ $this->currentCard->back }}
                                    </div>

                                    @if ($this->currentCard->explanation)
                                        <div class="mt-4 rounded-xl border border-[#e2e9df] bg-[#f6f9f5] p-3.5 text-xs text-[#4c5c50] dark:border-[#263c2e] dark:bg-[#18281e] dark:text-[#a0b8a8]">
                                            <div class="mb-1 font-semibold text-[#27533c] dark:text-[#71c397]">💡 Why this works:</div>
                                            <div>{{ $this->currentCard->explanation }}</div>
                                        </div>
                                    @endif

                                    <!-- SM-2 Evaluation Buttons -->
                                    <div class="mt-8 border-t border-[#f0f3ed] pt-6 dark:border-[#203126]">
                                        <div class="mb-3 text-center text-xs font-semibold text-[#717c6c] dark:text-[#889f90]">
                                            How well did you recall this?
                                        </div>
                                        <div class="grid grid-cols-3 gap-2.5 sm:gap-3">
                                            <button
                                                type="button"
                                                wire:click="grade(1)"
                                                class="flex flex-col items-center justify-center rounded-xl border border-[#fca5a5] bg-[#fff5f5] p-3 text-center transition-all hover:bg-[#fee2e2] hover:shadow-xs active:scale-98 dark:border-[#522525] dark:bg-[#2d1616] dark:hover:bg-[#3d1c1c]"
                                            >
                                                <div class="flex items-center gap-1 text-xs font-bold text-[#b91c1c] dark:text-[#f87171]">
                                                    <span>Again</span>
                                                    <kbd class="rounded bg-black/10 px-1 py-0.2 text-[9px] font-mono dark:bg-white/10">1</kbd>
                                                </div>
                                                <div class="mt-0.5 text-[10px] text-[#dc2626] dark:text-[#fca5a5]">Reset (1d)</div>
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="grade(3)"
                                                class="flex flex-col items-center justify-center rounded-xl border border-[#86efac] bg-[#f0fdf4] p-3 text-center transition-all hover:bg-[#dcfce7] hover:shadow-xs active:scale-98 dark:border-[#1e4a2d] dark:bg-[#14281b] dark:hover:bg-[#1b3624]"
                                            >
                                                <div class="flex items-center gap-1 text-xs font-bold text-[#15803d] dark:text-[#4ade80]">
                                                    <span>Good</span>
                                                    <kbd class="rounded bg-black/10 px-1 py-0.2 text-[9px] font-mono dark:bg-white/10">2</kbd>
                                                </div>
                                                <div class="mt-0.5 text-[10px] text-[#16a34a] dark:text-[#86efac]">Recall pass</div>
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="grade(5)"
                                                class="flex flex-col items-center justify-center rounded-xl border border-[#93c5fd] bg-[#f0f9ff] p-3 text-center transition-all hover:bg-[#e0f2fe] hover:shadow-xs active:scale-98 dark:border-[#1e3a5f] dark:bg-[#13253a] dark:hover:bg-[#1a3350]"
                                            >
                                                <div class="flex items-center gap-1 text-xs font-bold text-[#1d4ed8] dark:text-[#60a5fa]">
                                                    <span>Easy</span>
                                                    <kbd class="rounded bg-black/10 px-1 py-0.2 text-[9px] font-mono dark:bg-white/10">3</kbd>
                                                </div>
                                                <div class="mt-0.5 text-[10px] text-[#2563eb] dark:text-[#93c5fd]">Fast bonus</div>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty State / Caught Up -->
                <div class="mx-auto flex w-full max-w-xl flex-col items-center justify-center rounded-2xl border border-[#dde3d8] bg-white p-8 text-center shadow-xs sm:p-12 dark:border-[#243a2c] dark:bg-[#16241b]">
                    <div class="flex size-16 items-center justify-center rounded-2xl bg-[#edf7f0] text-3xl dark:bg-[#1d3527]">🎉</div>
                    <h2 class="mt-4 font-display text-2xl font-bold text-[#172c21] dark:text-[#ecf4ee]">All caught up for today!</h2>
                    <p class="mt-2 text-sm text-[#667262] dark:text-[#90a697]">
                        You've reviewed all due flashcards in this deck. Spaced repetition ensures these patterns lock into long-term memory.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        <button
                            type="button"
                            wire:click="setMode('all')"
                            class="rounded-xl border border-[#d2dbce] bg-white px-4 py-2.5 text-xs font-semibold text-[#374438] transition-all hover:bg-[#f4f7f2] dark:border-[#284131] dark:bg-[#18281e] dark:text-[#c4d6cb] dark:hover:bg-[#203327]"
                        >
                            🔄 Continuous Cram Mode
                        </button>
                        <button
                            type="button"
                            wire:click="resetDueCards({{ $selectedDeckId ?? 'null' }})"
                            class="rounded-xl border border-[#d2dbce] bg-white px-4 py-2.5 text-xs font-semibold text-[#374438] transition-all hover:bg-[#f4f7f2] dark:border-[#284131] dark:bg-[#18281e] dark:text-[#c4d6cb] dark:hover:bg-[#203327]"
                        >
                            ⚡ Review All Again Now
                        </button>
                        <a
                            href="{{ url('/lab') }}"
                            class="rounded-xl bg-[#2f7a55] px-4 py-2.5 text-xs font-semibold text-white transition-all hover:bg-[#256345] dark:bg-[#276445]"
                        >
                            Go to Coaching Lab →
                        </a>
                    </div>
                </div>
            @endif

            <!-- Grammar Weakness Radar Section -->
            @if ($this->grammarWeaknesses->isNotEmpty())
                <div class="mt-4 rounded-2xl border border-[#e2e7de] bg-white p-5 shadow-xs dark:border-[#243a2c] dark:bg-[#16241b]">
                    <div class="flex items-center justify-between border-b border-[#eef2eb] pb-3 dark:border-[#223528]">
                        <div>
                            <h3 class="font-display text-base font-bold text-[#172c21] dark:text-[#ecf4ee]">Grammar Weakness Radar</h3>
                            <p class="text-xs text-[#717b6d] dark:text-[#8ba393]">Prioritize categories where your memory has lapsed most often.</p>
                        </div>
                        <a href="{{ url('/lab') }}" class="text-xs font-semibold text-[#2f7a55] hover:underline dark:text-[#52b788]">
                            Practice in Lab →
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                        @foreach ($this->grammarWeaknesses as $weakness)
                            <div class="flex items-center justify-between rounded-xl border border-[#edf1ea] bg-[#f9faf8] p-3 dark:border-[#263c2e] dark:bg-[#18291e]">
                                <div>
                                    <div class="text-xs font-bold text-[#1f3729] dark:text-[#d7e5dc]">{{ $weakness->grammar_tag }}</div>
                                    <div class="text-[11px] text-[#717b6d] dark:text-[#8ba393]">{{ $weakness->card_count }} cards in deck</div>
                                </div>
                                <div class="text-right">
                                    <span class="rounded-md bg-[#fee2e2] px-2 py-0.5 text-[11px] font-bold text-[#991b1b] dark:bg-[#3d1c1c] dark:text-[#f87171]">
                                        {{ $weakness->total_lapses }} lapses
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </main>

    <!-- Quick Add Custom Flashcard Modal -->
    @if ($showAddModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs" x-data @keydown.window.escape="$wire.closeAddModal()">
            <div class="w-full max-w-lg rounded-2xl border border-[#dce3d7] bg-white p-6 shadow-xl dark:border-[#2a4433] dark:bg-[#16251c]">
                <div class="flex items-center justify-between border-b border-[#edf1ea] pb-3 dark:border-[#23382a]">
                    <h3 class="font-display text-lg font-bold text-[#172c21] dark:text-[#ecf4ee]">Add New Flashcard</h3>
                    <button type="button" wire:click="closeAddModal" class="text-sm font-semibold text-[#717b6d] hover:text-[#172c21] dark:text-[#8ba393]">✕</button>
                </div>

                <form wire:submit="saveCustomCard" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-[#404a3d] dark:text-[#b4c9bc]">Deck</label>
                        <select wire:model="newDeckId" class="w-full rounded-lg border border-[#d2dbcd] bg-white px-3 py-2 text-xs text-[#1e2d24] dark:border-[#2b4434] dark:bg-[#18291e] dark:text-[#e4efe7]">
                            @foreach ($this->decks as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('newDeckId') <span class="text-[11px] text-[#dc2626]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-[#404a3d] dark:text-[#b4c9bc]">Front (Prompt / Portuguese sentence)</label>
                        <textarea wire:model="newFront" rows="2" placeholder="e.g. Como dizer: Eu costumava trabalhar até tarde." class="w-full rounded-lg border border-[#d2dbcd] bg-white px-3 py-2 text-xs text-[#1e2d24] dark:border-[#2b4434] dark:bg-[#18291e] dark:text-[#e4efe7]"></textarea>
                        @error('newFront') <span class="text-[11px] text-[#dc2626]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-[#404a3d] dark:text-[#b4c9bc]">Back (Correct English target)</label>
                        <textarea wire:model="newBack" rows="2" placeholder="e.g. I used to work late." class="w-full rounded-lg border border-[#d2dbcd] bg-white px-3 py-2 text-xs text-[#1e2d24] dark:border-[#2b4434] dark:bg-[#18291e] dark:text-[#e4efe7]"></textarea>
                        @error('newBack') <span class="text-[11px] text-[#dc2626]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-[#404a3d] dark:text-[#b4c9bc]">Grammar Tag</label>
                            <input type="text" wire:model="newGrammarTag" placeholder="e.g. Past Habits" class="w-full rounded-lg border border-[#d2dbcd] bg-white px-3 py-2 text-xs text-[#1e2d24] dark:border-[#2b4434] dark:bg-[#18291e] dark:text-[#e4efe7]">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-[#404a3d] dark:text-[#b4c9bc]">Rule Explanation</label>
                            <input type="text" wire:model="newExplanation" placeholder="e.g. Used to expresses past habits" class="w-full rounded-lg border border-[#d2dbcd] bg-white px-3 py-2 text-xs text-[#1e2d24] dark:border-[#2b4434] dark:bg-[#18291e] dark:text-[#e4efe7]">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-[#edf1ea] pt-3 dark:border-[#23382a]">
                        <button type="button" wire:click="closeAddModal" class="rounded-lg px-3 py-1.5 text-xs font-medium text-[#6b7668] hover:bg-[#f2f5ef] dark:text-[#90a697] dark:hover:bg-[#1b2b21]">Cancel</button>
                        <button type="submit" class="rounded-lg bg-[#2f7a55] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#256345] dark:bg-[#286647]">Save Card</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
