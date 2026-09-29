<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $student = auth()->guard('student')->user();

        if (!$student) {
            return redirect()->route('schools.login', ['school' => $request->route('school')])
                ->with('error', 'Please login first.');
        }

        // Enforce email verification
        if (!$student->hasVerifiedEmail()) {
            return redirect()->route('schools.verification.show', ['school' => $request->route('school')]);
        }

        // Check if user has guest role
        if ($student->role !== 'guest') {
            // If they're already a student, redirect to student dashboard
            return redirect()->route('schools.student.dashboard', ['school' => $request->route('school')])
                ->with('info', 'You already have student access.');
        }

        // AUD-005 Fix: School Boundary Check
        $schoolParam = $request->route('school');
        $school = $schoolParam instanceof \App\Models\School ? $schoolParam : \App\Models\School::where('slug', '=', $schoolParam, 'and')->first(['*']);
        if (!$school || (int)$student->school_id !== (int)$school->id) {
            auth()->guard('student')->logout();
            return redirect()->route('schools.login', ['school' => $school?->slug ?? $schoolParam])
                ->with('error', 'Unauthorized: You do not belong to this school.');
        }

        return $next($request);
    }
}
