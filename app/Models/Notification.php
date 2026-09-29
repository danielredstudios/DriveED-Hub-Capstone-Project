<?php

namespace App\Models;

use App\Traits\HasSchoolScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasSchoolScope;
    use HasFactory;

    protected $fillable = [
        'school_id',
        'notifiable_type',
        'notifiable_id',
        'type',
        'title',
        'message',
        'icon',
        'action_url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the school this notification belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the notifiable entity (Student, Admin, Instructor).
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if the notification has been read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Scope: unread notifications only.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: for a specific notifiable.
     */
    public function scopeForNotifiable($query, $notifiable)
    {
        return $query->where('notifiable_type', get_class($notifiable))
                     ->where('notifiable_id', $notifiable->id);
    }

    /**
     * Create a notification for a user.
     */
    public static function send($notifiable, string $type, string $title, string $message, string $icon = 'info', ?string $actionUrl = null): self
    {
        return static::create([
            'school_id' => $notifiable->school_id,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * Get the emoji icon for display.
     */
    public function getIconEmoji(): string
    {
        return match($this->icon) {
            'success' => '✅',
            'warning' => '⚠️',
            'error' => '❌',
            'enrollment' => '📋',
            'session' => '🚗',
            'payment' => '💳',
            'license' => '🪪',
            default => 'ℹ️',
        };
    }

    /**
     * Get human-readable time ago.
     */
    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }
}
