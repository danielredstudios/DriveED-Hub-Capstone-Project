<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    /**
     * Get revenue summary for a school/branch within a date range.
     */
    public function getRevenueSummary(School $school, ?int $branchId = null, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $query = Payment::where('school_id', $school->id);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($startDate) {
            $query->where('received_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('received_at', '<=', $endDate);
        }

        $stats = (clone $query)
            ->select(
                DB::raw("SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as gross_revenue"),
                DB::raw("SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END) as total_refunded"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount"),
                DB::raw("COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count"),
                DB::raw("COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count"),
                DB::raw("COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count"),
                DB::raw("COUNT(CASE WHEN status = 'refunded' THEN 1 END) as refunded_count")
            )
            ->first();

        return [
            'gross_revenue' => (float)($stats->gross_revenue ?? 0),
            'total_refunded' => (float)($stats->total_refunded ?? 0),
            'pending_amount' => (float)($stats->pending_amount ?? 0),
            'net_revenue' => (float)($stats->gross_revenue ?? 0) - (float)($stats->total_refunded ?? 0),
            'approved_count' => (int)($stats->approved_count ?? 0),
            'pending_count' => (int)($stats->pending_count ?? 0),
            'rejected_count' => (int)($stats->rejected_count ?? 0),
            'refunded_count' => (int)($stats->refunded_count ?? 0),
        ];
    }

    /**
     * Get collection breakdown by method.
     */
    public function getCollectionByMethod(School $school, ?int $branchId = null)
    {
        return Payment::where('school_id', $school->id)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', '=', 'approved')
            ->select('method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('method')
            ->get();
    }
}
