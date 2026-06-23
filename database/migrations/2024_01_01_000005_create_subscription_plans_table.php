<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('plan_type')->default('standard');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->integer('max_units')->default(100);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'half_yearly', 'yearly'])->default('yearly');
            $table->enum('plan_duration', ['1_month', '3_months', '6_months', '1_year', '2_years'])->default('1_year');
            $table->integer('trial_period_days')->default(0);
            $table->string('badge')->nullable();
            $table->string('color', 7)->default('#2563EB');
            $table->integer('priority')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plan_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->string('module_name');
            $table->string('module_key');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_number')->unique();
            $table->foreignId('society_id')->constrained('societies')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->string('building_name')->nullable();
            $table->decimal('monthly_cost_per_flat', 12, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('additional_free_days')->default(0);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'half_yearly', 'yearly'])->default('yearly');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'expiring_soon', 'expired', 'cancelled'])->default('active');
            $table->string('payment_method')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_proof')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plan_modules');
        Schema::dropIfExists('subscription_plans');
    }
};
