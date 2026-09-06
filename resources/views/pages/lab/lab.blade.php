<div class="verbally-shell">
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
                <a href="{{ url('/lab') }}" class="rounded-lg bg-[#2f7a55] px-2.5 py-1 text-xs font-semibold text-white transition-colors dark:bg-[#235c40]" aria-current="page">Coaching Lab</a>
                <a href="{{ url('/review') }}" class="rounded-lg px-2.5 py-1 text-xs font-medium text-[#5c6357] transition-colors hover:bg-[#edf6ef] dark:text-[#a3b8aa] dark:hover:bg-[#1b2b21]">Flashcards</a>
                <a href="{{ url('/conversations') }}" class="rounded-lg px-2.5 py-1 text-xs font-medium text-[#5c6357] transition-colors hover:bg-[#edf6ef] dark:text-[#a3b8aa] dark:hover:bg-[#1b2b21]">Conversations</a>
            </nav>
        </div>
        <div class="verbally-nav-meta">
            <span class="verbally-session">Lab · {{ count($results) }} runs</span>
            <button
                type="button"
                x-data
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
            <button type="button" x-on:click="$wire.clearLab()" @disabled($processing) class="verbally-clear" aria-label="Clear lab history">
                Clear lab
            </button>
        </div>
    </header>

    <main class="verbally-main">
        <section class="verbally-editor" aria-labelledby="lab-studio-title">
            <div class="verbally-editor-intro">
                <p class="verbally-eyebrow">Advanced Coaching Studio</p>
                <h1 id="lab-studio-title">Targeted coaching,<br><em>your way.</em></h1>
                <p class="verbally-lede">Choose a coaching goal or write a custom command to rewrite, enrich, quiz, or explore your English.</p>
            </div>

            <form wire:submit="runCommand" class="verbally-compose">
                <label for="lab-text" class="verbally-compose-label">Your English text</label>
                <textarea
                    id="lab-text"
                    aria-label="Your English text"
                    aria-describedby="lab-count"
                    placeholder="Paste or write an English sentence, email draft, or paragraph..."
                    wire:model="text"
                    wire:loading.attr="disabled"
                    @disabled($processing)
                    class="min-h-[140px]"
                ></textarea>
                <div class="verbally-compose-footer">
                    <span id="lab-count" class="verbally-count">{{ mb_strlen($text) }} / 2000</span>
                    <span class="verbally-hint">Select a command below to run</span>
                </div>
            </form>

            @error('text')
                <p class="verbally-form-error" role="alert">{{ $message }}</p>
            @enderror

            <div class="mt-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-[#5c6357] dark:text-[#a3b8aa]">Coaching Commands</p>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        wire:click="selectCommand('formal')"
                        @disabled($processing)
                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors {{ $command === 'formal' ? 'border-[#2f7a55] bg-[#edf6ef] text-[#17352a] font-semibold dark:border-[#4ade80] dark:bg-[#1b3829] dark:text-[#ecf4ee]' : 'border-[#e2e5dd] bg-[#fbfcfa] text-[#5c6357] hover:bg-white dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0]' }}"
                    >
                        👔 Make it Formal
                    </button>
                    <button
                        type="button"
                        wire:click="selectCommand('casual')"
                        @disabled($processing)
                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors {{ $command === 'casual' ? 'border-[#2f7a55] bg-[#edf6ef] text-[#17352a] font-semibold dark:border-[#4ade80] dark:bg-[#1b3829] dark:text-[#ecf4ee]' : 'border-[#e2e5dd] bg-[#fbfcfa] text-[#5c6357] hover:bg-white dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0]' }}"
                    >
                        ☕ Make it Casual
                    </button>
                    <button
                        type="button"
                        wire:click="selectCommand('vocabulary')"
                        @disabled($processing)
                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors {{ $command === 'vocabulary' ? 'border-[#2f7a55] bg-[#edf6ef] text-[#17352a] font-semibold dark:border-[#4ade80] dark:bg-[#1b3829] dark:text-[#ecf4ee]' : 'border-[#e2e5dd] bg-[#fbfcfa] text-[#5c6357] hover:bg-white dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0]' }}"
                    >
                        💎 Enrich Vocabulary
                    </button>
                    <button
                        type="button"
                        wire:click="selectCommand('quiz')"
                        @disabled($processing)
                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors {{ $command === 'quiz' ? 'border-[#2f7a55] bg-[#edf6ef] text-[#17352a] font-semibold dark:border-[#4ade80] dark:bg-[#1b3829] dark:text-[#ecf4ee]' : 'border-[#e2e5dd] bg-[#fbfcfa] text-[#5c6357] hover:bg-white dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0]' }}"
                    >
                        ❓ Practice Quiz
                    </button>
                    <button
                        type="button"
                        wire:click="selectCommand('custom')"
                        @disabled($processing)
                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors {{ $command === 'custom' ? 'border-[#2f7a55] bg-[#edf6ef] text-[#17352a] font-semibold dark:border-[#4ade80] dark:bg-[#1b3829] dark:text-[#ecf4ee]' : 'border-[#e2e5dd] bg-[#fbfcfa] text-[#5c6357] hover:bg-white dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0]' }}"
                    >
                        ⚡ Custom Command
                    </button>
                </div>
            </div>

            @if ($command === 'custom')
                <div class="mt-3">
                    <label for="custom-prompt" class="mb-1 block text-xs font-medium text-[#5c6357] dark:text-[#a3b8aa]">Your custom instruction for the coach:</label>
                    <input
                        id="custom-prompt"
                        type="text"
                        wire:model="customPrompt"
                        placeholder="e.g. Shorten to 2 punchy sentences, or explain why this sounds rude..."
                        @disabled($processing)
                        class="w-full rounded-xl border border-[#dce1d8] bg-white px-3.5 py-2.5 text-sm text-[#17352a] placeholder-[#8a9184] transition-colors focus:border-[#2f7a55] focus:outline-none dark:border-[#263a2e] dark:bg-[#15221b] dark:text-[#ecf4ee] dark:placeholder-[#667e70]"
                    />
                    @error('customPrompt')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if ($error)
                <div class="mt-3 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300" role="alert">
                    {{ $error }}
                </div>
            @endif

            <button
                type="button"
                x-on:click="$wire.runCommand()"
                wire:loading.attr="disabled"
                @disabled($processing)
                class="verbally-submit mt-4"
            >
                <span wire:loading.remove>Execute Coaching Command</span>
                <span wire:loading>Coaching in progress…</span>
            </button>

            <div class="verbally-editor-note">
                <span>Tailored coaching feedback powered by Gemini.</span>
            </div>
        </section>

        <section class="verbally-conversation" aria-labelledby="lab-output-title">
            <div class="verbally-conversation-head">
                <div>
                    <p class="verbally-eyebrow">Lab Workspace</p>
                    <h2 id="lab-output-title">Coach Results</h2>
                </div>
                <span class="verbally-date">Today</span>
            </div>

            <div data-conversation-scroll class="verbally-conversation-scroll" aria-live="polite">
                @forelse ($results as $result)
                    <div class="flex flex-col gap-2.5 rounded-2xl border border-[#e8eae4] bg-white p-5 shadow-[0_1px_2px_rgba(30,40,25,.03)] dark:border-[#263a2e] dark:bg-[#18261f] dark:shadow-[0_2px_12px_rgba(0,0,0,.3)]" wire:key="lab-res-{{ $result['id'] }}">
                        <div class="flex items-center justify-between border-b border-[#eef0ea] pb-2.5 dark:border-[#24372c]">
                            <span class="rounded-md bg-[#edf6ef] px-2 py-0.5 text-xs font-semibold text-[#2f7a55] dark:bg-[#1b3829] dark:text-[#4ade80]">
                                {{ $result['label'] }}
                            </span>
                            <button
                                type="button"
                                x-data="{
                                    speaking: false,
                                    speak() {
                                        if (this.speaking) {
                                            window.verballyStopSpeaking ? window.verballyStopSpeaking() : (window.speechSynthesis && window.speechSynthesis.cancel());
                                            this.speaking = false;
                                            return;
                                        }
                                        if (window.verballySpeak) {
                                            window.verballySpeak(@js($result['response']), () => { this.speaking = true; }, () => { this.speaking = false; });
                                        }
                                    }
                                }"
                                x-on:click="speak()"
                                class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[11px] font-medium text-[#2f7a55] transition-colors hover:bg-[#edf6ef] hover:text-[#17352a] dark:text-[#4ade80] dark:hover:bg-[#192b20] dark:hover:text-[#86efac]"
                                :aria-label="speaking ? 'Stop listening' : 'Listen to pronunciation'"
                                :title="speaking ? 'Stop' : 'Listen to coach'"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.757 3.63 8.25 4.51 8.25H6.75Z" />
                                </svg>
                                <span x-text="speaking ? 'Playing…' : 'Listen'">Listen</span>
                            </button>
                        </div>

                        <div class="text-xs text-[#8a9184] dark:text-[#7f998a]">
                            <span class="font-semibold">Original:</span> "{{ mb_strimwidth($result['text'], 0, 90, '…') }}"
                        </div>

                        <div class="whitespace-pre-wrap text-[14.5px] leading-[1.65] text-[#17352a] dark:text-[#ecf4ee]">
                            {{ $result['response'] }}
                        </div>

                        @if ($result['translation'])
                            <div class="mt-2 rounded-xl border border-[#d2e4d9] bg-[#f4faf6] p-3 text-[13.5px] leading-[1.6] text-[#244b39] dark:border-[#234533] dark:bg-[#13231a] dark:text-[#a8d5be]">
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-[#2f7a55] dark:text-[#4ade80]">🇧🇷 Tradução (PT-BR)</span>
                                <div class="whitespace-pre-wrap">{{ $result['translation'] }}</div>
                            </div>
                        @endif

                        <div class="mt-2 flex flex-wrap items-center gap-2 border-t border-[#eef0ea] pt-3 dark:border-[#24372c]">
                            @if (! $result['translation'])
                                <button
                                    type="button"
                                    wire:click="translateResult({{ $result['id'] }})"
                                    @disabled($result['translating'])
                                    class="rounded-lg border border-[#e2e5dd] bg-[#fbfcfa] px-3 py-1.5 text-xs font-medium text-[#5c6357] transition-colors hover:bg-white hover:text-[#17352a] disabled:opacity-50 dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0] dark:hover:bg-[#1c2c23] dark:hover:text-[#ecf4ee]"
                                >
                                    {{ $result['translating'] ? 'Traduzindo…' : 'Translate (PT)' }}
                                </button>
                            @endif

                            <button
                                type="button"
                                wire:click="saveResultToDeck({{ $result['id'] }})"
                                @disabled($result['saved'] ?? false)
                                class="rounded-lg border border-[#e2e5dd] bg-[#fbfcfa] px-3 py-1.5 text-xs font-medium text-[#5c6357] transition-colors hover:bg-white hover:text-[#17352a] disabled:opacity-50 dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0] dark:hover:bg-[#1c2c23] dark:hover:text-[#ecf4ee]"
                            >
                                {{ ($result['saved'] ?? false) ? '✓ Saved to Deck' : '📌 Save to Deck' }}
                            </button>

                            <button
                                type="button"
                                x-data="{ copied: false }"
                                x-on:click="navigator.clipboard.writeText(@js($result['response'])); copied = true; setTimeout(() => copied = false, 2000)"
                                class="rounded-lg border border-[#e2e5dd] bg-[#fbfcfa] px-3 py-1.5 text-xs font-medium text-[#5c6357] transition-colors hover:bg-white hover:text-[#17352a] dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0] dark:hover:bg-[#1c2c23] dark:hover:text-[#ecf4ee]"
                            >
                                <span x-text="copied ? 'Copied!' : 'Copy response'">Copy response</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="verbally-empty" role="status">
                        <p>No coaching runs yet.</p>
                        <span>Select a command and write or paste text on the left.</span>
                    </div>
                @endforelse
            </div>
        </section>
    </main>
</div>
