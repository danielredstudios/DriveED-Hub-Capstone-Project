<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SystemLog extends Model
{
    use HasFactory;

    private const ALLOWED_CATEGORIES = [
        'database',
        'validation',
        'authentication',
        'authorization',
        'file_upload',
        'email',
        'payment',
        'api',
        'system',
        'booking',
        'schedule',
        'user_management',
        'course',
        'other',
    ];

    private const CATEGORY_ALIASES = [
        'admin' => 'user_management',
        'enrollment' => 'user_management',
    ];

    protected $fillable = [
        'school_id',
        'user_id',
        'user_type',
        'level',
        'category',
        'action',
        'message',
        'exception_class',
        'stack_trace',
        'context',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'notified_admin',
        'notified_system_admin',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'context' => 'array',
        'notified_admin' => 'boolean',
        'notified_system_admin' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class , 'resolved_by');
    }

    /**
     * Scopes
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    public function scopeCritical($query)
    {
        return $query->whereIn('level', ['emergency', 'alert', 'critical', 'error']);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Static helper methods for logging
     */
    public static function logError(
        string $message,
        string $category = 'other',
        ?\Throwable $exception = null,
        array $context = [],
        ?int $schoolId = null,
        string $action = null
        ): self
    {
        return self::createLog('error', $message, $category, $exception, $context, $schoolId, $action);
    }

    public static function logCritical(
        string $message,
        string $category = 'other',
        ?\Throwable $exception = null,
        array $context = [],
        ?int $schoolId = null,
        string $action = null
        ): self
    {
        return self::createLog('critical', $message, $category, $exception, $context, $schoolId, $action);
    }

    public static function logWarning(
        string $message,
        string $category = 'other',
        array $context = [],
        ?int $schoolId = null,
        string $action = null
        ): self
    {
        return self::createLog('warning', $message, $category, null, $context, $schoolId, $action);
    }

    public static function logInfo(
        string $message,
        string $category = 'other',
        array $context = [],
        ?int $schoolId = null,
        string $action = null
        ): self
    {
        return self::createLog('info', $message, $category, null, $context, $schoolId, $action);
    }

    private static function createLog(
        string $level,
        string $message,
        string $category,
        ?\Throwable $exception,
        array $context,
        ?int $schoolId,
        ?string $action
        ): self
    {
        $guardName = self::getCurrentGuardName();
        $user = Auth::guard($guardName)->user();

        // Note: user_id FK references 'users' table, but our app uses separate tables:
        // - admins table (system_admin, school_admin)
        // - instructors table
        // - students table (students, guests)
        // So we always set user_id to null and store actor info in context instead
        if ($user) {
            $context['actor_id'] = $user->id;
            $context['actor_type'] = $guardName;
            $context['actor_email'] = $user->email ?? null;
            $context['actor_name'] = $user->name ?? null;
        }

        $normalizedCategory = self::normalizeCategory($category, $context);

        $log = self::create([
            'school_id' => $schoolId ?? ($user->school_id ?? null),
            'user_id' => null, // Always null - our users aren't in the 'users' table
            'user_type' => $guardName ?? 'system',
            'level' => $level,
            'category' => $normalizedCategory,
            'action' => $action,
            'message' => self::maskPii($message),
            'exception_class' => $exception ? get_class($exception) : null,
            'stack_trace' => $exception ? self::redactStackTrace($exception->getTraceAsString()) : null,
            'context' => self::maskContextPii($context),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
        ]);

        // Notify if critical
        if (in_array($level, ['emergency', 'alert', 'critical', 'error'])) {
            $log->notifyAdmins();
        }

        return $log;
    }

    private static function normalizeCategory(string $category, array &$context): string
    {
        $normalized = strtolower(trim($category));

        if (isset(self::CATEGORY_ALIASES[$normalized])) {
            $context['log_category_original'] = $category;
            return self::CATEGORY_ALIASES[$normalized];
        }

        if (in_array($normalized, self::ALLOWED_CATEGORIES, true)) {
            return $normalized;
        }

        $context['log_category_original'] = $category;
        return 'other';
    }

    private static function getCurrentGuardName(): ?string
    {
        foreach (['admin', 'instructor', 'student'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }
        return null;
    }

    /**
     * Mask PII (Personally Identifiable Information) in a string
     */
    private static function maskPii(string $string): string
    {
        // Mask emails
        $string = preg_replace('/([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', '***@$2', $string);
        // Mask phone numbers (basic pattern)
        $string = preg_replace('/(\+?\d{1,3}[\s-]?)?\(?\d{3}\)?[\s-]?\d{3}[\s-]?\d{4}/', '***-***-****', $string);
        return $string;
    }

    /**
     * Mask PII in context array
     */
    private static function maskContextPii(array $context): array
    {
        array_walk_recursive($context, function (&$item) {
            if (is_string($item)) {
                $item = self::maskPii($item);
            }
        });
        return $context;
    }

    /**
     * Redact and truncate stack traces to prevent sensitive path exposure
     */
    private static function redactStackTrace(string $trace): string
    {
        // Redact absolute paths to the project root
        $root = base_path();
        $trace = str_replace($root, '[PROJECT_ROOT]', $trace);

        // Limit length to prevent massive log entries
        if (strlen($trace) > 5000) {
            $trace = substr($trace, 0, 5000) . "\n... [TRUNCATED]";
        }

        return $trace;
    }

    /**
     * Mark as resolved
     */
    public function resolve(string $notes = null): bool
    {
        $guardName = self::getCurrentGuardName();
        $user = Auth::guard($guardName)->user();

        // Note: resolved_by FK references 'users' table, but our users are in separate tables
        // So we always set resolved_by to null and include resolver info in resolution_notes
        $resolutionNotes = $notes;
        if ($user) {
            $resolverInfo = "[Resolved by {$guardName}: {$user->name} (ID: {$user->id})]";
            $resolutionNotes = $resolverInfo . ($notes ? "\n\n" . $notes : "");
        }

        return $this->update([
            'resolved_at' => now(),
            'resolved_by' => null, // Always null - our users aren't in the 'users' table
            'resolution_notes' => $resolutionNotes,
        ]);
    }

    /**
     * Notify admins about this error
     */
    public function notifyAdmins(): void
    {
        // TODO: Implement email/notification system
        // For now, just mark as notified
        $this->update([
            'notified_admin' => true,
            'notified_system_admin' => true,
        ]);
    }

    /**
     * Get formatted error details
     */
    public function getFormattedDetails(): string
    {
        $details = [];
        $details[] = "Level: " . strtoupper($this->level);
        $details[] = "Category: " . ucfirst($this->category);

        if ($this->action) {
            $details[] = "Action: " . $this->action;
        }

        if ($this->school) {
            $details[] = "School: " . $this->school->name;
        }

        if ($this->user) {
            $details[] = "User: " . $this->user->name . " ({$this->user_type})";
        }

        $details[] = "Message: " . $this->message;

        if ($this->exception_class) {
            $details[] = "Exception: " . $this->exception_class;
        }

        $details[] = "Time: " . $this->created_at->format('Y-m-d H:i:s');
        $details[] = "URL: " . $this->url;

        return implode("\n", $details);
    }
}
