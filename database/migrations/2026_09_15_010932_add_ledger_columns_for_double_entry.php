<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_groups', function (Blueprint $table) {
            $table->string('kind', 20)->default('asset')->after('name');
        });

        // Derive the accounting nature from the seeded group names.
        foreach (['Assets' => 'asset', 'Liabilities' => 'liability', 'Income' => 'income', 'Other Income' => 'income', 'Expenses' => 'expense', 'Equity' => 'equity'] as $name => $kind) {
            DB::table('account_groups')->where('name', $name)->update(['kind' => $kind]);
        }

        Schema::table('accounts', function (Blueprint $table) {
            $table->string('system_key', 40)->nullable()->after('name')->index();
            $table->boolean('is_bank')->default(false)->after('system_key');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->nullableMorphs('source');
            $table->timestamp('reconciled_at')->nullable()->after('location');
            $table->foreignId('bank_statement_line_id')->nullable()->after('reconciled_at');
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->foreignId('income_account_id')->nullable()->after('account_id')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('accounting_payments', function (Blueprint $table) {
            $table->foreignId('expense_account_id')->nullable()->after('account_id')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->boolean('is_opening')->default(false)->after('status');
            $table->nullableMorphs('source');
        });

        Schema::table('charge_heads', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('applies_to')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('expense_categories', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('applicable_for')->constrained('accounts')->nullOnDelete();
        });

        Schema::table('numbering_series', function (Blueprint $table) {
            $table->string('document_type', 40)->default('maintenance_bill')->change();
        });

        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained('societies')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('import_batch', 40)->index();
            $table->date('statement_date');
            $table->string('description')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statement_lines');

        Schema::table('expense_categories', fn (Blueprint $table) => $table->dropConstrainedForeignId('account_id'));
        Schema::table('charge_heads', fn (Blueprint $table) => $table->dropConstrainedForeignId('account_id'));
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropMorphs('source');
            $table->dropColumn('is_opening');
        });
        Schema::table('accounting_payments', fn (Blueprint $table) => $table->dropConstrainedForeignId('expense_account_id'));
        Schema::table('receipts', fn (Blueprint $table) => $table->dropConstrainedForeignId('income_account_id'));
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropMorphs('source');
            $table->dropColumn(['reconciled_at', 'bank_statement_line_id']);
        });
        Schema::table('accounts', fn (Blueprint $table) => $table->dropColumn(['system_key', 'is_bank']));
        Schema::table('account_groups', fn (Blueprint $table) => $table->dropColumn('kind'));
    }
};
