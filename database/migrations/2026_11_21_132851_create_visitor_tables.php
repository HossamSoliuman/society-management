<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_pre_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('visitor_name');
            $table->string('mobile', 30)->nullable();
            $table->string('purpose', 30)->default('guest');
            $table->unsignedSmallInteger('people_count')->default(1);
            $table->string('vehicle_no', 30)->nullable();
            $table->dateTime('expected_at');
            $table->dateTime('valid_until');
            $table->string('pass_code', 10);
            $table->string('status', 20)->default('pending');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['society_id', 'pass_code']);
            $table->index(['society_id', 'status', 'expected_at']);
        });

        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('pre_approval_id')->nullable()->constrained('visitor_pre_approvals')->nullOnDelete();
            $table->string('name');
            $table->string('mobile', 30)->nullable();
            $table->string('purpose', 30)->default('guest');
            $table->string('company')->nullable();
            $table->unsignedSmallInteger('people_count')->default(1);
            $table->string('vehicle_no', 30)->nullable();
            $table->dateTime('check_in_at');
            $table->dateTime('check_out_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['society_id', 'check_in_at']);
            $table->index(['society_id', 'check_out_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
        Schema::dropIfExists('visitor_pre_approvals');
    }
};
