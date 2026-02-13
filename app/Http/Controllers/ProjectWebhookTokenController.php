<?php

namespace App\Http\Controllers;

use App\Http\Requests\RevokeProjectWebhookTokenRequest;
use App\Http\Requests\RotateProjectWebhookTokenRequest;
use App\Http\Requests\StoreProjectWebhookTokenRequest;
use App\Models\Project;
use App\Models\ProjectWebhookToken;
use App\Support\WebhookTokenManager;
use Illuminate\Http\JsonResponse;

class ProjectWebhookTokenController extends Controller
{
    public function store(
        StoreProjectWebhookTokenRequest $request,
        Project $project,
        WebhookTokenManager $webhookTokenManager,
    ): JsonResponse {
        $payload = $webhookTokenManager->issue(
            project: $project,
            actor: $request->user(),
            name: $request->string('name')->toString(),
        );

        /** @var ProjectWebhookToken $token */
        $token = $payload['token'];

        return response()->json([
            'id' => $token->id,
            'name' => $token->name,
            'token_prefix' => $token->token_prefix,
            'plain_text_token' => $payload['plain_text_token'],
            'revoked_at' => $token->revoked_at,
        ], 201);
    }

    public function rotate(
        RotateProjectWebhookTokenRequest $request,
        Project $project,
        ProjectWebhookToken $webhookToken,
        WebhookTokenManager $webhookTokenManager,
    ): JsonResponse {
        abort_if($webhookToken->project_id !== $project->getKey(), 404);
        abort_if($webhookToken->revoked_at !== null, 422, 'Token has already been revoked.');

        $payload = $webhookTokenManager->rotate($webhookToken, $request->user());

        /** @var ProjectWebhookToken $token */
        $token = $payload['token'];

        return response()->json([
            'id' => $token->id,
            'name' => $token->name,
            'token_prefix' => $token->token_prefix,
            'plain_text_token' => $payload['plain_text_token'],
            'revoked_at' => $token->revoked_at,
        ]);
    }

    public function destroy(
        RevokeProjectWebhookTokenRequest $request,
        Project $project,
        ProjectWebhookToken $webhookToken,
        WebhookTokenManager $webhookTokenManager,
    ): JsonResponse {
        abort_if($webhookToken->project_id !== $project->getKey(), 404);

        $webhookTokenManager->revoke($webhookToken);

        return response()->json([
            'status' => 'revoked',
            'id' => $webhookToken->id,
        ]);
    }
}
