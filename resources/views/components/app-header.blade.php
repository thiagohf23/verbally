@props(['mark', 'brand', 'tagline', 'sessionLabel', 'clearLabel', 'clearAction' => null, 'disabled' => false])

<header {{ $attributes->class(['flex h-[60px] flex-none items-center justify-between border-b border-[#e8eae4] bg-white px-5 sm:px-7 dark:border-[#26382e] dark:bg-[#15221b]']) }}>
    <div class="flex items-center gap-[11px]">
        <div class="flex size-[30px] items-center justify-center rounded-[9px] bg-[#2f7a55] font-display text-[19px] font-medium text-white">{{ $mark }}</div>
        <div class="flex flex-col leading-[1.05]"><span class="font-display text-xl font-medium tracking-[-0.01em] text-[#17352a] dark:text-[#ecf4ee]">{{ $brand }}</span><span class="text-[11px] font-medium tracking-[0.02em] text-[#8a9184] dark:text-[#7f998a]">{{ $tagline }}</span></div>
    </div>
    <div class="flex items-center gap-2 sm:gap-4"><span class="hidden items-center gap-[7px] text-[13px] text-[#6f7669] sm:flex dark:text-[#9cb2a4]"><span class="size-[7px] rounded-full bg-[#2f7a55]"></span>{{ $sessionLabel }}</span><button type="button" @if ($clearAction) x-on:click="$wire.{{ $clearAction }}()" @endif @disabled($disabled) class="flex items-center gap-[7px] rounded-lg border border-[#e2e5dd] bg-white px-[10px] py-[7px] text-xs font-medium text-[#6f7669] disabled:cursor-not-allowed disabled:opacity-50 sm:px-[13px] dark:border-[#283e30] dark:bg-[#18261f] dark:text-[#cbdad0]"> <span class="text-[13px] leading-none">↺</span>{{ $clearLabel }}</button></div>
</header>
