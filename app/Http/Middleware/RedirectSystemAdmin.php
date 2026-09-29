<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectSystemAdmin
{
    /**
     * Handle an incoming request.
     * Redirect system admins to their dedicated portal
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            
            if ($admin->role === 'system_admin') {
                // System admin should use the system admin portal, not school-specific routes
                return redirect()->route('system-admin.dashboard')
                    ->with('info', 'System administrators should use the system admin portal.');
            }
        }

        return $next($request);
    }
}
