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
        Schema::create('accounting_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_no');
            $table->dateTime('date');
            $table->string('payee');
            $table->string('purpose')->nullable();
            $table->string('mode');
            $table->decimal('amount', 14, 2)->default(0);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('reference_no')->nullable();
            $table->enum('status', ['completed', 'pending'])->default('completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_payments');
    }
};
