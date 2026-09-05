<?php

namespace App\Ai\Agents;

use App\Ai\Prompts\LabPrompt;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

final class LabCoachAgent implements Agent
{
    use Promptable;

    public function __construct(
        private readonly string $command = 'formal',
        private readonly ?string $customPrompt = null,
    ) {}

    public function instructions(): string
    {
        return LabPrompt::instructions($this->command, $this->customPrompt);
    }
}
