<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('society_id')->constrained('members')->nullOnDelete();
        });

        Schema::table('maintenance_bills', function (Blueprint $table) {
            $table->timestamp('late_fee_applied_at')->nullable()->after('late_fee');
            $table->timestamp('delivered_at')->nullable()->after('send_whatsapp');
            $table->timestamp('last_reminder_at')->nullable()->after('delivered_at');
            $table->unsignedInteger('reminders_sent')->default(0)->after('last_reminder_at');
        });

        Schema::table('bill_settings', function (Blueprint $table) {
            $table->string('upi_id')->nullable()->after('default_collection_account');
            $table->json('reminder_days_before_due')->nullable()->after('auto_sms_bill');
            $table->json('reminder_days_after_due')->nullable()->after('reminder_days_before_due');
            $table->json('notification_settings')->nullable()->after('reminder_days_after_due');
        });

        Schema::table('late_fee_settings', function (Blueprint $table) {
            $table->string('late_fee_type')->default('percentage')->change();
            $table->decimal('late_fee_per_day', 12, 2)->nullable()->after('late_fee_flat');
        });
    }

    public function down(): void
    {
        Schema::table('late_fee_settings', function (Blueprint $table) {
            $table->dropColumn('late_fee_per_day');
        });

        Schema::table('bill_settings', function (Blueprint $table) {
            $table->dropColumn(['upi_id', 'reminder_days_before_due', 'reminder_days_after_due', 'notification_settings']);
        });

        Schema::table('maintenance_bills', function (Blueprint $table) {
            $table->dropColumn(['late_fee_applied_at', 'delivered_at', 'last_reminder_at', 'reminders_sent']);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_id');
        });
    }
};
