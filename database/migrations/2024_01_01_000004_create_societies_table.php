<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('society_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('societies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('registration_number')->nullable();
            $table->string('prefix')->unique();
            $table->foreignId('society_type_id')->nullable()->constrained('society_types');
            $table->date('registration_date')->nullable();
            $table->string('pan_number')->nullable();
            $table->integer('flats_count')->default(0);
            $table->integer('shops_count')->default(0);
            $table->integer('offices_count')->default(0);
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('secondary_email')->nullable();
            $table->string('primary_mobile')->nullable();
            $table->string('alternate_mobile')->nullable();
            $table->string('landline')->nullable();
            $table->string('website')->nullable();
            $table->string('chairman_name')->nullable();
            $table->string('chairman_mobile')->nullable();
            $table->string('chairman_email')->nullable();
            $table->string('secretary_name')->nullable();
            $table->string('secretary_mobile')->nullable();
            $table->string('secretary_email')->nullable();
            $table->string('treasurer_name')->nullable();
            $table->string('treasurer_mobile')->nullable();
            $table->string('treasurer_email')->nullable();
            $table->foreignId('subscription_plan_id')->nullable();
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'half_yearly', 'yearly'])->default('yearly');
            $table->integer('grace_period_days')->default(15);
            $table->boolean('auto_renewal')->default(true);
            $table->integer('trial_period_days')->default(0);
            $table->enum('subscription_status', ['active', 'expiring_soon', 'expired', 'cancelled'])->default('active');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('societies');
        Schema::dropIfExists('society_types');
    }
};
