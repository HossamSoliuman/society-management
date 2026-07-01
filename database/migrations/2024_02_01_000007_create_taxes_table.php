<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->enum('tax_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('rate', 8, 2)->default(0);
            $table->string('apply_on')->default('All Charge Heads');
            $table->decimal('slab_from', 12, 2)->nullable();
            $table->decimal('slab_to', 12, 2)->nullable();
            $table->string('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
