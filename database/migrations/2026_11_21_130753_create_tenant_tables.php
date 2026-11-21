<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('owner_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('name');
            $table->string('mobile', 30);
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('occupants_count')->default(1);
            $table->string('id_proof_type', 50)->nullable();
            $table->string('id_proof_number', 100)->nullable();
            $table->text('permanent_address')->nullable();
            $table->date('move_in_date')->nullable();
            $table->date('move_out_date')->nullable();
            $table->string('police_verification', 20)->default('pending');
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'status']);
        });

        Schema::create('tenant_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('agreement_number', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->string('document_path')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('terminated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['society_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_agreements');
        Schema::dropIfExists('tenants');
    }
};
