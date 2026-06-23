<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrefixSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'module', 'prefix', 'starting_number', 'current_number', 'padding', 'status',
    ];
}
