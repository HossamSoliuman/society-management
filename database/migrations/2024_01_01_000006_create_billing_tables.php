<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('payment_modes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('society_id')->constrained('societies')->onDelete('cascade');
            $table->string('member_name');
            $table->string('flat_number');
            $table->string('building_name')->nullable();
            $table->string('invoice_type')->default('Maintenance');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('outstanding_amount', 12, 2)->default(0);
            $table->enum('status', ['paid', 'pending', 'overdue', 'partially_paid', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('society_id')->constrained('societies');
            $table->string('member_name');
            $table->string('flat_number');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->date('payment_date');
            $table->enum('status', ['success', 'pending', 'failed'])->default('success');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique();
            $table->foreignId('payment_id')->constrained('payments');
            $table->foreignId('society_id')->constrained('societies');
            $table->string('member_name');
            $table->string('flat_number');
            $table->decimal('amount', 12, 2);
            $table->string('refund_method');
            $table->date('refund_date');
            $table->enum('status', ['completed', 'pending', 'failed'])->default('pending');
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payment_modes');
        Schema::dropIfExists('unit_types');
    }
};
