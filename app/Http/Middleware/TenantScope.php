<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TenantScope middleware — currently DORMANT.
 *
 * To fully activate multi-tenancy you must first ensure that session('tenant_id')
 * is resolved and stored at login (e.g. in LoginResponse or AuthController), then:
 *   1. Register this middleware in bootstrap/app.php (or Kernel.php) for web routes.
 *   2. Activate the BelongsToTenant trait on models you want scoped.
 *   3. Populate the tenant_id column added by migration
 *      2026_04_15_000003_add_tenant_to_countries_table.php.
 *
 * Do NOT activate before session('tenant_id') is reliably populated, or all tenant
 * lookups will fall through to the "No tenant assigned" 403.
 */
class TenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Super admins can switch tenants via header
        if ($user->is_super_admin && $request->hasHeader('X-Tenant-ID')) {
            $tenantId = (int) $request->header('X-Tenant-ID');
            $tenant = Tenant::find($tenantId);

            if (!$tenant || !$tenant->isActive()) {
                abort(403, 'Tenant not found or inactive.');
            }
        } else {
            $tenantId = $user->tenant_id;

            if (!$tenantId && !$user->is_super_admin) {
                abort(403, 'No tenant assigned.');
            }
        }

        if ($tenantId) {
            // Bind tenant context — BelongsToTenant trait reads this
            app()->instance('current_tenant_id', $tenantId);
            app()->instance('current_tenant', Tenant::find($tenantId));
        }

        // Also bind country scope if user has one
        if ($user->country_id) {
            app()->instance('current_country_id', $user->country_id);
        }

        return $next($request);
    }
}
