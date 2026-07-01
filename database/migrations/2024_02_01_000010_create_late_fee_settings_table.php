<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('late_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();

            // Late fee
            $table->boolean('enable_late_fee')->default(true);
            $table->unsignedInteger('grace_period_days')->default(15);
            $table->enum('late_fee_type', ['percentage', 'flat'])->default('percentage');
            $table->decimal('late_fee_percent', 8, 2)->default(2);
            $table->decimal('late_fee_flat', 12, 2)->nullable();
            $table->decimal('max_late_fee_cap', 12, 2)->nullable();
            $table->string('compounding')->default('Monthly');

            // Interest on arrears
            $table->boolean('enable_interest')->default(true);
            $table->enum('interest_calc_type', ['simple', 'compound'])->default('simple');
            $table->decimal('interest_rate_annual', 8, 2)->default(18);
            $table->unsignedInteger('apply_interest_after_days')->default(30);
            $table->string('interest_calc_on')->default('Outstanding Amount (Including Previous Dues)');
            $table->string('round_off_interest')->default('Round to Nearest Rupee');

            // Exemptions
            $table->json('exempt_members')->nullable();
            $table->json('exempt_charge_heads')->nullable();
            $table->json('exempt_bill_types')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('late_fee_settings');
    }
};
