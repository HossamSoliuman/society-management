<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_path')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->integer('records_count')->default(0);
            $table->enum('status', ['pending', 'validated', 'imported', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_uploads');
    }
};
