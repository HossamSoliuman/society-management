<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('message');
            $table->enum('recipient_type', ['all_members', 'all_residents', 'all_staff', 'custom'])->default('all_members');
            $table->integer('estimated_recipients')->default(0);
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->string('category')->nullable();
            $table->enum('delivery_channel', ['in_app', 'email', 'sms', 'all'])->default('all');
            $table->enum('send_type', ['now', 'scheduled'])->default('now');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['draft', 'sent', 'scheduled'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->text('description');
            $table->string('category')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed', 'reopened'])->default('open');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('society_id')->nullable()->constrained('societies');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->text('message');
            $table->string('attachment')->nullable();
            $table->timestamps();
        });

        Schema::create('prefix_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('prefix');
            $table->integer('starting_number')->default(1);
            $table->integer('current_number')->default(1);
            $table->integer('padding')->default(4);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('prefix_settings');
    }
};
