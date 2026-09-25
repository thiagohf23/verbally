<div
    class="min-h-screen bg-[#f7faf5] text-[#17352a] dark:bg-[#0e1712] dark:text-[#ecf4ee]"
    x-data="verballyConversation"
    x-on:verbally-play-partner.window="playPartnerEvent($event.detail)"
>
    <x-nav active="conversations" class="verbally-learning-nav">
        <span class="verbally-session">{{ $this->learnerTurns() }}/5 turns · audio first</span>
        <x-theme-toggle />
        <button type="button" wire:click="clearConversation" @disabled($processing) class="verbally-clear">↺ Restart</button>
    </x-nav>

    <main class="mx-auto grid w-full max-w-[1500px] gap-5 px-4 py-5 lg:grid-cols-[300px_minmax(0,1fr)] lg:px-8 lg:py-7">
        <aside class="flex flex-col gap-4">
            <section class="rounded-2xl border border-[#dfe6dc] bg-white p-4 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[.14em] text-[#2f7a55]">Your mission</p>
                        <h1 class="font-display text-lg font-semibold">5-turn conversation</h1>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-full bg-[#edf6ef] text-sm font-bold text-[#2f7a55] dark:bg-[#193323]">{{ $this->learnerTurns() }}/5</div>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-[#edf0ea] dark:bg-[#26382e]">
                    <div class="h-full rounded-full bg-[#2f7a55] transition-all" style="width: {{ min(100, $this->learnerTurns() * 20) }}%"></div>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-[#687266] dark:text-[#92a899]">Listen without reading, answer aloud, and keep the conversation moving. Perfection can wait.</p>
            </section>

            <section class="rounded-2xl border border-[#dfe6dc] bg-white p-4 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                <p class="mb-2 text-[10px] font-bold uppercase tracking-[.14em] text-[#7a877b]">Coach behavior</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" wire:click="setFeedbackMode('fluency')" class="rounded-xl border px-3 py-2 text-left text-xs {{ $feedbackMode === 'fluency' ? 'border-[#2f7a55] bg-[#edf6ef] text-[#1f5e3f] dark:bg-[#193323] dark:text-[#7bd3a3]' : 'border-[#e2e7df] text-[#687266] dark:border-[#294032] dark:text-[#93a99a]' }}"><strong class="block">Fluency</strong><span class="text-[10px]">Feedback at end</span></button>
                    <button type="button" wire:click="setFeedbackMode('study')" class="rounded-xl border px-3 py-2 text-left text-xs {{ $feedbackMode === 'study' ? 'border-[#2f7a55] bg-[#edf6ef] text-[#1f5e3f] dark:bg-[#193323] dark:text-[#7bd3a3]' : 'border-[#e2e7df] text-[#687266] dark:border-[#294032] dark:text-[#93a99a]' }}"><strong class="block">Study</strong><span class="text-[10px]">Coach every turn</span></button>
                </div>
            </section>

            <section class="rounded-2xl border border-[#dfe6dc] bg-white p-4 shadow-xs dark:border-[#243a2c] dark:bg-[#15231a]">
                <div class="mb-3 flex items-center justify-between"><p class="text-[10px] font-bold uppercase tracking-[.14em] text-[#7a877b]">Scenario</p><span class="text-[10px] text-[#8a9689]">Choose your context</span></div>
                <div class="flex flex-col gap-1.5">
                    @foreach (self::scenarios() as $key => $scenario)
                        <button type="button" wire:key="scenario-{{ $key }}" wire:click="switchScenario('{{ $key }}')" class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-left transition {{ $scenarioKey === $key ? 'border-[#2f7a55] bg-[#f1f8f3] dark:border-[#438866] dark:bg-[#193023]' : 'border-transparent hover:border-[#e1e7de] hover:bg-[#f8faf7] dark:hover:border-[#294032] dark:hover:bg-[#19281f]' }}">
                            <span class="text-xl">{{ $scenario['avatar'] }}</span>
                            <span class="min-w-0"><strong class="block truncate text-xs">{{ $scenario['title'] }}</strong><span class="block truncate text-[10px] text-[#7c887b] dark:text-[#849b8c]">{{ $scenario['setting'] }}</span></span>
                        </button>
                    @endforeach
                </div>
            </section>
        </aside>

        <section class="flex min-h-[760px] min-w-0 flex-col overflow-hidden rounded-2xl border border-[#dbe3d8] bg-white shadow-sm dark:border-[#243a2c] dark:bg-[#15231a]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e8ede6] bg-[#fbfcfa] px-5 py-4 dark:border-[#24372c] dark:bg-[#121e16]">
                <div class="flex items-center gap-3">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-[#edf6ef] text-xl dark:bg-[#193323]">{{ $this->currentScenario()['avatar'] }}</span>
                    <div><h2 class="font-display text-base font-semibold">{{ $this->currentScenario()['title'] }}</h2><p class="text-xs text-[#748073] dark:text-[#849b8c]">Talking with {{ $this->currentScenario()['partner'] }} · transcript hidden by default</p></div>
                </div>
                <div class="flex items-center gap-2 rounded-xl border border-[#dde5da] bg-white p-1 dark:border-[#294032] dark:bg-[#17261d]">
                    <span class="pl-2 text-[10px] font-semibold uppercase tracking-wider text-[#7a877b]">Speed</span>
                    @foreach ([0.75, 0.9, 1.0] as $rate)
                        <button type="button" x-on:click="setPlaybackRate({{ $rate }})" x-bind:class="playbackRate === {{ $rate }} ? 'bg-[#2f7a55] text-white' : 'text-[#657164] dark:text-[#9bb09f]'" class="rounded-lg px-2 py-1 text-[11px] font-semibold">{{ $rate }}×</button>
                    @endforeach
                </div>
            </div>

            <div id="chat-stream" class="flex-1 space-y-6 overflow-y-auto px-5 py-6 sm:px-8">
                @foreach ($messages as $msg)
                    @if ($msg['role'] === 'partner')
                        <article class="flex items-start gap-3" wire:key="message-{{ $msg['id'] }}">
                            <span class="flex size-9 flex-none items-center justify-center rounded-xl bg-[#edf6ef] text-lg dark:bg-[#193323]">{{ $this->currentScenario()['avatar'] }}</span>
                            <div class="max-w-[88%] rounded-2xl rounded-tl-sm border border-[#e0e7dd] bg-[#f8faf7] p-4 dark:border-[#28402f] dark:bg-[#132218]">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="mr-auto text-[10px] font-bold uppercase tracking-[.12em] text-[#2f7a55]">{{ $this->currentScenario()['partner'] }}</span>
                                    <button type="button" data-speech="{{ $msg['text'] }}" x-on:click="speak($el.dataset.speech, {{ $msg['id'] }})" class="rounded-lg bg-[#2f7a55] px-3 py-1.5 text-xs font-semibold text-white">🔊 Listen</button>
                                    <button type="button" x-on:click="toggleTranscript({{ $msg['id'] }})" class="rounded-lg border border-[#d7e0d4] px-3 py-1.5 text-xs font-medium text-[#526052] dark:border-[#2b4433] dark:text-[#a6b9aa]" x-text="isTranscriptVisible({{ $msg['id'] }}) ? 'Hide text' : 'Show transcript'">Show transcript</button>
                                </div>
                                <div x-cloak x-show="isTranscriptVisible({{ $msg['id'] }})" x-transition class="mt-3 border-t border-[#e2e8df] pt-3 text-sm leading-relaxed dark:border-[#294032]">{{ $msg['text'] }}</div>
                                <p x-show="!isTranscriptVisible({{ $msg['id'] }})" class="mt-3 text-xs italic text-[#7e897c] dark:text-[#819687]">Listen first. Reveal only after you have formed a guess.</p>
                            </div>
                        </article>
                    @else
                        <article class="flex flex-col items-end gap-3" wire:key="message-{{ $msg['id'] }}">
                            <div class="max-w-[82%] rounded-2xl rounded-tr-sm bg-[#2f7a55] px-4 py-3 text-sm leading-relaxed text-white"><div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-white/70">{{ ($msg['source'] ?? 'text') === 'voice' ? '🎙️ You said' : '⌨️ You wrote' }}</div>{{ $msg['text'] }}</div>

                            @if (($msg['source'] ?? 'text') === 'voice' && ! empty($msg['pronunciation']))
                                @php($pronunciation = $msg['pronunciation'])
                                <div class="w-full max-w-[92%] rounded-2xl border border-[#d9e7dd] bg-[#f4faf6] p-4 dark:border-[#245038] dark:bg-[#10251a]">
                                    <div class="flex items-center justify-between gap-3">
                                        <div><p class="text-[10px] font-bold uppercase tracking-[.12em] text-[#2f7a55]">Pronunciation coach</p><p class="mt-1 text-xs leading-relaxed text-[#496052] dark:text-[#a3bbab]">{{ $pronunciation['summary_pt'] ?? '' }}</p></div>
                                        @if (is_int($pronunciation['clarity_score'] ?? null))
                                            <div class="flex size-12 flex-none flex-col items-center justify-center rounded-full border-2 border-[#5aaa7c] bg-white dark:bg-[#173021]"><strong class="text-sm text-[#246542]">{{ $pronunciation['clarity_score'] }}</strong><span class="text-[8px] uppercase text-[#738375]">clear</span></div>
                                        @endif
                                    </div>
                                    @if (! empty($pronunciation['sound_focus']))
                                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                            @foreach ($pronunciation['sound_focus'] as $focus)
                                                <div class="rounded-xl border border-[#dce9df] bg-white p-3 text-xs dark:border-[#285039] dark:bg-[#163020]"><div class="font-semibold text-[#1f5e3f] dark:text-[#78d3a3]">{{ $focus['word'] ?? '' }} <span class="font-mono">{{ $focus['ipa'] ?? $focus['sound'] ?? '' }}</span></div><p class="mt-1 leading-relaxed text-[#536357] dark:text-[#a6bbaa]">{{ $focus['mouth_tip_pt'] ?? '' }}</p><p class="mt-1 font-medium text-[#2f7a55]">{{ $focus['practice_pt'] ?? '' }}</p></div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if (! ($pronunciation['audio_analyzed'] ?? false))<p class="mt-2 text-[10px] text-amber-700 dark:text-amber-400">Feedback limitado à transcrição do navegador; nenhum diagnóstico de som foi inventado.</p>@endif
                                </div>
                            @endif

                            @if ($feedbackMode === 'study' && ! empty($msg['native_alternative']))
                                <div class="w-full max-w-[92%] rounded-2xl border border-[#dce7df] bg-white p-4 text-xs dark:border-[#294234] dark:bg-[#17281e]">
                                    <div class="mb-3 flex items-center justify-between gap-3"><p class="font-bold uppercase tracking-wider text-[#2f7a55]">Turn feedback</p><button type="button" wire:click="saveToDeck({{ $msg['id'] }})" @disabled($msg['saved_to_deck'] ?? false) class="rounded-lg border border-[#cddbcf] px-2 py-1 disabled:opacity-60 dark:border-[#31513c]">{{ ($msg['saved_to_deck'] ?? false) ? '✓ Saved' : '＋ Save phrase' }}</button></div>
                                    <div class="grid gap-2 sm:grid-cols-2"><div class="rounded-xl bg-[#f5f8f4] p-3 dark:bg-[#122219]"><span class="block text-[9px] font-bold uppercase text-[#718075]">Natural</span><strong class="mt-1 block text-sm">{{ $msg['native_alternative'] }}</strong></div><div class="rounded-xl bg-[#fff8eb] p-3 dark:bg-[#2a2112]"><span class="block text-[9px] font-bold uppercase text-amber-700 dark:text-amber-400">Conversational</span><strong class="mt-1 block text-sm">{{ $msg['smooth_variation'] }}</strong></div></div>
                                    <p class="mt-2 leading-relaxed text-[#5d6a60] dark:text-[#a2b5a6]">{{ $msg['coaching_tip'] }}</p>
                                </div>
                            @endif
                        </article>
                    @endif
                @endforeach

                @if ($processing)<div class="flex items-center gap-3 text-xs font-semibold text-[#2f7a55]"><span class="size-6 animate-spin rounded-full border-2 border-[#2f7a55] border-t-transparent"></span><span>{{ $this->currentScenario()['partner'] }} is responding…</span></div>@endif
                @if ($error)<div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300" role="alert">{{ $error }}</div>@endif

                @if ($sessionCompleted && $sessionFeedback)
                    <section class="rounded-2xl border-2 border-[#7ab792] bg-[#f2faf4] p-5 dark:border-[#2d6947] dark:bg-[#10251a]" id="session-feedback">
                        <p class="text-[10px] font-bold uppercase tracking-[.14em] text-[#2f7a55]">Session complete</p>
                        <h3 class="mt-1 font-display text-xl font-semibold">{{ $sessionFeedback['summary_pt'] ?? '' }}</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2"><div><h4 class="text-xs font-bold uppercase tracking-wider text-[#607064]">What worked</h4><ul class="mt-2 space-y-1 text-sm">@foreach (($sessionFeedback['wins'] ?? []) as $win)<li>✓ {{ $win }}</li>@endforeach</ul></div><div><h4 class="text-xs font-bold uppercase tracking-wider text-[#607064]">Next focus</h4><ul class="mt-2 space-y-1 text-sm">@foreach (($sessionFeedback['next_focus'] ?? []) as $focus)<li>→ {{ $focus }}</li>@endforeach</ul></div></div>
                        @if (! empty($sessionFeedback['corrections']))<div class="mt-4 space-y-2">@foreach ($sessionFeedback['corrections'] as $correction)<div class="rounded-xl border border-[#dce8df] bg-white p-3 text-sm dark:border-[#2b4936] dark:bg-[#173021]"><span class="text-red-500 line-through">{{ $correction['original'] ?? '' }}</span><span class="mx-2">→</span><strong class="text-[#22633f] dark:text-[#7cd5a4]">{{ $correction['better'] ?? '' }}</strong><p class="mt-1 text-xs text-[#68766b] dark:text-[#9daf9f]">{{ $correction['reason_pt'] ?? '' }}</p></div>@endforeach</div>@endif
                        @if (! empty($sessionFeedback['phrases_to_keep']))
                            <div class="mt-4"><h4 class="text-xs font-bold uppercase tracking-wider text-[#607064]">Shadowing · listen, then copy the rhythm</h4><div class="mt-2 flex flex-wrap gap-2">@foreach ($sessionFeedback['phrases_to_keep'] as $phrase)<div class="flex items-center gap-2 rounded-xl border border-[#cfe0d4] bg-white p-2 pl-3 text-sm dark:border-[#31533d] dark:bg-[#173021]"><strong>{{ $phrase }}</strong><button type="button" data-speech="{{ $phrase }}" x-on:click="speak($el.dataset.speech, null, false)" class="rounded-lg bg-[#edf6ef] px-2 py-1 text-xs text-[#2f7a55] dark:bg-[#21412d]">🔊</button><button type="button" data-phrase="{{ $phrase }}" x-on:click="$wire.saveSessionPhrase($el.dataset.phrase)" class="rounded-lg border border-[#d5e1d7] px-2 py-1 text-xs dark:border-[#31533d]">＋ Save</button></div>@endforeach</div></div>
                        @endif
                        <div class="mt-5 flex flex-wrap gap-2"><button type="button" wire:click="clearConversation" class="rounded-xl bg-[#2f7a55] px-4 py-2 text-sm font-semibold text-white">Start another session</button><a href="{{ url('/today') }}" class="rounded-xl border border-[#bfd3c5] bg-white px-4 py-2 text-sm font-semibold dark:border-[#31533d] dark:bg-[#173021]">Back to today</a></div>
                    </section>
                @endif
            </div>

            @unless ($sessionCompleted)
                <div class="border-t border-[#e5ebe2] bg-[#fbfcfa] p-4 sm:p-5 dark:border-[#24372c] dark:bg-[#121e16]">
                    <div x-show="recording || uploading" x-cloak class="mb-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300"><span x-show="recording">● Recording <strong x-text="recordingSeconds + 's'"></strong> — speak naturally, then stop.</span><span x-show="uploading">Analyzing your audio… <strong x-text="uploadProgress + '%'"></strong></span><p x-show="liveTranscript" class="mt-1 text-xs" x-text="liveTranscript"></p></div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="flex-1"><label for="dialogue-input" class="mb-1.5 block text-xs font-semibold">Your response in English</label><textarea id="dialogue-input" rows="2" wire:model="userMessage" @disabled($processing) placeholder="Speak first, or type when you need a quiet alternative…" class="w-full resize-none rounded-xl border border-[#d2dbce] bg-white px-4 py-3 text-sm shadow-2xs placeholder:text-[#919d8e] focus:border-[#2f7a55] focus:outline-hidden dark:border-[#27402f] dark:bg-[#16271c] dark:text-[#e4efe7]"></textarea>@error('userMessage')<span class="text-xs text-red-500">{{ $message }}</span>@enderror @error('voiceRecording')<span class="text-xs text-red-500">{{ $message }}</span>@enderror</div>
                        <div class="flex gap-2"><button type="button" x-on:click="recording ? stopRecording() : startRecording()" x-bind:disabled="uploading || !recordingSupported" x-bind:class="recording ? 'bg-red-600 hover:bg-red-700' : 'bg-[#2f7a55] hover:bg-[#276847]'" class="inline-flex h-12 min-w-[150px] items-center justify-center gap-2 rounded-xl px-5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"><span x-text="recording ? '■ Stop & send' : '🎙️ Start speaking'">🎙️ Start speaking</span></button><button type="button" wire:click="sendMessage" @disabled($processing) class="inline-flex h-12 items-center justify-center rounded-xl border border-[#ccd7c9] bg-white px-4 text-sm font-semibold dark:border-[#31503b] dark:bg-[#193022]">Send text</button></div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-[11px] text-[#7c887b] dark:text-[#7f9586]"><span x-show="recordingSupported">Your recording is analyzed, then discarded.</span><span x-show="!recordingSupported">Voice recording is unavailable in this browser; typing still works.</span>@if ($this->learnerTurns() > 0)<button type="button" wire:click="finishSession" class="font-semibold text-[#2f7a55] underline decoration-dotted underline-offset-4">Finish now & see focused feedback</button>@endif</div>
                </div>
            @endunless
        </section>
    </main>
</div>
