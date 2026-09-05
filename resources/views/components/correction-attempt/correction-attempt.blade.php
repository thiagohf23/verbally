<div>
    @island(name: 'attempt', always: true)
        <div @if ($pending) x-init="$nextTick(() => $wire.completeCorrection().catch(() => $wire.reportStaleOperation()))" @endif>
            <x-correction-card
                mark="V"
                label="Corrected"
                :segments="$segments"
                :explanations="$explanations"
                :corrected="$corrected"
                :error="$error"
                :off-topic="$offTopic"
                rewrite-label="Rewrite naturally"
                examples-label="More examples"
                translate-label="Translate (PT)"
                :attempt-id="$attemptId"
                :disabled="$sessionProcessing"
                :pending="$pending"
                :follow-ups="$followUps"
                :follow-up-pending="$followUpPending"
                :follow-up-kind="$followUpKind"
                :follow-up-error="$followUpError"
                :retry-action="'$wire.$parent.retryAttempt('.$attemptId.', \''.($errorStage ?? 'stream').'\')'"
                :follow-up-retry-action="'$wire.$parent.startFollowUp('.$attemptId.', \''.($followUpKind ?? 'rewrite').'\')'"
                :translate-action="'$wire.$parent.startFollowUp('.$attemptId.', \'translate\')'"
                :save-action="'$wire.saveToDeck()'"
                :saved="$savedToFlashcards"
                :rewrite-action="'$wire.$parent.startFollowUp('.$attemptId.', \'rewrite\')'"
                :examples-action="'$wire.$parent.startFollowUp('.$attemptId.', \'example\')'"
            />
        </div>
    @endisland
</div>
