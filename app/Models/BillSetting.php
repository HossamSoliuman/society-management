<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'due_date_days' => 'integer',
            'grace_period_days' => 'integer',
            'allow_zero_amount_bills' => 'boolean',
            'include_sinking_fund' => 'boolean',
            'include_reserve_fund' => 'boolean',
            'adjust_advance_amount' => 'boolean',
            'minimum_bill_amount' => 'decimal:2',
            'include_previous_dues' => 'boolean',
            'allow_partial_payments' => 'boolean',
            'auto_email_bill' => 'boolean',
            'auto_sms_bill' => 'boolean',
            'show_society_details' => 'boolean',
            'show_member_details' => 'boolean',
            'show_flat_details' => 'boolean',
            'show_bill_summary' => 'boolean',
            'show_previous_balance' => 'boolean',
            'show_payment_history' => 'boolean',
            'show_charge_head_description' => 'boolean',
            'show_notes' => 'boolean',
            'show_payment_qr' => 'boolean',
            'amount_decimal_places' => 'integer',
            'show_logo' => 'boolean',
            'show_address' => 'boolean',
            'show_contact' => 'boolean',
            'show_gstin' => 'boolean',
            'show_thank_you' => 'boolean',
            'show_footer_note' => 'boolean',
            'show_qr' => 'boolean',
            'show_terms' => 'boolean',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }
}
