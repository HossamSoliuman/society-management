<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('sub_title')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('department')->nullable();
            $table->enum('tender_type', ['service', 'supply'])->default('service');
            $table->text('description')->nullable();
            $table->string('tender_category')->nullable();
            $table->decimal('estimated_value', 14, 2)->nullable();
            $table->decimal('emd_amount', 14, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->dateTime('submission_deadline')->nullable();
            $table->dateTime('opening_date')->nullable();
            $table->integer('validity_days')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('delivery_terms')->nullable();
            $table->string('contract_type')->nullable();
            $table->string('tax_option')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('eligibility_criteria')->nullable();
            $table->text('evaluation_criteria')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('venue')->nullable();
            $table->string('visibility')->nullable();
            $table->boolean('allow_online_submission')->default(true);
            $table->boolean('allow_partial_bidding')->default(false);
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'in_progress', 'draft', 'under_review', 'awarded', 'closed'])->default('draft');
            $table->string('awarded_vendor')->nullable();
            $table->decimal('contract_value', 14, 2)->nullable();
            $table->date('awarded_date')->nullable();
            $table->date('closed_date')->nullable();
            $table->string('closed_reason')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
