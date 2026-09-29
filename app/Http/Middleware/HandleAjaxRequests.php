<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleAjaxRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If this is an AJAX request, set the flag BEFORE the view is rendered
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $request->attributes->set('is_ajax', true);
            view()->share('isAjax', true);
        }
        
        return $next($request);
    }
}
