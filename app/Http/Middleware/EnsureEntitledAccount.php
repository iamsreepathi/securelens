<?php

namespace App\Http\Middleware;

use App\Support\EntitlementChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEntitledAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && app(EntitlementChecker::class)->userHasEntitledTeam($user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'An active subscription is required for this action.',
            ], 402);
        }

        return redirect()->route('billing.edit')
            ->with('status', 'subscription-required');
    }

    public static function message(): string
    {
        return 'An active subscription is required for this action.';
    }
}
