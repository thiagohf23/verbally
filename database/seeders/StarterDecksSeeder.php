<?php

namespace Database\Seeders;

use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Database\Seeder;

class StarterDecksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. My Personal Corrections Deck
        Deck::firstOrCreate(
            ['slug' => 'my-corrections'],
            [
                'name' => 'My Corrections',
                'description' => 'Personal flashcards saved directly from your live practice and coaching lab.',
                'icon' => 'bookmark',
                'color' => '#7c3aed',
                'is_system' => true,
            ]
        );

        // 2. Daily Essential Patterns
        $daily = Deck::firstOrCreate(
            ['slug' => 'daily-patterns'],
            [
                'name' => 'Daily Essential Patterns',
                'description' => 'Crucial everyday structures, habits, and polite phrasing for natural conversations.',
                'icon' => 'chat',
                'color' => '#2f7a55',
                'is_system' => true,
            ]
        );

        $dailyCards = [
            [
                'front' => 'Como dizer: "Eu costumava acordar cedo todos os dias."',
                'back' => 'I used to wake up early every day.',
                'grammar_tag' => 'Past Habits',
                'explanation' => "Use 'used to + verb' for past routines or states that are no longer true.",
                'audio_text' => 'I used to wake up early every day.',
            ],
            [
                'front' => 'Como dizer: "Estou ansioso para te ver amanhã."',
                'back' => 'I look forward to seeing you tomorrow.',
                'grammar_tag' => 'Phrasal Verbs',
                'explanation' => "In 'look forward to', the 'to' is a preposition, so it requires an -ing verb (gerund).",
                'audio_text' => 'I look forward to seeing you tomorrow.',
            ],
            [
                'front' => 'Como dizer: "Quanto tempo leva para chegar lá?"',
                'back' => 'How long does it take to get there?',
                'grammar_tag' => 'Questions',
                'explanation' => "Use 'How long does it take + to [verb]' when asking about travel or task duration.",
                'audio_text' => 'How long does it take to get there?',
            ],
            [
                'front' => 'Como dizer: "Isso depende do clima."',
                'back' => 'It depends on the weather.',
                'grammar_tag' => 'Prepositions',
                'explanation' => "In English, 'depend' takes the preposition 'on' (never 'of').",
                'audio_text' => 'It depends on the weather.',
            ],
            [
                'front' => 'Como dizer: "Eu deveria ter te avisado antes."',
                'back' => 'I should have told you earlier.',
                'grammar_tag' => 'Modal Verbs',
                'explanation' => "Use 'should have + past participle' to express a past regret or missed responsibility.",
                'audio_text' => 'I should have told you earlier.',
            ],
        ];

        foreach ($dailyCards as $card) {
            Flashcard::firstOrCreate(
                ['deck_id' => $daily->id, 'front' => $card['front']],
                array_merge($card, [
                    'due_at' => now(),
                    'interval_days' => 0,
                    'ease_factor' => 2.50,
                    'repetitions' => 0,
                    'lapses' => 0,
                ])
            );
        }

        // 3. Tricky Prepositions (In, On, At)
        $prepositions = Deck::firstOrCreate(
            ['slug' => 'prepositions'],
            [
                'name' => 'Tricky Prepositions (In, On, At)',
                'description' => 'Stop confusing time and place prepositions once and for all.',
                'icon' => 'pin',
                'color' => '#2563eb',
                'is_system' => true,
            ]
        );

        $prepCards = [
            [
                'front' => 'Complete: I will meet you ___ 8 PM.',
                'back' => 'I will meet you at 8 PM.',
                'grammar_tag' => 'Prepositions',
                'explanation' => "Use 'at' for specific clock times (at 5:00, at midnight, at noon).",
                'audio_text' => 'I will meet you at 8 PM.',
            ],
            [
                'front' => 'Complete: The meeting is ___ Monday morning.',
                'back' => 'The meeting is on Monday morning.',
                'grammar_tag' => 'Prepositions',
                'explanation' => "Use 'on' for specific calendar days and dates (on Monday, on July 4th).",
                'audio_text' => 'The meeting is on Monday morning.',
            ],
            [
                'front' => 'Complete: He was born ___ 1995.',
                'back' => 'He was born in 1995.',
                'grammar_tag' => 'Prepositions',
                'explanation' => "Use 'in' for years, months, decades, and long time periods.",
                'audio_text' => 'He was born in 1995.',
            ],
            [
                'front' => 'Complete: She is sitting ___ the bus.',
                'back' => 'She is sitting on the bus.',
                'grammar_tag' => 'Prepositions',
                'explanation' => "Use 'on' for public transportation where passengers can walk upright (bus, train, plane).",
                'audio_text' => 'She is sitting on the bus.',
            ],
            [
                'front' => 'Complete: I am ___ home right now.',
                'back' => 'I am at home right now.',
                'grammar_tag' => 'Prepositions',
                'explanation' => "Use 'at home' as a fixed location expression without an article.",
                'audio_text' => 'I am at home right now.',
            ],
        ];

        foreach ($prepCards as $card) {
            Flashcard::firstOrCreate(
                ['deck_id' => $prepositions->id, 'front' => $card['front']],
                array_merge($card, [
                    'due_at' => now(),
                    'interval_days' => 0,
                    'ease_factor' => 2.50,
                    'repetitions' => 0,
                    'lapses' => 0,
                ])
            );
        }

        // 4. Irregular Verbs Mastery
        $verbs = Deck::firstOrCreate(
            ['slug' => 'irregular-verbs'],
            [
                'name' => 'Irregular Verbs Mastery',
                'description' => 'Fast recall of high-frequency simple past and past participle forms.',
                'icon' => 'bolt',
                'color' => '#d97706',
                'is_system' => true,
            ]
        );

        $verbCards = [
            [
                'front' => 'Passado simples e particípio de: go',
                'back' => 'went / gone',
                'grammar_tag' => 'Irregular Verbs',
                'explanation' => 'go (present) -> went (simple past) -> gone (past participle with have/has).',
                'audio_text' => 'go, went, gone',
            ],
            [
                'front' => 'Passado simples e particípio de: write',
                'back' => 'wrote / written',
                'grammar_tag' => 'Irregular Verbs',
                'explanation' => 'write (present) -> wrote (simple past) -> written (past participle).',
                'audio_text' => 'write, wrote, written',
            ],
            [
                'front' => 'Passado simples e particípio de: choose',
                'back' => 'chose / chosen',
                'grammar_tag' => 'Irregular Verbs',
                'explanation' => 'choose (present) -> chose (simple past) -> chosen (past participle).',
                'audio_text' => 'choose, chose, chosen',
            ],
            [
                'front' => 'Passado simples e particípio de: speak',
                'back' => 'spoke / spoken',
                'grammar_tag' => 'Irregular Verbs',
                'explanation' => 'speak (present) -> spoke (simple past) -> spoken (past participle).',
                'audio_text' => 'speak, spoke, spoken',
            ],
        ];

        foreach ($verbCards as $card) {
            Flashcard::firstOrCreate(
                ['deck_id' => $verbs->id, 'front' => $card['front']],
                array_merge($card, [
                    'due_at' => now(),
                    'interval_days' => 0,
                    'ease_factor' => 2.50,
                    'repetitions' => 0,
                    'lapses' => 0,
                ])
            );
        }
    }
}
