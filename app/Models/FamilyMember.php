<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\FamilyMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyMember extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<FamilyMemberFactory> */
    use HasFactory;

    public const RELATIONS = ['Spouse', 'Son', 'Daughter', 'Father', 'Mother', 'Brother', 'Sister', 'Other'];

    protected $fillable = [
        'society_id', 'member_id', 'name', 'relation', 'gender', 'date_of_birth', 'mobile', 'email',
        'occupation', 'is_resident', 'id_proof_type', 'id_proof_number', 'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_resident' => 'boolean',
    ];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
