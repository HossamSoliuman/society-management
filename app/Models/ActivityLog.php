<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'society_id', 'user_name', 'user_email', 'action', 'module',
        'description', 'properties', 'subject_type', 'subject_id', 'ip_address', 'user_agent', 'status',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Write an entry attributed to the current request/user.
     *
     * @param  array<string, mixed>  $properties
     */
    public static function record(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        string $status = 'success',
        ?User $user = null,
        ?int $societyId = null,
    ): self {
        $user ??= auth()->user();
        $request = app()->runningInConsole() ? null : request();

        return static::create([
            'user_id' => $user?->id,
            'society_id' => $societyId ?? ($subject instanceof Society ? $subject->id : ($subject?->society_id ?? $user?->society_id)),
            'user_name' => $user?->name ?? ($properties['user_name'] ?? 'System'),
            'user_email' => $user?->email ?? ($properties['user_email'] ?? null),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'properties' => $properties ?: null,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
            'status' => $status,
        ]);
    }
}
