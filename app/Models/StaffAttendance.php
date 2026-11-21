<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    use BelongsToSociety;

    protected $table = 'staff_attendance';

    /**
     * Status => [label, short code, badge class].
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    public const STATUSES = [
        'present' => ['Present', 'P', 'badge-success'],
        'absent' => ['Absent', 'A', 'badge-danger'],
        'half_day' => ['Half Day', 'H', 'badge-warning'],
        'leave' => ['On Leave', 'L', 'badge-info'],
    ];

    protected $fillable = ['society_id', 'staff_id', 'date', 'status', 'check_in', 'check_out', 'notes', 'marked_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class)->withTrashed();
    }
}
