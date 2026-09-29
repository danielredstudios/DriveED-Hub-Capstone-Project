<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $school = $request->route('school');

        if (!$school instanceof School) {
            return $next($request);
        }

        // Block access to deactivated schools
        if (array_key_exists('status', $school->getAttributes()) && $school->status !== 'active') {
            return response()->view('errors.inactive-school', ['school' => $school], 403);
        }

        // Skip school validation for logout routes to prevent conflicts
        $routeName = $request->route()?->getName();
        if ($routeName && Str::endsWith($routeName, '.logout')) {
            return $next($request);
        }

        $request->session()->put('school_id', $school->id);
        $request->session()->put('school_slug', $school->slug);

        // We keep app.timezone as UTC globally for consistent storage.
        // We can use $school->timezone for display-level conversion in views.

        view()->share('currentSchool', $school);
        view()->share('schoolUrl', static function (string $path = '') use ($school): string {
            $path = ltrim($path, '/');

            if ($path === '') {
                return url($school->slug);
            }

            return url($school->slug . '/' . $path);
        });
        view()->share('schoolRoute', static function (string $name, array $parameters = []) use ($school) {
            $routeName = str_starts_with($name, 'schools.') ? $name : 'schools.' . $name;

            $candidates = [$routeName, $routeName . '.protected'];

            foreach ($candidates as $candidate) {
                if (Route::has($candidate)) {
                    return route($candidate, array_merge(['school' => $school], $parameters));
                }
            }

            // Fallback: try to build a URL path for the given name to avoid throwing
            $fallbackPath = trim(str_replace('.', '/', preg_replace('/^schools\./', '', $routeName)), '/');

            return url($school->slug . '/' . $fallbackPath);
        });

        foreach (['admin', 'instructor', 'student'] as $guard) {
            $user = Auth::guard($guard)->user();

            if ($user && (int)$user->school_id !== (int)$school->id) {
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('schools.login', $school)
                    ->withErrors(['email' => 'Please login using the correct school portal.']);
            }
        }

        return $next($request);
    }
}
