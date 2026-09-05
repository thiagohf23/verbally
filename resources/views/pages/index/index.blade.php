<div class="verbally-shell">
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
                    <a href="{{ url('/') }}" class="rounded-lg bg-[#2f7a55] px-2.5 py-1 text-xs font-semibold text-white transition-colors dark:bg-[#235c40]" aria-current="page">Practice</a>
                    <a href="{{ url('/lab') }}" class="rounded-lg px-2.5 py-1 text-xs font-medium text-[#5c6357] transition-colors hover:bg-[#edf6ef] dark:text-[#a3b8aa] dark:hover:bg-[#1b2b21]">Coaching Lab</a>
                </nav>
            </div>
            <div class="verbally-nav-meta">
                <span class="verbally-session">Session · {{ $this->completedCorrections() }} corrections</span>
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
                <button type="button" x-on:click="$wire.clearSession()" @disabled($processing) class="verbally-clear" aria-label="Clear correction session">
                    Clear session
                </button>
            </div>
        </header>
    @endisland

    <main class="verbally-main">
        @island(name: 'editor', always: true)
            <section class="verbally-editor" aria-labelledby="editor-title">
                <div class="verbally-editor-intro">
                    <p class="verbally-eyebrow">English practice, made personal</p>
                    <h1 id="editor-title">Make your English<br><em>sound like you.</em></h1>
                    <p class="verbally-lede">Write freely. Get thoughtful corrections, clear explanations, and a better way to say what you mean.</p>
                </div>

                <form wire:submit="submitText" class="verbally-compose">
                    <label for="verbally-text" class="verbally-compose-label">Write in English</label>
                    <textarea id="verbally-text" aria-label="Write in English" aria-describedby="verbally-hint verbally-count" placeholder="Try: Yesterday I go to the park..." wire:model="text" wire:loading.attr="disabled" @disabled($processing || $this->completedCorrections() >= 20)></textarea>
                    <div class="verbally-compose-footer">
                        <span id="verbally-count" class="verbally-count">{{ mb_strlen($text) }} / 2000</span>
                        <span id="verbally-hint" class="verbally-hint">Enter to correct · Shift + Enter for a new line</span>
                    </div>
                </form>

                @error('text')
                    <p class="verbally-form-error" role="alert">{{ $message }}</p>
                @enderror

                <button type="button" x-on:click="$wire.submitText()" wire:loading.attr="disabled" @disabled($processing || $this->completedCorrections() >= 20) class="verbally-submit">
                    <span wire:loading.remove>Correct my text</span>
                    <span wire:loading>Correcting…</span>
                </button>

                <div class="verbally-editor-note"><span>Corrections are for learning, not perfection.</span></div>
            </section>
        @endisland

        @island(name: 'conversation', always: true)
            <section class="verbally-conversation" aria-labelledby="conversation-title">
                <div class="verbally-conversation-head">
                    <div><p class="verbally-eyebrow">Your practice space</p><h2 id="conversation-title">Conversation</h2></div>
                    <span class="verbally-date">Today</span>
                </div>
                <div data-conversation-scroll class="verbally-conversation-scroll" aria-live="polite">
                    @forelse ($attempts as $attempt)
                        <div class="verbally-attempt">
                            <div class="verbally-user-label">You wrote</div>
                            <x-user-bubble :message="$attempt['text']" />
                            <div class="verbally-correction">
                                <livewire:correction-attempt :attempt-id="$attempt['id']" :submission="$attempt['text']" :session-processing="$processing" :wire:key="'attempt-'.$attempt['id']" />
                            </div>
                        </div>
                    @empty
                        <div class="verbally-empty" role="status">
                            <p>Your corrections will appear here.</p>
                            <span>Start with a sentence on the left.</span>
                        </div>
                    @endforelse
                </div>
            </section>
        @endisland
    </main>
</div>
