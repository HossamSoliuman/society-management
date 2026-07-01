<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();

            // General
            $table->string('default_bill_type')->default('Monthly Maintenance');
            $table->string('default_bill_cycle')->default('Current Month');
            $table->string('bill_generation_date')->default('1st of Every Month');
            $table->unsignedInteger('due_date_days')->default(15);
            $table->unsignedInteger('grace_period_days')->default(5);
            $table->string('round_off')->default('Round to Nearest Rupee');
            $table->boolean('allow_zero_amount_bills')->default(true);

            // Calculation
            $table->enum('calculation_method', ['flat_based', 'area_based', 'unit_based', 'custom'])->default('flat_based');
            $table->boolean('include_sinking_fund')->default(true);
            $table->boolean('include_reserve_fund')->default(true);
            $table->boolean('adjust_advance_amount')->default(true);
            $table->decimal('minimum_bill_amount', 12, 2)->default(100);
            $table->boolean('include_previous_dues')->default(true);

            // Other
            $table->string('default_payment_mode')->nullable();
            $table->string('default_collection_account')->nullable();
            $table->boolean('allow_partial_payments')->default(true);
            $table->boolean('auto_email_bill')->default(true);
            $table->boolean('auto_sms_bill')->default(false);

            // Display
            $table->boolean('show_society_details')->default(true);
            $table->boolean('show_member_details')->default(true);
            $table->boolean('show_flat_details')->default(true);
            $table->boolean('show_bill_summary')->default(true);
            $table->boolean('show_previous_balance')->default(true);
            $table->boolean('show_payment_history')->default(true);
            $table->boolean('show_charge_head_description')->default(true);
            $table->boolean('show_notes')->default(true);
            $table->boolean('show_payment_qr')->default(true);
            $table->string('currency_format')->default('Indian Rupee (₹)');
            $table->unsignedTinyInteger('amount_decimal_places')->default(2);

            // Bill numbering format
            $table->string('bill_number_prefix')->default('MB');
            $table->string('bill_number_format')->default('YYYYMMDD-XXXX');
            $table->string('next_sequence_number')->default('000157');

            // Notes
            $table->text('terms_conditions')->nullable();
            $table->text('footer_note')->nullable();

            // Bill Design
            $table->enum('template', ['modern', 'classic', 'compact', 'minimal'])->default('modern');
            $table->string('logo_path')->nullable();
            $table->string('society_name')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_address')->default(true);
            $table->boolean('show_contact')->default(true);
            $table->boolean('show_gstin')->default(false);
            $table->string('primary_color')->default('#FF6A00');
            $table->string('secondary_color')->default('#1F2937');
            $table->string('text_color')->default('#374151');
            $table->boolean('show_thank_you')->default(true);
            $table->boolean('show_footer_note')->default(true);
            $table->boolean('show_qr')->default(true);
            $table->boolean('show_terms')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_settings');
    }
};
