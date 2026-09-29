<?php

namespace App\Http\Controllers;

use App\Models\GCashSetting;
use App\Models\School;
use App\Models\Student;
use App\Models\Admin;
use App\Models\EnrollmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageController extends Controller
{
    /**
     * Serve a student license file securely
     */
    public function streamLicense(School $school, Student $student)
    {
        // 1. Authorization: Only the student themselves or an admin from the same school can view
        $isOwner = Auth::guard('student')->check() && Auth::guard('student')->id() === $student->id;
        $admin = Auth::guard('admin')->user();
        $isAdmin = $admin instanceof Admin && (int) $admin->school_id === (int) $school->id;
        
        abort_unless($isOwner || $isAdmin, 403, 'Unauthorized access to this document.');

        // 2. School Boundary
        abort_if((int)$student->school_id !== (int)$school->id, 404);

        if (!$student->student_license_path || !Storage::disk('local')->exists($student->student_license_path)) {
            abort(404, 'License file not found.');
        }

        return $this->fileResponseFromDisk('local', $student->student_license_path);
    }

    /**
     * Serve an enrollment credential file securely
     */
    public function streamCredential(School $school, EnrollmentRequest $enrollment)
    {
        // 1. Authorization: Student owner or admin
        $isOwner = Auth::guard('student')->check() && (int)Auth::guard('student')->id() === (int)$enrollment->learner_id;
        $admin = Auth::guard('admin')->user();
        $isAdmin = $admin instanceof Admin && (int) $admin->school_id === (int) $school->id;
        
        abort_unless($isOwner || $isAdmin, 403, 'Unauthorized access to this document.');

        // 2. School Boundary
        abort_if((int)$enrollment->school_id !== (int)$school->id, 404);

        if (!$enrollment->credentials_file_path || !Storage::disk('local')->exists($enrollment->credentials_file_path)) {
            abort(404, 'Credential file not found.');
        }

        return $this->fileResponseFromDisk('local', $enrollment->credentials_file_path);
    }

    /**
     * Serve a GCash payment image securely.
     */
    public function streamGcashQr(School $school, GCashSetting $gcashSetting)
    {
        $student = Auth::guard('student')->user();
        $admin = Auth::guard('admin')->user();

        $isStudent = Auth::guard('student')->check()
            && $student instanceof Student
            && (int) $student->school_id === (int) $school->id;
        $isAdmin = Auth::guard('admin')->check()
            && $admin instanceof Admin
            && (int) $admin->school_id === (int) $school->id;

        abort_unless($isStudent || $isAdmin, 403, 'Unauthorized access to this resource.');
        abort_if((int) $gcashSetting->school_id !== (int) $school->id, 404);
        abort_unless($gcashSetting->is_active, 404, 'GCash setting is not active.');

        $disk = Storage::disk('public');
        if (!$gcashSetting->qr_path || !$disk->exists($gcashSetting->qr_path)) {
            abort(404, 'GCash image not found.');
        }

        return $this->fileResponseFromDisk('public', $gcashSetting->qr_path);
    }

    /**
     * Serve a payment receipt image securely.
     */
    public function streamReceipt(Request $request, School $school)
    {
        $path = $request->query('path');
        abort_unless((bool)$path, 404);

        // Authorization: Must be logged in (student or admin)
        abort_unless(Auth::guard('student')->check() || Auth::guard('admin')->check(), 403);

        // Security check: Only allow known storage directories
        $allowedPrefixes = ['receipts/', 'screenshots/payments/', 'proof_of_payment/'];
        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $isAllowed = true;
                break;
            }
        }
        abort_unless($isAllowed, 403, 'Invalid receipt path.');

        // Try both local and public disks for deployment robustness
        foreach (['local', 'public'] as $diskName) {
            if (Storage::disk($diskName)->exists($path)) {
                return $this->fileResponseFromDisk($diskName, $path);
            }
        }

        abort(404, 'Receipt file not found.');
    }

    /**
     * Serve a vehicle image securely.
     */
    public function streamVehicleImage(School $school, \App\Models\VehicleImage $image)
    {
        // Authorization: Admin or Student from same school
        $admin = Auth::guard('admin')->user();
        $student = Auth::guard('student')->user();
        
        $isAuthorized = ($admin && (int)$admin->school_id === (int)$school->id) ||
                        ($student && (int)$student->school_id === (int)$school->id);
        
        abort_unless($isAuthorized, 403);
        abort_if((int)$image->school_id !== (int)$school->id, 404);

        if (!$image->image_path || !Storage::disk('local')->exists($image->image_path)) {
            abort(404, 'Vehicle image not found.');
        }

        return $this->fileResponseFromDisk('local', $image->image_path);
    }

    private function fileResponseFromDisk(string $diskName, string $path)
    {
        $disk = Storage::disk($diskName);
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path));
    }
}
