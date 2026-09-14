<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->foreignId('society_id')->nullable()->after('id')->constrained('societies')->nullOnDelete();
            $table->json('target_roles')->nullable()->after('recipient_type');
            $table->unsignedInteger('delivered_count')->default(0)->after('estimated_recipients');
        });

        Schema::table('notices', function (Blueprint $table) {
            $table->foreignId('society_id')->nullable()->after('id')->constrained('societies')->nullOnDelete();
            $table->json('target_roles')->nullable()->after('audience_type');
            $table->unsignedInteger('delivered_count')->default(0)->after('estimated_recipients');
            $table->timestamp('delivered_at')->nullable()->after('delivered_count');
        });

        Schema::create('notice_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained('notices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('acknowledged_at');
            $table->timestamps();
            $table->unique(['notice_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_acknowledgements');

        Schema::table('notices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('society_id');
            $table->dropColumn(['target_roles', 'delivered_count', 'delivered_at']);
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('society_id');
            $table->dropColumn(['target_roles', 'delivered_count']);
        });
    }
};
