<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('unit_number');
            $table->string('building')->nullable();
            $table->string('wing')->nullable();
            $table->string('floor')->nullable();
            $table->string('unit_type')->nullable();
            $table->integer('area_sqft')->nullable();
            $table->enum('status', ['occupied', 'vacant', 'under_maintenance'])->default('vacant');
            $table->string('occupied_by_name')->nullable();
            $table->string('occupied_by_role')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('owner_mobile')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
