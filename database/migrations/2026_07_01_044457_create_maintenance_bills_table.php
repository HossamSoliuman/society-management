<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();
            $table->string('bill_number')->unique();

            // Links to Phase-1 members/units (nullable) + denormalized display columns.
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('member_name')->nullable();
            $table->string('flat_number')->nullable();
            $table->string('tower_wing')->nullable();
            $table->string('floor')->nullable();

            $table->string('bill_month');
            $table->date('bill_date');
            $table->date('due_date');
            $table->string('bill_cycle');
            $table->string('billing_type')->default('Monthly Maintenance');

            $table->decimal('sub_total', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('previous_dues', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('collected_amount', 12, 2)->default(0);
            $table->decimal('outstanding_amount', 12, 2)->default(0);

            $table->enum('status', ['paid', 'pending', 'partial', 'overdue', 'cancelled'])->default('pending');

            $table->string('collection_account')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('send_email')->default(false);
            $table->boolean('send_sms')->default(false);
            $table->boolean('send_whatsapp')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_bills');
    }
};
