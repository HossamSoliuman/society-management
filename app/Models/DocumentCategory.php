<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\DocumentCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    /** @use HasFactory<DocumentCategoryFactory> */
    use BelongsToSociety, HasFactory;

    protected $fillable = [
        'society_id', 'name', 'description', 'icon', 'color', 'status',
    ];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function statusBadgeClass(): string
    {
        return $this->status === 'active' ? 'badge-success' : 'badge-danger';
    }
}
