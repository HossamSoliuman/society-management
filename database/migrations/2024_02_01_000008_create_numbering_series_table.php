<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('numbering_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();
            $table->enum('document_type', ['maintenance_bill', 'receipt', 'credit_note', 'debit_note', 'refund'])->default('maintenance_bill');
            $table->boolean('is_default')->default(false);
            $table->string('prefix')->nullable();
            $table->string('format')->default('YYYY-#####');
            $table->unsignedBigInteger('next_number')->default(1);
            $table->enum('reset_frequency', ['never', 'yearly', 'monthly', 'daily'])->default('yearly');
            $table->string('financial_year')->nullable();
            $table->date('start_date')->nullable();
            $table->string('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering_series');
    }
};
