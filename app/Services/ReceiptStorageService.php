<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReceiptStorageService
{
    /**
     * Store a forensic receipt with a non-enumerable filename.
     */
    public function store(UploadedFile $file, $schoolId)
    {
        $hash = Str::random(32);
        $timestamp = now()->timestamp;
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $filename = "{$hash}_{$timestamp}.{$extension}";
        
        // Store on private disk
        $path = $file->storeAs("receipts/{$schoolId}", $filename, 'local');
        
        return $path;
    }

    /**
     * Generate a temporary signed/secure URL for viewing the receipt.
     */
    public function getUrl($path, $school = null)
    {
        if (!$path) return null;

        // Normalize path
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        
        // Resolve school slug reliably
        $slug = null;
        if ($school instanceof School) {
            $slug = $school->slug;
        } elseif (is_numeric($school)) {
            $slug = School::find($school)?->slug;
        } elseif (is_string($school) && !empty($school) && $school !== 'default') {
            $slug = $school;
        } elseif (request()->route('school') instanceof School) {
            $slug = request()->route('school')->slug;
        } elseif (is_string(request()->route('school')) && !empty(request()->route('school')) && request()->route('school') !== 'default') {
            $slug = request()->route('school');
        } elseif (Auth::guard('admin')->user()?->school) {
            $slug = Auth::guard('admin')->user()->school->slug;
        } elseif (Auth::guard('student')->user()?->school) {
            $slug = Auth::guard('student')->user()->school->slug;
        } elseif ($firstSchool = School::first()) {
            $slug = $firstSchool->slug;
        }

        if (empty($slug)) {
            $slug = 'default';
        }

        return route('schools.guest.storage.receipt', [
            'school' => $slug, 
            'path' => $path
        ]);
    }
}
