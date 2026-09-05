@props(['rewriteLabel', 'examplesLabel', 'translateLabel' => 'Translate (PT)', 'saveLabel' => 'Save to Deck', 'rewriteAction' => null, 'examplesAction' => null, 'translateAction' => null, 'saveAction' => null, 'saved' => false, 'disabled' => false])

<div class="mt-[15px] flex flex-wrap gap-2">
    @if ($saveAction)
        <button
            type="button"
            x-on:click="{{ $saveAction }}"
            @disabled($disabled || $saved)
            class="rounded-lg border border-[#e2e5dd] bg-[#fbfcfa] px-3 py-1.5 text-[12.5px] font-medium text-[#5c6357] transition-colors hover:bg-white hover:text-[#17352a] disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0] dark:hover:bg-[#1c2c23] dark:hover:text-[#ecf4ee]"
        >
            {{ $saved ? '✓ Saved to Deck' : '📌 Save to Deck' }}
        </button>
    @endif
    <button type="button" @if ($translateAction) x-on:click="{{ $translateAction }}" @endif @disabled($disabled) class="rounded-lg border border-[#e2e5dd] bg-[#fbfcfa] px-3 py-1.5 text-[12.5px] font-medium text-[#5c6357] transition-colors hover:bg-white hover:text-[#17352a] disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0] dark:hover:bg-[#1c2c23] dark:hover:text-[#ecf4ee]">{{ $translateLabel }}</button>
    <button type="button" @if ($rewriteAction) x-on:click="{{ $rewriteAction }}" @endif @disabled($disabled) class="rounded-lg border border-[#e2e5dd] bg-[#fbfcfa] px-3 py-1.5 text-[12.5px] font-medium text-[#5c6357] transition-colors hover:bg-white hover:text-[#17352a] disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0] dark:hover:bg-[#1c2c23] dark:hover:text-[#ecf4ee]">{{ $rewriteLabel }}</button>
    <button type="button" @if ($examplesAction) x-on:click="{{ $examplesAction }}" @endif @disabled($disabled) class="rounded-lg border border-[#e2e5dd] bg-[#fbfcfa] px-3 py-1.5 text-[12.5px] font-medium text-[#5c6357] transition-colors hover:bg-white hover:text-[#17352a] disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#273d2f] dark:bg-[#15221b] dark:text-[#cbdad0] dark:hover:bg-[#1c2c23] dark:hover:text-[#ecf4ee]">{{ $examplesLabel }}</button>
</div>

