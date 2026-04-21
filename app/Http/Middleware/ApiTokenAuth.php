<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next, ?string $scope = null): Response
    {
        $bearer = $request->bearerToken();

        if (!$bearer) {
            return response()->json(['error' => 'API token required'], 401);
        }

        $token = ApiToken::where('token', hash('sha256', $bearer))
            ->where('is_active', true)
            ->first();

        if (!$token || !$token->isValid()) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        if ($scope && !$token->hasScope($scope)) {
            return response()->json(['error' => "Missing scope: {$scope}"], 403);
        }

        // Set tenant context
        app()->instance('current_tenant_id', $token->tenant_id);

        // Update last used
        $token->update(['last_used_at' => now()]);

        // Set user for audit
        auth()->setUser($token->user);

        return $next($request);
    }
}
