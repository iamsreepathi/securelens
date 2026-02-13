<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectWebhookToken;
use App\Models\User;
use Illuminate\Support\Str;

class WebhookTokenManager
{
    /**
     * @return array{token: ProjectWebhookToken, plain_text_token: string}
     */
    public function issue(Project $project, User $actor, string $name, ?string $rotatedFromId = null): array
    {
        $plainTextToken = 'sl_ing_'.Str::random(48);
        $tokenHash = hash('sha256', $plainTextToken);

        $token = $project->webhookTokens()->create([
            'created_by' => $actor->getKey(),
            'rotated_from_id' => $rotatedFromId,
            'name' => $name,
            'token_prefix' => substr($plainTextToken, 0, 12),
            'token_hash' => $tokenHash,
        ]);

        return [
            'token' => $token,
            'plain_text_token' => $plainTextToken,
        ];
    }

    /**
     * @return array{token: ProjectWebhookToken, plain_text_token: string}
     */
    public function rotate(ProjectWebhookToken $token, User $actor): array
    {
        $token->forceFill([
            'revoked_at' => now(),
        ])->save();

        return $this->issue(
            project: $token->project,
            actor: $actor,
            name: $token->name,
            rotatedFromId: $token->getKey(),
        );
    }

    public function revoke(ProjectWebhookToken $token): void
    {
        $token->forceFill([
            'revoked_at' => now(),
        ])->save();
    }

    public function resolveActiveToken(Project $project, string $plainTextToken): ?ProjectWebhookToken
    {
        $tokenHash = hash('sha256', $plainTextToken);

        $token = $project->webhookTokens()
            ->where('token_hash', $tokenHash)
            ->whereNull('revoked_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($token === null) {
            return null;
        }

        $token->forceFill([
            'last_used_at' => now(),
        ])->save();

        return $token;
    }
}
