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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->string('asset_code')->unique();
            $table->foreignId('category_id')->constrained('asset_categories')->cascadeOnDelete();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('tower_wing')->nullable();
            $table->string('floor')->nullable();
            $table->string('area_room')->nullable();
            $table->string('assigned_to')->nullable();
            $table->string('vendor_supplier')->nullable();
            $table->string('purchase_from')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 14, 2)->default(0);
            $table->date('warranty_start')->nullable();
            $table->date('warranty_end')->nullable();
            $table->string('invoice_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->unsignedInteger('expected_life_years')->nullable();
            $table->string('depreciation_method')->nullable();
            $table->enum('status', ['in_use', 'under_maintenance', 'inactive', 'disposed'])->default('in_use');
            $table->enum('condition', ['good', 'fair', 'poor'])->default('good');
            $table->string('usage_type')->nullable();
            $table->string('qr_code')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('current_value', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
