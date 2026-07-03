<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->foreignId('document_category_id')->nullable()->constrained('document_categories')->nullOnDelete();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('related_to')->nullable();
            $table->json('tags')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('confidentiality', ['general', 'confidential', 'restricted'])->default('general');
            $table->string('uploaded_by')->nullable();
            $table->string('size')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('downloads')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
