<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();
            $table->string('receipt_number')->unique();

            // Links to Phase-1 members/units and the 2B bill (all nullable) + denormalized display columns.
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('maintenance_bill_id')->nullable()->constrained('maintenance_bills')->nullOnDelete();
            $table->string('member_name')->nullable();
            $table->string('member_mobile')->nullable();
            $table->string('member_email')->nullable();
            $table->string('flat_number')->nullable();
            $table->string('unit_label')->nullable();
            $table->string('member_code')->nullable();
            $table->string('unit_type')->nullable();

            $table->string('bill_type')->default('Maintenance');
            $table->string('bill_period')->nullable();
            $table->date('due_date')->nullable();
            $table->dateTime('receipt_date');

            $table->decimal('total_due', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('fine_penalty', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);

            $table->enum('payment_mode', ['cash', 'upi', 'card', 'net_banking', 'cheque', 'other'])->nullable();
            $table->string('reference_no')->nullable();
            $table->string('transaction_utr')->nullable();
            $table->string('collected_by')->nullable();

            $table->enum('status', ['paid', 'partial', 'pending', 'overdue', 'refunded'])->default('pending');
            $table->boolean('is_online')->default(false);
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_payments');
    }
};
