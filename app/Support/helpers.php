<?php

declare(strict_types=1);

use App\Models\School;
use Illuminate\Http\Request;

if (! function_exists('current_school')) {
    function current_school(Request $request = null): ?School
    {
        $request ??= request();

        $school = $request?->route('school');
        if ($school instanceof School) {
            return $school;
        }

        if (is_numeric($school)) {
            $resolvedSchool = School::find((int) $school);
            if ($resolvedSchool instanceof School) {
                return $resolvedSchool;
            }
        }

        if (is_string($school) && $school !== '') {
            $resolvedSchool = School::where('slug', $school)->first();
            if ($resolvedSchool instanceof School) {
                return $resolvedSchool;
            }
        }

        $sessionSchoolId = $request?->session()?->get('school_id');
        if ($sessionSchoolId) {
            return School::find($sessionSchoolId);
        }

        return null;
    }
}

if (! function_exists('school_route')) {
    function school_route(string $name, array $parameters = [], ?School $school = null): string
    {
        $school ??= current_school();

        if (! $school instanceof School) {
            return route($name, $parameters);
        }

        $routeName = str_starts_with($name, 'schools.') ? $name : 'schools.' . $name;

        return route($routeName, array_merge(['school' => $school], $parameters));
    }
}

if (! function_exists('school_url')) {
    function school_url(string $path = '', ?School $school = null): string
    {
        $school ??= current_school();

        if (! $school instanceof School) {
            return url($path);
        }

        $path = ltrim($path, '/');

        if ($path === '') {
            return url($school->slug);
        }

        return url($school->slug . '/' . $path);
    }
}
