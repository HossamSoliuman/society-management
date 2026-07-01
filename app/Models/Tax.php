<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'name', 'tax_type', 'rate', 'apply_on',
        'slab_from', 'slab_to', 'description', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'slab_from' => 'decimal:2',
            'slab_to' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function taxTypeLabel(): string
    {
        return ucfirst($this->tax_type);
    }
}
