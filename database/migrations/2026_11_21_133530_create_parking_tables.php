<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('slot_number', 30);
            $table->string('area', 60)->nullable();
            $table->string('slot_type', 20)->default('car');
            $table->boolean('is_covered')->default(true);
            $table->string('status', 20)->default('available');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['society_id', 'slot_number']);
        });

        Schema::create('parking_allotments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('parking_slot_id')->constrained('parking_slots')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('monthly_charge', 10, 2)->default(0);
            $table->string('status', 20)->default('active');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['parking_slot_id', 'status']);
            $table->index(['society_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_allotments');
        Schema::dropIfExists('parking_slots');
    }
};
