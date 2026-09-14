<?php

namespace App\Models;

use App\Models\Concerns\TargetsAudience;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes, TargetsAudience;

    protected $fillable = [
        'society_id', 'title', 'message', 'recipient_type', 'target_roles', 'estimated_recipients', 'delivered_count',
        'priority', 'category', 'delivery_channel', 'send_type',
        'scheduled_at', 'sent_at', 'status', 'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'target_roles' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Notification channels implied by the delivery_channel setting.
     *
     * @return array<int, string>
     */
    public function channels(): array
    {
        return match ($this->delivery_channel) {
            'in_app' => ['database'],
            'email' => ['mail'],
            'sms' => ['sms'],
            default => ['database', 'mail', 'sms'],
        };
    }
}
