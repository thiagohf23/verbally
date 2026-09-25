<div class="min-h-screen bg-[#f7faf5] text-[#17352a] dark:bg-[#0e1712] dark:text-[#ecf4ee]" x-data="verballyListening">
    <x-nav active="listen" />

    <main class="mx-auto grid w-full max-w-[1320px] gap-6 px-4 py-7 lg:grid-cols-[300px_minmax(0,1fr)] lg:px-8">
        <aside class="rounded-2xl border border-[#dfe6dc] bg-white p-4 dark:border-[#243a2c] dark:bg-[#15231a]">
            <p class="text-[10px] font-bold uppercase tracking-[.14em] text-[#2f7a55]">Listen & notice</p><h1 class="mt-1 font-display text-xl font-semibold">Short conversations</h1><p class="mt-1 text-xs leading-relaxed text-[#748073]">Hear the whole scene before revealing any words.</p>
            <div class="mt-5 space-y-2">@foreach (self::episodes() as $key => $episode)<button type="button" wire:click="selectEpisode('{{ $key }}')" class="w-full rounded-xl border p-3 text-left {{ $episodeKey === $key ? 'border-[#2f7a55] bg-[#f1f8f3] dark:bg-[#193023]' : 'border-[#e3e8e0] dark:border-[#294032]' }}"><strong class="block text-sm">{{ $episode['title'] }}</strong><span class="mt-1 block text-[10px] text-[#7d887c]">{{ $episode['level'] }} · {{ $episode['length'] }}</span></button>@endforeach</div>
        </aside>

        <section class="rounded-2xl border border-[#dbe3d8] bg-white p-5 shadow-sm sm:p-8 dark:border-[#243a2c] dark:bg-[#15231a]">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-[10px] font-bold uppercase tracking-[.14em] text-[#2f7a55]">Audio first</p><h2 class="mt-1 font-display text-2xl font-semibold">{{ $this->currentEpisode()['title'] }}</h2><p class="mt-1 text-sm text-[#6f7c70] dark:text-[#91a697]">{{ $this->currentEpisode()['description'] }}</p></div><span class="rounded-full bg-[#edf6ef] px-3 py-1 text-xs font-semibold text-[#2f7a55] dark:bg-[#193323]">{{ $this->currentEpisode()['level'] }}</span></div>

            <div class="mt-8 rounded-3xl bg-[#17352a] p-6 text-white sm:p-8 dark:bg-[#0a120e]">
                <div class="flex flex-col items-center text-center"><span class="flex size-16 items-center justify-center rounded-full bg-white/10 text-3xl">🎧</span><h3 class="mt-4 text-lg font-semibold">Listen to the complete scene</h3><p class="mt-1 max-w-md text-sm text-white/65">No subtitles yet. Focus on who, what happened, and the final outcome.</p>
                    <div class="mt-5 flex flex-wrap justify-center gap-2"><button type="button" data-lines="{{ base64_encode(json_encode($this->currentEpisode()['lines'], JSON_THROW_ON_ERROR)) }}" x-on:click="playEncodedDialogue($el.dataset.lines); $wire.noteReplay()" class="rounded-xl bg-[#54b780] px-5 py-3 text-sm font-bold text-[#0f2b1d]" x-text="playing ? '■ Stop' : '▶ Play conversation'">▶ Play conversation</button><button type="button" x-on:click="transcriptVisible = !transcriptVisible" class="rounded-xl border border-white/20 px-4 py-3 text-sm font-semibold" x-text="transcriptVisible ? 'Hide transcript' : 'Reveal transcript'">Reveal transcript</button></div>
                </div>
            </div>

            <div x-show="transcriptVisible" x-cloak x-transition class="mt-5 space-y-3">@foreach ($this->currentEpisode()['lines'] as $line)<div class="flex gap-3 rounded-xl border border-[#e0e7dd] bg-[#fafcfa] p-3 dark:border-[#294032] dark:bg-[#132218]"><strong class="w-20 flex-none text-xs text-[#2f7a55]">{{ $line['speaker'] }}</strong><p class="text-sm leading-relaxed">{{ $line['text'] }}</p></div>@endforeach</div>

            <div class="mt-7 border-t border-[#e4eae1] pt-6 dark:border-[#294032]"><p class="text-xs font-bold uppercase tracking-wider text-[#718075]">Check your understanding</p><h3 class="mt-2 text-lg font-semibold">{{ $this->currentEpisode()['question'] }}</h3><div class="mt-3 grid gap-2">@foreach ($this->currentEpisode()['options'] as $key => $option)<button type="button" wire:click="answer('{{ $key }}')" class="rounded-xl border p-3 text-left text-sm {{ $selectedAnswer === $key ? 'border-[#2f7a55] bg-[#f1f8f3] dark:bg-[#193023]' : 'border-[#e1e7de] dark:border-[#294032]' }}">{{ strtoupper($key) }}. {{ $option }}</button>@endforeach</div>
                @if ($answered)<div class="mt-4 rounded-xl p-4 text-sm {{ $selectedAnswer === $this->currentEpisode()['answer'] ? 'bg-green-50 text-green-800 dark:bg-green-950/30 dark:text-green-300' : 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-300' }}">{{ $selectedAnswer === $this->currentEpisode()['answer'] ? 'Correct — you caught the key meaning.' : 'Not quite. Listen once more and then inspect the transcript.' }}</div><button type="button" wire:click="completeEpisode" class="mt-3 rounded-xl bg-[#2f7a55] px-4 py-2.5 text-sm font-semibold text-white">Complete listening</button>@endif
            </div>

            @if ($completed)<div class="mt-6 rounded-2xl border border-[#cfe2d4] bg-[#f3faf5] p-5 dark:border-[#2c5b40] dark:bg-[#11271b]"><p class="text-xs font-bold uppercase tracking-wider text-[#2f7a55]">Useful chunks</p><div class="mt-3 flex flex-wrap gap-2">@foreach ($this->currentEpisode()['chunks'] as $chunk => $meaning)<span class="rounded-xl bg-white px-3 py-2 text-xs shadow-xs dark:bg-[#193022]"><strong>{{ $chunk }}</strong> · {{ $meaning }}</span>@endforeach</div><a href="{{ url('/conversations') }}" class="mt-4 inline-flex rounded-xl bg-[#17352a] px-4 py-2.5 text-sm font-semibold text-white dark:bg-[#2f7a55]">Take a role in this scenario →</a></div>@endif
        </section>
    </main>
</div>
