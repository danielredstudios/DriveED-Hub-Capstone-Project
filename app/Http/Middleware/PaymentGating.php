<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentGating
{
    /**
     * Handle an incoming request.
     * Blocks guests/students from protected routes unless they have an approved enrollment/payment.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // 1. Only apply to students/guests by default (Admins/Instructors bypass)
        if (auth()->guard('student')->check()) {
            $student = auth()->guard('student')->user();

            // 2. If already a 'student' (not guest), they satisfy basic gating
            if ($student->role === 'student') {
                return $next($request);
            }

            // 3. If 'guest', check if they have any approved enrollment/payment
            // Guest routes (like dashboard, payment submission) should bypass this middleware
            // But 'enrollment-protected' routes (like scheduling, course content) must check status
            $hasApprovedEnrollment = $student->enrollmentRequests()
                ->where('status', 'approved')
                ->exists();

            if (!$hasApprovedEnrollment) {
                return redirect()->route('schools.guest.dashboard', ['school' => $request->route('school')])
                    ->with('info', 'Your enrollment is still being processed. Please wait for approval before accessing this feature.');
            }
            
            // Side-door promotion removed. Role promotion is handled
            // exclusively by the enrollment approval path.
        }

        return $next($request);
    }
}
