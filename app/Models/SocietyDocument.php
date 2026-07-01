<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocietyDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'society_id', 'title', 'file_path', 'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'date',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }
}
