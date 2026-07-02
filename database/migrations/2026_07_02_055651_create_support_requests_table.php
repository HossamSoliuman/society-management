<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();
            $table->string('request_id')->unique();
            $table->string('subject');
            $table->string('category');
            $table->enum('raised_by_type', ['member', 'staff_admin'])->default('member');
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('raised_by_name');
            $table->string('flat_no')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('preferred_contact')->nullable();
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->text('description');
            $table->string('location')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('raised_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_requests');
    }
};
