<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use App\Models\School;
use App\Models\Course;
use App\Models\Instructor;

/**
 * Cache Helper for frequently accessed static/semi-static data
 * Reduces database load by caching common queries
 */
class CacheHelper
{
    /**
     * Default cache duration in seconds (15 minutes)
     */
    const CACHE_DURATION = 900;

    /**
     * Get active courses for a school (cached)
     * Use for dropdowns and listings that don't change often
     */
    public static function getActiveCourses(int $schoolId): \Illuminate\Support\Collection
    {
        return Cache::remember(
            "school.{$schoolId}.courses.active",
            self::CACHE_DURATION,
            function () use ($schoolId) {
                return Course::where('school_id', $schoolId)
                    ->where('status', 'active')
                    ->select('id', 'name', 'duration', 'price', 'description')
                    ->orderBy('name')
                    ->get();
            }
        );
    }

    /**
     * Get active instructors for a school (cached)
     */
    public static function getActiveInstructors(int $schoolId): \Illuminate\Support\Collection
    {
        return Cache::remember(
            "school.{$schoolId}.instructors.active",
            self::CACHE_DURATION,
            function () use ($schoolId) {
                return Instructor::where('school_id', $schoolId)
                    ->where('status', 'active')
                    ->select('id', 'name', 'email', 'availability', 'license_number')
                    ->orderBy('name')
                    ->get();
            }
        );
    }

    /**
     * Get school settings (cached heavily since rarely changes)
     */
    public static function getSchoolSettings(int $schoolId)
    {
        return Cache::remember(
            "school.{$schoolId}.settings",
            3600, // 1 hour
            function () use ($schoolId) {
                return School::with('schoolSetting')->find($schoolId);
            }
        );
    }

    /**
     * Clear school-specific caches
     * Call this when courses, instructors, or settings are updated
     */
    public static function clearSchoolCache(int $schoolId): void
    {
        Cache::forget("school.{$schoolId}.courses.active");
        Cache::forget("school.{$schoolId}.instructors.active");
        Cache::forget("school.{$schoolId}.settings");
    }

    /**
     * Get dashboard statistics (cached for 5 minutes)
     */
    public static function getDashboardStats(int $schoolId, string $type): array
    {
        return Cache::remember(
            "school.{$schoolId}.dashboard.{$type}",
            300, // 5 minutes
            function () use ($schoolId, $type) {
                // Return empty for now - implement specific stats as needed
                return [];
            }
        );
    }

    /**
     * Clear dashboard cache
     */
    public static function clearDashboardCache(int $schoolId): void
    {
        Cache::forget("school.{$schoolId}.dashboard.admin");
        Cache::forget("school.{$schoolId}.dashboard.instructor");
        Cache::forget("school.{$schoolId}.dashboard.student");
    }
}
