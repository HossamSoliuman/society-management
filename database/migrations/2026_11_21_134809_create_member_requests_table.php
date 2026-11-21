<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('societies', function (Blueprint $table) {
            $table->string('join_code', 20)->nullable()->unique()->after('society_code');
        });

        Schema::create('member_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('request_type', 20)->default('join');
            $table->string('member_type', 20)->default('owner');
            $table->string('name');
            $table->string('mobile', 30);
            $table->string('email')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('unit_number', 50);
            $table->date('move_in_date')->nullable();
            $table->string('document_path')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_note')->nullable();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['society_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_requests');

        Schema::table('societies', function (Blueprint $table) {
            $table->dropUnique(['join_code']);
            $table->dropColumn('join_code');
        });
    }
};
