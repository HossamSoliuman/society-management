<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charge_heads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('category', ['maintenance', 'utilities', 'parking', 'amenities', 'other'])->default('maintenance');
            $table->enum('type', ['recurring', 'one_time'])->default('recurring');
            $table->enum('calculation_type', ['per_flat', 'per_sqft', 'per_slot', 'fixed'])->default('per_flat');
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->string('applies_to')->default('All Bills');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charge_heads');
    }
};
