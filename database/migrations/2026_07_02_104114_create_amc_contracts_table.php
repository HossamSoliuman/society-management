<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amc_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_asset');
            $table->string('item_sub')->nullable();
            $table->foreignId('amc_category_id')->nullable()->constrained('amc_categories')->nullOnDelete();
            $table->string('vendor_name')->nullable();
            $table->unsignedBigInteger('service_vendor_id')->nullable();
            $table->string('contract_no')->nullable();
            $table->string('po_invoice_no')->nullable();
            $table->string('contract_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration_months')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->nullable();
            $table->integer('renewal_reminder_days')->default(30);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'expiring_soon', 'expired', 'draft'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amc_contracts');
    }
};
