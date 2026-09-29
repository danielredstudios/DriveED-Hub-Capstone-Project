<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (Railway, Heroku, etc.) - required for HTTPS
        $middleware->trustProxies(at: '*');

        // Invitation onboarding links are token-protected and may be opened without a stable browser session.
        // Exempt this endpoint from CSRF so account activation does not fail with 419.
        $middleware->validateCsrfTokens(except: [
            '*/onboard/*',
        ]);
        
        $middleware->alias([
            'school.context' => \App\Http\Middleware\EnsureSchoolContext::class,
            'ajax' => \App\Http\Middleware\HandleAjaxRequests::class,
            'guest.role' => \App\Http\Middleware\EnsureGuestRole::class,
            'student.role' => \App\Http\Middleware\EnsureStudentRole::class,
            'system.admin' => \App\Http\Middleware\EnsureSystemAdmin::class,
            'redirect.system.admin' => \App\Http\Middleware\RedirectSystemAdmin::class,
            'school.admin.only' => \App\Http\Middleware\EnsureSchoolAdminOnly::class,
            'branch.access' => \App\Http\Middleware\EnsureBranchAccess::class,
            'nocache' => \App\Http\Middleware\NoCache::class,
        ]);
        
        // Handle guest redirects for multi-tenant authentication
        $middleware->redirectGuestsTo(function ($request) {
            // Try to extract school from the URL
            $segments = $request->segments();
            if (!empty($segments[0])) {
                return route('schools.login', ['school' => $segments[0]]);
            }
            // Fallback to a default school or home page
            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle CSRF token mismatch (419) - redirect back with input instead of error page
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Your session has expired. Please refresh the page.',
                    ], 419);
                }

                return redirect()->back()
                    ->withInput($request->except('_token', 'password', 'password_confirmation'))
                    ->with('error', 'Your session expired. Please submit the form again.')
                    ->withErrors(['session' => 'Your session expired. Please submit the form again.']);
            }
        });

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page.',
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('error', 'Your session expired. Please submit the form again.')
                ->withErrors(['session' => 'Your session expired. Please submit the form again.']);
        });
    })->create();
