@props(['segments'])

<p {{ $attributes->class(['mb-4 text-[15.5px] leading-[1.65] text-[#5c6357] dark:text-[#cbdad0]']) }}>
    @foreach ($segments as $segment)
        @if ($segment['type'] === 'removed')
            <del class="text-[#c0392b] decoration-[#e0a9a2] dark:text-[#f87171] dark:decoration-[#991b1b]">{{ $segment['original'] }}</del>
        @elseif ($segment['type'] === 'added')
            <ins class="rounded bg-[#e9f3ee] px-1 font-semibold text-[#2f7a55] no-underline dark:bg-[#1b3829] dark:text-[#4ade80]">{{ $segment['replacement'] }}</ins>
        @else
            {{ $segment['original'] }}
        @endif
    @endforeach
</p>
