<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentStatusLog extends Model
{
    protected $fillable = [
        'payment_id',
        'school_id',
        'actor_id',
        'action_type',
        'from_status',
        'to_status',
        'reason_code',
        'reason_note',
        'ip_address',
        'user_agent',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function actor()
    {
        // Polymorphic or simple User ID? Since Student and Admin are separate tables,
        // we might need a polymorphic relationship if we want to point to the actor.
        // For now, we'll keep the ID as an unsignedBigInteger and actor_type if needed.
        return $this->belongsTo(User::class, 'actor_id');
    }
}
