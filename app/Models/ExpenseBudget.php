<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Yearly budget per expense category (category null = overall society budget).
 */
class ExpenseBudget extends Model
{
    use BelongsToSociety;

    protected $fillable = ['society_id', 'expense_category_id', 'year', 'amount', 'notes'];

    protected $casts = [
        'year' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }
}
