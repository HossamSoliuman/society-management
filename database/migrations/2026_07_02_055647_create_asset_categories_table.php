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
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('icon')->default('fa-cube');
            $table->string('color')->default('gray');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('movable')->default(true);
            $table->boolean('immovable')->default(true);
            $table->foreignId('parent_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->unsignedInteger('asset_life_years')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_categories');
    }
};
