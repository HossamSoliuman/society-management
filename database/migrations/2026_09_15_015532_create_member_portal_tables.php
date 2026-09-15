<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('society_id')->constrained('users')->nullOnDelete();
            $table->timestamp('invited_at')->nullable()->after('join_date');
        });

        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('name');
            $table->string('relation', 50)->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->boolean('is_resident')->default(true);
            $table->string('id_proof_type', 50)->nullable();
            $table->string('id_proof_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('registration_no', 30);
            $table->string('vehicle_type', 30)->default('car');
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('owner_name')->nullable();
            $table->string('rfid_tag', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['society_id', 'registration_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('family_members');
        Schema::table('members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('invited_at');
        });
    }
};
