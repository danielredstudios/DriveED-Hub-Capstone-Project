<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
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
        $extension = $file->getClientOriginalExtension();
        $filename = "{$hash}_{$timestamp}.{$extension}";
        
        // Store on private disk
        $path = $file->storeAs("receipts/{$schoolId}", $filename, 'local');
        
        return $path;
    }

    /**
     * Generate a temporary signed URL for viewing the receipt.
     */
    public function getUrl($path)
    {
        if (!$path) return null;
        
        // Use our secure streaming route instead of cloud-only temporaryUrl
        return route('schools.guest.storage.receipt', [
            'school' => request()->route('school') ?? 'default', 
            'path' => $path
        ]);
    }
}
