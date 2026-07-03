<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('notice_type', ['general', 'maintenance', 'billing', 'event'])->default('general');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->string('short_description')->nullable();
            $table->longText('content')->nullable();
            $table->string('attach_path')->nullable();
            $table->dateTime('publish_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('pin_to_dashboard')->default(false);
            $table->string('audience_type')->default('all_members');
            $table->integer('estimated_recipients')->default(0);
            $table->boolean('send_email')->default(false);
            $table->boolean('send_sms')->default(false);
            $table->boolean('require_acknowledgement')->default(false);
            $table->enum('status', ['published', 'scheduled', 'draft', 'expired'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
