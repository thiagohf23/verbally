<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningSession extends Model
{
    public const KIND_CONVERSATION = 'conversation';

    public const KIND_LISTENING = 'listening';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    /** @var list<string> */
    protected $fillable = [
        'kind',
        'scenario_key',
        'feedback_mode',
        'status',
        'messages',
        'feedback',
        'turns_count',
        'replay_count',
        'speaking_seconds',
        'started_at',
        'completed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'messages' => 'array',
            'feedback' => 'array',
            'turns_count' => 'integer',
            'replay_count' => 'integer',
            'speaking_seconds' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
