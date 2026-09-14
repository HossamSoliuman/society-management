<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('renewed_from_id')->nullable()->after('plan_id')->constrained('subscriptions')->nullOnDelete();
            $table->decimal('amount', 12, 2)->default(0)->after('monthly_cost_per_flat');
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->string('cancel_reason')->nullable()->after('cancelled_at');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('subscription_id')->nullable()->after('society_id')->constrained('subscriptions')->nullOnDelete();
            $table->string('category')->default('Subscription')->after('invoice_type');
            $table->string('member_name')->nullable()->change();
            $table->string('flat_number')->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('member_name')->nullable()->change();
            $table->string('flat_number')->nullable()->change();
            $table->foreignId('recorded_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->string('member_name')->nullable()->change();
            $table->string('flat_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('member_name')->nullable(false)->change();
            $table->string('flat_number')->nullable(false)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recorded_by');
            $table->string('member_name')->nullable(false)->change();
            $table->string('flat_number')->nullable(false)->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_id');
            $table->dropColumn('category');
            $table->string('member_name')->nullable(false)->change();
            $table->string('flat_number')->nullable(false)->change();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('renewed_from_id');
            $table->dropColumn(['amount', 'cancelled_at', 'cancel_reason']);
        });
    }
};
