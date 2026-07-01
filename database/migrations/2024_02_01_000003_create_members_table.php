<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->string('name');
            $table->enum('member_type', ['owner', 'family_member', 'tenant'])->default('owner');
            $table->string('flat_unit')->nullable();
            $table->string('tower_wing')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->string('avatar')->nullable();
            $table->date('join_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
