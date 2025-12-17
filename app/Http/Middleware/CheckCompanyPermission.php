<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCompanyPermission
{
    public function handle(Request $request, Closure $next, string $permissionName)
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        $company = $user->companyOwner?->company;
        if (!$company) {
            abort(403, 'No company context found associated with this user.');
        }

        if (! $company->hasPermissionTo($permissionName, 'web')) {
            abort(403, "Restricted: Your company plan does not include the '{$permissionName}' feature.");
        }

        return $next($request);
    }
}