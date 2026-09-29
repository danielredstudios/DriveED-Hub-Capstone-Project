<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Identity and Linkage
            $table->string('guest_identity_token')->after('branch_id')->nullable()->index();
            $table->unsignedBigInteger('payer_user_id')->after('guest_identity_token')->nullable()->index();
            
            // Forensic Audit & Normalization
            $table->string('normalized_reference')->after('reference')->nullable();
            $table->string('or_number')->after('normalized_reference')->nullable();
            $table->string('normalized_or_number')->after('or_number')->nullable();
            $table->string('proof_of_payment_path')->after('normalized_or_number')->nullable();
            
            // Rejection Audit
            $table->string('rejection_reason_code')->after('status')->nullable();
            $table->text('rejection_reason_note')->after('rejection_reason_code')->nullable();
            
            // Verification/Refund Audit
            $table->unsignedBigInteger('received_by_admin_id')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->unsignedBigInteger('refunded_by_admin_id')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->decimal('refunded_amount', 10, 2)->nullable()->default(0);
            
            // Snapshots
            $table->string('snap_qr_source')->nullable(); // 'branch' or 'school'
            $table->unsignedBigInteger('snap_config_id')->nullable();
            $table->decimal('snap_expected_amount', 10, 2)->nullable();
            $table->string('snap_qr_path')->nullable();
            $table->dateTime('snap_at')->nullable();

            // Unique Indexes (Forever)
            $table->unique(['school_id', 'normalized_reference'], 'payments_gcash_global_unique');
            $table->unique(['school_id', 'branch_id', 'normalized_or_number'], 'payments_onsite_branch_unique');
        });

        // 🟢 DATA BACKFILL: Satisfy XOR for existing records
        $payments = DB::table('payments')->get();
        foreach ($payments as $p) {
            $studentId = null;
            if ($p->booking_id) {
                $studentId = DB::table('bookings')->where('id', $p->booking_id)->value('student_id');
            } elseif ($p->enrollment_request_id) {
                $studentId = DB::table('enrollment_requests')->where('id', $p->enrollment_request_id)->value('learner_id');
            }

            if ($studentId) {
                DB::table('payments')->where('id', $p->id)->update(['payer_user_id' => $studentId]);
            }
        }

        // XOR Trigger Enforcement (Race-safe for MySQL 8)
        if (DB::getDriverName() !== 'sqlite') {
            DB::unprepared("
                CREATE TRIGGER payments_linkage_xor_insert_check BEFORE INSERT ON payments
                FOR EACH ROW
                BEGIN
                    IF (NEW.booking_id IS NOT NULL AND NEW.enrollment_request_id IS NOT NULL) OR (NEW.booking_id IS NULL AND NEW.enrollment_request_id IS NULL) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payment must be linked to exactly one booking or enrollment request.';
                    END IF;
                    IF (NEW.guest_identity_token IS NOT NULL AND NEW.payer_user_id IS NOT NULL) OR (NEW.guest_identity_token IS NULL AND NEW.payer_user_id IS NULL) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payment must have exactly one identity (guest or user).';
                    END IF;
                END;
            ");

            DB::unprepared("
                CREATE TRIGGER payments_linkage_xor_update_check BEFORE UPDATE ON payments
                FOR EACH ROW
                BEGIN
                    IF (NEW.booking_id IS NOT NULL AND NEW.enrollment_request_id IS NOT NULL) OR (NEW.booking_id IS NULL AND NEW.enrollment_request_id IS NULL) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payment must be linked to exactly one booking or enrollment request.';
                    END IF;
                    IF (NEW.guest_identity_token IS NOT NULL AND NEW.payer_user_id IS NOT NULL) OR (NEW.guest_identity_token IS NULL AND NEW.payer_user_id IS NULL) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payment must have exactly one identity (guest or user).';
                    END IF;
                END;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            DB::unprepared('DROP TRIGGER IF EXISTS payments_linkage_xor_insert_check');
            DB::unprepared('DROP TRIGGER IF EXISTS payments_linkage_xor_update_check');

            $table->dropUnique('payments_gcash_global_unique');
            $table->dropUnique('payments_onsite_branch_unique');
            
            $table->dropColumn([
                'guest_identity_token', 'payer_user_id', 
                'normalized_reference', 'or_number', 'normalized_or_number',
                'proof_of_payment_path', 'rejection_reason_code', 'rejection_reason_note',
                'received_by_admin_id', 'received_at', 'refunded_by_admin_id', 'refunded_at',
                'snap_qr_source', 'snap_config_id', 'snap_expected_amount', 'snap_qr_path', 'snap_at'
            ]);
        });
    }
};
