<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocietyType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'status'];

    public function societies()
    {
        return $this->hasMany(Society::class);
    }
}
