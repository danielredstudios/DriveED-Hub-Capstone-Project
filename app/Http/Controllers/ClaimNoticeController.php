<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClaimNoticeController extends Controller
{
    public function store(Request $request, School $school, Student $student)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && (int) $admin->school_id === (int) $school->id, 403);
        abort_unless((int) $student->school_id === (int) $school->id && $student->isStudent(), 404);
        abort_unless($admin->canAccessBranch($student->branch_id), 403);

        $data = $request->validate([
            'kind' => 'required|in:certificate,claim_stub',
            'message' => 'required|string|max:1000',
        ]);
        $title = $data['kind'] === 'certificate' ? 'Your certificate is ready for pickup' : 'Your claim stub is ready for pickup';
        Notification::send($student, 'certificate_claim_ready', $title, $data['message'], 'success',
            route('schools.student.dashboard', $school));

        return back()->with('success', 'Pickup notice sent! The student can now see it on their dashboard and alerts.');
    }
}
