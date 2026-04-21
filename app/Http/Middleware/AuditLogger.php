<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogger
{
    private array $auditedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), $this->auditedMethods) && $request->user()) {
            AuditLog::record(
                action: strtolower($request->method()) . ':' . $request->path(),
            );
        }

        return $response;
    }
}
