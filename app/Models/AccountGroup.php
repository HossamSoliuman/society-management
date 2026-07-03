<?php

namespace App\Models;

use Database\Factories\AccountGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountGroup extends Model
{
    /** @use HasFactory<AccountGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'society_id', 'name', 'color', 'icon',
    ];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'group_id');
    }
}
