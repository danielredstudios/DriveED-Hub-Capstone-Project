<?php

/**
 * TEST MIGRATION - Add new fields to courses table
 * 
 * This migration adds:
 * - license_type: 'non-professional' | 'professional'
 * - theoretical_hours_required: decimal
 * - practical_hours_required: decimal
 * 
 * Run this after testing the form component
 * 
 * Command: php artisan migrate
 * Rollback: php artisan migrate:rollback
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Add license type field
            $table->enum('license_type', ['non-professional', 'professional'])
                  ->default('non-professional')
                  ->after('type')
                  ->comment('License category: Non-Pro (personal use) or Pro (for-hire)');
            
            // Add phase-based hour requirements
            $table->decimal('theoretical_hours_required', 5, 1)
                  ->default(8.0)
                  ->after('duration_hours')
                  ->comment('Required classroom/theory hours');
            
            $table->decimal('practical_hours_required', 5, 1)
                  ->default(20.0)
                  ->after('theoretical_hours_required')
                  ->comment('Required behind-the-wheel hours');
            
            // Add index for filtering by license type
            $table->index('license_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['license_type']);
            $table->dropColumn([
                'license_type',
                'theoretical_hours_required',
                'practical_hours_required'
            ]);
        });
    }
};
