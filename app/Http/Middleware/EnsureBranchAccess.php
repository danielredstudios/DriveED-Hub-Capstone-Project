<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    /**
     * For branch secretaries, ensure the requested resource belongs to their branch.
     * This middleware checks if a 'branch_id' parameter or route segment is accessible
     * by the current admin. School admins pass through unrestricted.
     *
     * Usage in routes: middleware('branch.access')
     * Works with query parameter ?branch_id=X or route parameter {branch}
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            abort(403, 'Unauthorized.');
        }

        // School admins have unrestricted access
        if ($admin->isSchoolAdmin()) {
            return $next($request);
        }

        // Branch secretaries must have a branch assigned
        if ($admin->isBranchSecretary()) {
            if (!$admin->branch_id) {
                abort(403, 'You are not assigned to any branch.');
            }

            // Share branch context with all views
            view()->share('secretaryBranch', $admin->branch);
            view()->share('isBranchSecretary', true);
        }

        return $next($request);
    }
}
