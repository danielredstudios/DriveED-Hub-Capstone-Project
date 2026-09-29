<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\RegistrationRequest;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function showRegistrationForm(School $school)
    {
        return view($school->resolveView('registration'), [
            'school' => $school,
        ]);
    }

    public function submitRegistration(Request $request, School $school)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
            ],
            'contact' => ['required', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'is_new_driver' => 'required|boolean',
        ]);

        // Check if email already exists in students, instructors, or pending requests
        $emailExists = \App\Models\Student::where('school_id', $school->id)
            ->where('email', $request->email)
            ->exists();

        if (!$emailExists) {
            $emailExists = \App\Models\Instructor::where('school_id', $school->id)
                ->where('email', $request->email)
                ->exists();
        }

        if (!$emailExists) {
            $emailExists = RegistrationRequest::where('school_id', $school->id)
                ->where('email', $request->email)
                ->where('status', 'pending')
                ->exists();
        }

        if ($emailExists) {
            return back()->withErrors(['email' => 'This email is already registered or has a pending request.'])->withInput();
        }

        RegistrationRequest::create([
            'school_id' => $school->id,
            'first_name' => trim($request->first_name),
            'last_name' => trim($request->last_name),
            'email' => trim($request->email),
            'contact' => trim($request->contact),
            'is_new_driver' => $request->is_new_driver,
            'status' => 'pending',
        ]);

        return redirect()->route('schools.registration.form', $school)
            ->with('success', 'Your registration request has been submitted successfully! We will review your request and contact you soon.');
    }
}
