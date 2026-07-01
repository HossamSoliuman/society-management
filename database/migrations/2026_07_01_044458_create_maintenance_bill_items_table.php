<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_bill_id')->constrained('maintenance_bills')->cascadeOnDelete();
            $table->foreignId('charge_head_id')->nullable()->constrained('charge_heads')->nullOnDelete();
            $table->string('charge_head_name');
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_bill_items');
    }
};
