<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('system-admin.login')) {
            return $next($request);
        }

        if (!Auth::guard('admin')->check()) {
            return redirect()->route('system-admin.login')->with('error', 'Please login as System Administrator.');
        }

        $admin = Auth::guard('admin')->user();

        if ($admin->role !== 'system_admin') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Access denied. System administrator privileges required.'
                ], 403);
            }

            if ($request->header('referer') === $request->fullUrl()) {
                return redirect()->route('welcome')->with('error', 'Access denied. System Administrator privileges required.');
            }

            return redirect()->back()->with('error', 'Access denied. System Administrator privileges required.');
        }

        return $next($request);
    }
}
