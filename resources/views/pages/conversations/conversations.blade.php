<div class="verbally-shell min-h-screen bg-[#fcfdfa] text-[#17352a] dark:bg-[#0e1712] dark:text-[#ecf4ee]" x-data="{
    isSpeaking: false,
    speak(text) {
        if (!text) return;
        this.isSpeaking = true;
        window.verballySpeak(text, () => { this.isSpeaking = true; }, () => { this.isSpeaking = false; });
    },
    pastePrompt(text) {
        $wire.set('userMessage', text);
        this.$nextTick(() => {
            const el = document.getElementById('dialogue-input');
            if (el) el.focus();
        });
    }
}">
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
                    <a href="{{ url('/review') }}" class="rounded-lg px-2.5 py-1 text-xs font-medium text-[#5c6357] transition-colors hover:bg-[#edf6ef] dark:text-[#a3b8aa] dark:hover:bg-[#1b2b21]">Flashcards</a>
                    <a href="{{ url('/conversations') }}" class="rounded-lg bg-[#2f7a55] px-2.5 py-1 text-xs font-semibold text-white transition-colors dark:bg-[#235c40]" aria-current="page">Conversations</a>
                </nav>
            </div>
            <div class="verbally-nav-meta">
                <span class="verbally-session">🎭 Roleplay · {{ $this->currentScenario()['partner'] }}</span>
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
                <button type="button" wire:click="clearConversation" @disabled($processing) class="verbally-clear" aria-label="Restart dialogue">
                    ↺ Restart
                </button>
            </div>
        </header>
    @endisland

    <!-- Full-Width Responsive Container -->
    <main class="mx-auto w-full max-w-[1440px] px-4 py-6 sm:px-6 sm:py-8 lg:px-10">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8">
            <!-- Left Column: Scenarios & Conversation Coach Guides (4 Cols on Desktop) -->
            <div class="flex flex-col gap-6 lg:col-span-4">
                <!-- Scenarios Selector -->
                <div class="rounded-2xl border border-[#e2e6de] bg-white p-5 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                    <div class="mb-3 border-b border-[#edf1ea] pb-3 dark:border-[#223528]">
                        <h2 class="font-display text-sm font-bold uppercase tracking-wider text-[#172c21] dark:text-[#ecf4ee]">Roleplay Scenarios</h2>
                        <p class="text-xs text-[#717b6d] dark:text-[#8ba393]">Choose a real-life situation to practice full conversations.</p>
                    </div>

                    <div class="space-y-2">
                        @foreach (self::scenarios() as $key => $scenario)
                            <button
                                type="button"
                                wire:key="scenario-{{ $key }}"
                                wire:click="switchScenario('{{ $key }}')"
                                class="flex w-full items-start gap-3 rounded-xl border p-3 text-left transition-all {{ $scenarioKey === $key ? 'border-[#2f7a55] bg-[#f4faf6] shadow-xs dark:border-[#388860] dark:bg-[#192f22]' : 'border-[#e8ece5] bg-white hover:bg-[#f7f9f6] dark:border-[#243a2c] dark:bg-[#15231a] dark:hover:bg-[#1b2d21]' }}"
                            >
                                <span class="text-2xl">{{ $scenario['avatar'] }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-bold text-[#1a2e22] dark:text-[#e4efe7]">{{ $scenario['title'] }}</div>
                                        @if ($scenarioKey === $key)
                                            <span class="rounded-full bg-[#2f7a55] px-2 py-0.2 text-[10px] font-bold text-white dark:bg-[#388860]">Active</span>
                                        @endif
                                    </div>
                                    <div class="mt-0.5 truncate text-[11px] text-[#717b6d] dark:text-[#8ba393]">{{ $scenario['setting'] }}</div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Scenario Coach Hints & Starters -->
                <div class="rounded-2xl border border-[#e2e6de] bg-white p-5 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                    <div class="mb-3 border-b border-[#edf1ea] pb-3 dark:border-[#223528]">
                        <h3 class="font-display text-sm font-bold uppercase tracking-wider text-[#172c21] dark:text-[#ecf4ee]">
                            💡 {{ $scenarioKey === 'first_date' ? 'Date Tips & Starters' : 'Coach Tips' }}
                        </h3>
                        <p class="text-xs text-[#717b6d] dark:text-[#8ba393]">
                            {{ $scenarioKey === 'first_date' ? 'Keep it charming, curious, and smooth:' : 'Click to copy phrase idea into your input:' }}
                        </p>
                    </div>

                    @if ($scenarioKey === 'first_date')
                        <div class="space-y-2.5 text-xs text-[#4b584a] dark:text-[#a0b5a6]">
                            <div class="rounded-xl border border-[#edf3ec] bg-[#f9fbf8] p-3 dark:border-[#243a2c] dark:bg-[#121f16]">
                                <div class="font-bold text-[#204a33] dark:text-[#5fe1a4]">Break the ice with warmth:</div>
                                <button type="button" @click="pastePrompt('I actually come here all the time! The vibe is great, and you look lovely tonight by the way.')" class="mt-1 block text-left text-[11px] text-[#2f7a55] hover:underline dark:text-[#74cca0]">
                                    "I actually come here all the time! The vibe is great, and you look lovely tonight by the way." ↗
                                </button>
                            </div>

                            <div class="rounded-xl border border-[#edf3ec] bg-[#f9fbf8] p-3 dark:border-[#243a2c] dark:bg-[#121f16]">
                                <div class="font-bold text-[#204a33] dark:text-[#5fe1a4]">Ask about her passions:</div>
                                <button type="button" @click="pastePrompt('So what do you love doing when you are not working? Any fun travel stories lately?')" class="mt-1 block text-left text-[11px] text-[#2f7a55] hover:underline dark:text-[#74cca0]">
                                    "So what do you love doing when you're not working? Any fun travel stories lately?" ↗
                                </button>
                            </div>

                            <div class="rounded-xl border border-[#edf3ec] bg-[#f9fbf8] p-3 dark:border-[#243a2c] dark:bg-[#121f16]">
                                <div class="font-bold text-[#204a33] dark:text-[#5fe1a4]">Playful banter & curiosity:</div>
                                <button type="button" @click="pastePrompt('No way, really? You have to tell me how you got into that!')" class="mt-1 block text-left text-[11px] text-[#2f7a55] hover:underline dark:text-[#74cca0]">
                                    "No way, really? You have to tell me how you got into that!" ↗
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="space-y-2 text-xs text-[#525f51] dark:text-[#9bb1a1]">
                            <p>• Speak naturally in full sentences.</p>
                            <p>• Don't worry about mistakes — your coach will show the native way to say it.</p>
                            <p>• Save your favorite phrases directly to your flashcard deck!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Interactive Dialogue Stage & In-line Coach (8 Cols on Desktop) -->
            <div class="flex flex-col lg:col-span-8">
                <div class="flex flex-1 flex-col overflow-hidden rounded-2xl border border-[#dde3d8] bg-white shadow-sm dark:border-[#243a2c] dark:bg-[#15231a]">
                    <!-- Scenario Stage Header -->
                    <div class="flex items-center justify-between border-b border-[#edf0ea] bg-[#fafbfa] px-6 py-4 dark:border-[#223528] dark:bg-[#121e16]">
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-white text-xl shadow-xs dark:bg-[#1a2d21]">{{ $this->currentScenario()['avatar'] }}</span>
                            <div>
                                <h3 class="font-display text-base font-bold text-[#172c21] dark:text-[#ecf4ee]">{{ $this->currentScenario()['title'] }}</h3>
                                <p class="text-xs text-[#717b6d] dark:text-[#8ba393]">Talking with <strong>{{ $this->currentScenario()['partner'] }}</strong> · {{ $this->currentScenario()['setting'] }}</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-[#edf6ef] px-3 py-1 text-xs font-semibold text-[#2f7a55] dark:bg-[#193323] dark:text-[#52b788]">
                            Interactive Roleplay
                        </span>
                    </div>

                    <!-- Chat Messages Flow -->
                    <div class="flex-1 space-y-6 overflow-y-auto p-6 sm:p-8" id="chat-stream">
                        @foreach ($messages as $msg)
                            @if ($msg['role'] === 'partner')
                                <!-- Partner Message Bubble -->
                                <div class="flex items-start gap-3.5" wire:key="msg-{{ $msg['id'] }}">
                                    <div class="flex size-9 flex-none items-center justify-center rounded-xl bg-[#edf6ef] text-lg shadow-xs dark:bg-[#1a3324]">
                                        {{ $this->currentScenario()['avatar'] }}
                                    </div>
                                    <div class="max-w-[85%] rounded-2xl rounded-tl-xs border border-[#e4eae0] bg-[#f8faf7] p-4 text-sm leading-relaxed text-[#1b2f23] dark:border-[#223829] dark:bg-[#132218] dark:text-[#ecf4ee]">
                                        <div class="mb-1 flex items-center justify-between gap-4">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#2f7a55] dark:text-[#52b788]">
                                                {{ $this->currentScenario()['partner'] }}
                                            </span>
                                            <button
                                                type="button"
                                                @click="speak('{{ addslashes($msg['text']) }}')"
                                                class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[11px] font-semibold text-[#2f7a55] transition-colors hover:bg-black/5 dark:text-[#74cca0] dark:hover:bg-white/5"
                                                title="Listen to pronunciation"
                                            >
                                                <span :class="isSpeaking ? 'animate-bounce' : ''">🔊</span>
                                                <span>Listen</span>
                                            </button>
                                        </div>
                                        <div class="whitespace-pre-wrap">{{ $msg['text'] }}</div>
                                    </div>
                                </div>
                            @else
                                <!-- User Turn & Synchronized Coach Analysis -->
                                <div class="flex flex-col items-end space-y-3" wire:key="msg-{{ $msg['id'] }}">
                                    <!-- User Bubble -->
                                    <div class="max-w-[85%] rounded-2xl rounded-tr-xs bg-[#2f7a55] p-4 text-sm leading-relaxed text-white shadow-xs dark:bg-[#286647]">
                                        <div class="mb-1 text-[11px] font-bold uppercase tracking-wider text-white/70">
                                            You said:
                                        </div>
                                        <div class="whitespace-pre-wrap font-medium">{{ $msg['text'] }}</div>
                                    </div>

                                    <!-- Synchronized Fluency & Grammar Coach Card -->
                                    @if (! empty($msg['native_alternative']))
                                        <div class="w-full max-w-[95%] rounded-2xl border border-[#d6e7dc] bg-[#f3f9f5] p-4 text-xs shadow-xs sm:p-5 dark:border-[#204730] dark:bg-[#0f2418]">
                                            <div class="mb-2 flex items-center justify-between border-b border-[#e1eee5] pb-2 dark:border-[#1a3826]">
                                                <div class="flex items-center gap-1.5 font-bold uppercase tracking-wider text-[#205338] dark:text-[#5fe1a4]">
                                                    <span>💎</span>
                                                    <span>Coach Feedback & Alternatives</span>
                                                </div>
                                                @if (! ($msg['saved_to_deck'] ?? false))
                                                    <button
                                                        type="button"
                                                        wire:click="saveToDeck({{ $msg['id'] }}, 'native')"
                                                        class="inline-flex items-center gap-1 rounded-lg border border-[#badbca] bg-white px-2.5 py-1 text-[11px] font-semibold text-[#225b3d] shadow-2xs hover:bg-[#eaf4ee] dark:border-[#27533b] dark:bg-[#183524] dark:text-[#a0d9b9] dark:hover:bg-[#1f422e]"
                                                    >
                                                        <span>📌 Save Phrase to Deck</span>
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#2f7a55] dark:text-[#74cca0]">
                                                        ✓ Saved to Deck
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <!-- Native Alternative -->
                                                <div class="rounded-xl border border-[#dfece3] bg-white p-3 dark:border-[#1e3c2b] dark:bg-[#14281c]">
                                                    <div class="mb-1 flex items-center justify-between">
                                                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#2f7a55] dark:text-[#74cca0]">How a Native Speaks:</span>
                                                        <button type="button" @click="speak('{{ addslashes($msg['native_alternative']) }}')" class="text-[11px] text-[#2f7a55] dark:text-[#74cca0]">🔊</button>
                                                    </div>
                                                    <div class="font-medium text-[#163323] dark:text-[#e4efe7]">{{ $msg['native_alternative'] }}</div>
                                                </div>

                                                <!-- Smooth / Charming Variation -->
                                                @if (! empty($msg['smooth_variation']) && $msg['smooth_variation'] !== $msg['native_alternative'])
                                                    <div class="rounded-xl border border-[#dfece3] bg-white p-3 dark:border-[#1e3c2b] dark:bg-[#14281c]">
                                                        <div class="mb-1 flex items-center justify-between">
                                                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#d97706] dark:text-[#f59e0b]">✨ Smooth / Charming:</span>
                                                            <button type="button" @click="speak('{{ addslashes($msg['smooth_variation']) }}')" class="text-[11px] text-[#d97706] dark:text-[#f59e0b]">🔊</button>
                                                        </div>
                                                        <div class="font-medium text-[#163323] dark:text-[#e4efe7]">{{ $msg['smooth_variation'] }}</div>
                                                    </div>
                                                @endif
                                            </div>

                                            @if (! empty($msg['coaching_tip']))
                                                <div class="mt-3 text-[12px] leading-relaxed text-[#405447] dark:text-[#a2beaf]">
                                                    <strong class="font-semibold text-[#1e4832] dark:text-[#71c397]">💡 Why this works:</strong> {{ $msg['coaching_tip'] }}
                                                </div>
                                            @endif

                                            @if (! empty($msg['key_vocabulary']))
                                                <div class="mt-2.5 flex flex-wrap items-center gap-1.5 pt-2">
                                                    <span class="text-[10px] font-semibold text-[#667d70] dark:text-[#789684]">Key phrasing:</span>
                                                    @foreach ($msg['key_vocabulary'] as $term)
                                                        <span class="rounded-md bg-[#e4efe8] px-2 py-0.5 text-[11px] font-medium text-[#204a33] dark:bg-[#1a3827] dark:text-[#74cca0]">{{ $term }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        @if ($processing)
                            <div class="flex items-center gap-3 text-xs font-semibold text-[#2f7a55] dark:text-[#74cca0]">
                                <span class="flex size-7 animate-spin items-center justify-center rounded-full border-2 border-[#2f7a55] border-t-transparent dark:border-[#74cca0] dark:border-t-transparent"></span>
                                <span>{{ $this->currentScenario()['partner'] }} is typing… and coach is analyzing your English</span>
                            </div>
                        @endif

                        @if ($error)
                            <div class="rounded-xl border border-red-200 bg-red-50 p-3.5 text-xs text-red-700 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-300">
                                {{ $error }}
                            </div>
                        @endif
                    </div>

                    <!-- Input / Response Box -->
                    <div class="border-t border-[#edf0ea] bg-[#fafbfa] p-4 sm:p-6 dark:border-[#223528] dark:bg-[#121e16]">
                        <form wire:submit="sendMessage" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="relative flex-1">
                                <label for="dialogue-input" class="mb-1.5 block text-xs font-semibold text-[#4e5b4c] dark:text-[#a0b5a6]">
                                    Reply to {{ $this->currentScenario()['partner'] }} in English:
                                </label>
                                <textarea
                                    id="dialogue-input"
                                    rows="2"
                                    wire:model="userMessage"
                                    @disabled($processing)
                                    placeholder="Write your answer naturally in English... (e.g. Yes, I come here often and you look beautiful today!)"
                                    class="w-full resize-none rounded-xl border border-[#d2dbce] bg-white px-4 py-2.5 text-sm text-[#193123] shadow-2xs placeholder:text-[#919d8e] focus:border-[#2f7a55] focus:outline-hidden dark:border-[#27402f] dark:bg-[#16271c] dark:text-[#e4efe7] dark:placeholder:text-[#6a8473]"
                                    @keydown.ctrl.enter="$wire.sendMessage()"
                                    @keydown.cmd.enter="$wire.sendMessage()"
                                ></textarea>
                                @error('userMessage')
                                    <span class="text-[11px] text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                @disabled($processing)
                                class="inline-flex h-[46px] items-center justify-center gap-2 rounded-xl bg-[#2f7a55] px-6 text-sm font-semibold text-white shadow-sm transition-all hover:bg-[#256345] hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50 dark:bg-[#286647] dark:hover:bg-[#327e58]"
                            >
                                <span wire:loading.remove wire:target="sendMessage">Send</span>
                                <span wire:loading.remove wire:target="sendMessage">💬</span>
                                <span wire:loading wire:target="sendMessage">Sending…</span>
                            </button>
                        </form>
                        <div class="mt-2 flex items-center justify-between text-[11px] text-[#869282] dark:text-[#728779]">
                            <span>Tip: Press <kbd class="rounded bg-black/5 px-1 py-0.2 font-mono dark:bg-white/10">Ctrl+Enter</kbd> to send</span>
                            <span>Coach analyzes every turn in real time</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
