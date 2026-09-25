@props([
    'active' => 'today',
])

@php
    $sections = [
        'today' => ['label' => 'Today', 'url' => url('/today')],
        'listen' => ['label' => 'Listening', 'url' => url('/listen')],
        'conversations' => ['label' => 'Conversation', 'url' => url('/conversations')],
        'review' => ['label' => 'Review', 'url' => url('/review')],
        'writing' => ['label' => 'Writing', 'url' => url('/writing')],
        'lab' => ['label' => 'Lab', 'url' => url('/lab')],
    ];
@endphp

<header {{ $attributes->merge(['class' => 'verbally-nav']) }} aria-label="Primary navigation">
    <div class="flex items-center gap-3 sm:gap-6">
        <a href="{{ url('/') }}" class="verbally-brand" aria-label="Verbally home">
            <span class="verbally-logo-wrap">
                <img src="{{ asset('images/verbally-logo-lockup.png') }}" class="dark:hidden" alt="Verbally" width="207" height="178">
                <img src="{{ asset('images/verbally-logo-lockup-dark.png') }}" class="hidden dark:block" alt="Verbally" width="207" height="178">
            </span>
        </a>
        <nav class="flex items-center gap-1" aria-label="Sections">
            @foreach ($sections as $key => $section)
                @if ($active === $key)
                    <a href="{{ $section['url'] }}" class="rounded-lg bg-[#2f7a55] px-2.5 py-1 text-xs font-semibold text-white transition-colors dark:bg-[#235c40]" aria-current="page">{{ $section['label'] }}</a>
                @else
                    <a href="{{ $section['url'] }}" class="rounded-lg px-2.5 py-1 text-xs font-medium text-[#5c6357] transition-colors hover:bg-[#edf6ef] dark:text-[#a3b8aa] dark:hover:bg-[#1b2b21]">{{ $section['label'] }}</a>
                @endif
            @endforeach
        </nav>
    </div>
    @if ($slot->isNotEmpty())
        <div class="verbally-nav-meta">
            {{ $slot }}
        </div>
    @else
        <div class="verbally-nav-meta">
            <x-theme-toggle />
        </div>
    @endif
</header>
