<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'society_id', 'original_name', 'stored_path', 'uploaded_by',
        'records_count', 'status',
    ];

    protected function casts(): array
    {
        return [
            'records_count' => 'integer',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * Map the upload status to a `.status-badge` state class.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'validated', 'imported' => 'active',
            'pending' => 'pending',
            'failed' => 'overdue',
            default => 'cancelled',
        };
    }
}
