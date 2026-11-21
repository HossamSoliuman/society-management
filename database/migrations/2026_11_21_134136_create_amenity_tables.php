<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->time('open_time')->default('06:00');
            $table->time('close_time')->default('22:00');
            $table->unsignedSmallInteger('max_hours')->default(4);
            $table->decimal('charge_per_hour', 10, 2)->default(0);
            $table->boolean('requires_approval')->default(true);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('amenity_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained('amenities')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('booked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('guests')->default(0);
            $table->string('purpose')->nullable();
            $table->decimal('charge', 10, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note')->nullable();
            $table->foreignId('maintenance_bill_id')->nullable()->constrained('maintenance_bills')->nullOnDelete();
            $table->timestamps();

            $table->index(['amenity_id', 'booking_date', 'status']);
            $table->index(['society_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_bookings');
        Schema::dropIfExists('amenities');
    }
};
