<?php

namespace App\Ai\Agents;

use App\Ai\Prompts\ConversationPrompt;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

final class ConversationTurnAgent implements Agent
{
    use Promptable;

    public function __construct(
        private readonly string $scenarioKey = 'first_date',
    ) {}

    public function instructions(): string
    {
        return ConversationPrompt::instructions($this->scenarioKey);
    }
}
