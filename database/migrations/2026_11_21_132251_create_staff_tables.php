<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('service_vendors')->nullOnDelete();
            $table->string('employee_code', 30)->nullable();
            $table->string('name');
            $table->string('role', 50);
            $table->string('employment_type', 20)->default('society');
            $table->string('shift', 20)->default('general');
            $table->string('mobile', 30);
            $table->string('email')->nullable();
            $table->string('emergency_contact', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('id_proof_type', 50)->nullable();
            $table->string('id_proof_number', 100)->nullable();
            $table->date('joining_date')->nullable();
            $table->decimal('monthly_salary', 12, 2)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
        });

        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 20);
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['staff_id', 'date']);
            $table->index(['society_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
        Schema::dropIfExists('staff');
    }
};
