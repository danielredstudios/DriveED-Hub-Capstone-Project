<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolAdminOnly
{
    /**
     * Block branch secretaries from accessing school-admin-only routes.
     * Use this middleware on routes that only central school admins should access
     * (e.g., financial reports, admin management, settings).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            abort(403, 'Unauthorized.');
        }

        if ($admin->isBranchSecretary()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'This action is restricted to school administrators.',
                ], 403);
            }

            return redirect()->back()->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
