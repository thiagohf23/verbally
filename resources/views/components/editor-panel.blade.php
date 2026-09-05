@props(['title', 'description', 'placeholder', 'value' => '', 'countLabel', 'shortcutLabel', 'model' => null, 'disabled' => false])

<section {{ $attributes->class(['flex flex-col bg-white p-6 sm:p-[30px_28px] lg:border-r lg:border-[#e8eae4] dark:bg-[#111a15] dark:lg:border-[#26382e]']) }}>
    <div class="mb-5 flex flex-col gap-[5px]">
        <h2 class="font-display text-[26px] font-medium tracking-[-0.015em] text-[#17352a] dark:text-[#ecf4ee]">{{ $title }}</h2>
        <p class="text-[13.5px] leading-[1.5] text-[#7a8175] dark:text-[#9cb2a4]">{{ $description }}</p>
    </div>
    <div class="flex min-h-[300px] flex-1 flex-col overflow-hidden rounded-[14px] border border-[#e2e5dd] bg-[#fbfcfa] dark:border-[#26382e] dark:bg-[#17241e]">
        <textarea aria-label="{{ $title }}" placeholder="{{ $placeholder }}" @if ($model) wire:model="{{ $model }}" @endif wire:loading.attr="disabled" @disabled($disabled) class="min-h-0 flex-1 resize-none border-0 bg-transparent p-[18px] text-[15px] leading-[1.65] text-[#21251f] outline-none placeholder:text-[#a3a99c] dark:text-[#ecf4ee] dark:placeholder:text-[#789082]">{{ $value }}</textarea>
        <div class="flex flex-none justify-end border-t border-[#eceee8] px-[14px] py-3 dark:border-[#26382e]"><span class="text-[11.5px] text-[#a3a99c] dark:text-[#789082]">{{ $countLabel }}</span></div>
    </div>
    <div class="mt-4 flex items-center justify-between gap-3">
        <span class="text-[12px] text-[#a3a99c] dark:text-[#789082]">{{ $shortcutLabel }}</span>
        {{ $action }}
    </div>
</section>
