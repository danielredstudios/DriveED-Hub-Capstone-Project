<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Harden Admins table: move from global unique email to school-scoped unique email
        Schema::table('admins', function (Blueprint $table) {
            // Check if the old unique index exists before dropping
            $exists = collect(DB::select("SHOW INDEXES FROM admins"))->contains('Key_name', 'admins_email_unique');
            if ($exists) {
                $table->dropUnique('admins_email_unique');
            }

            $table->unique(['school_id', 'email'], 'admins_school_email_unique');
        });

        // 2. Harden Password Reset Tokens table: support multi-tenant resets
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            // In many DBs, we can't just 'dropPrimary' easily if it was created as a string primary.
            // We use a raw statement to drop the primary key to ensure compatibility across MySQL variants.
            DB::statement('ALTER TABLE password_reset_tokens DROP PRIMARY KEY');

            // Add school_id and user_type to the primary key/unique constraint
            $table->unique(['email', 'school_id', 'user_type'], 'password_resets_scoped_unique');

            // Re-add index for performance on cleanup
            $table->index(['school_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropUnique('password_resets_scoped_unique');
            $table->dropIndex(['school_id', 'email']);
            $table->primary('email');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropUnique('admins_school_email_unique');
            $table->unique('email', 'admins_email_unique');
        });
    }
};
