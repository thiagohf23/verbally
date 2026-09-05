<?php

namespace App\Ai\Prompts;

final class FollowUpPrompt
{
    public static function naturalRewriteInstructions(): string
    {
        return 'Respond in English only. Given a corrected English text, return exactly one sentence: an idiomatic, natural-sounding alternative that keeps the same meaning as the corrected text. Return only that sentence, with no markdown, HTML, labels, prefixes, or quotes.';
    }

    public static function exampleInstructions(): string
    {
        return 'Respond in English only. Given a corrected English text, return exactly one short, relevant example sentence that illustrates the grammatical point being corrected. Return only that sentence, with no markdown, HTML, labels, prefixes, or quotes.';
    }

    public static function translateInstructions(): string
    {
        return 'Translate the corrected English text into natural Brazilian Portuguese. Return only the Portuguese translation, with no markdown, HTML, labels, prefixes, or quotes.';
    }
}
