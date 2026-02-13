<?php

namespace App\Support;

use App\Models\AdminOperationalAction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminOperationalActionRecorder
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>|null  $beforeState
     * @param  array<string, mixed>|null  $afterState
     */
    public function record(
        ?User $actor,
        string $action,
        string $targetType,
        ?string $targetId = null,
        array $metadata = [],
        ?array $beforeState = null,
        ?array $afterState = null,
    ): AdminOperationalAction {
        $record = AdminOperationalAction::query()->create([
            'actor_user_id' => $actor?->getKey(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'metadata' => $metadata,
        ]);

        Log::channel('ingestion')->warning('admin.operational_action.executed', [
            'action_id' => $record->id,
            'actor_user_id' => $record->actor_user_id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'metadata' => $metadata,
        ]);

        return $record;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>|null  $beforeState
     * @param  array<string, mixed>|null  $afterState
     */
    public function recordById(
        ?string $actorUserId,
        string $action,
        string $targetType,
        ?string $targetId = null,
        array $metadata = [],
        ?array $beforeState = null,
        ?array $afterState = null,
    ): AdminOperationalAction {
        $actor = $actorUserId !== null
            ? User::query()->find($actorUserId)
            : null;

        return $this->record($actor, $action, $targetType, $targetId, $metadata, $beforeState, $afterState);
    }
}
