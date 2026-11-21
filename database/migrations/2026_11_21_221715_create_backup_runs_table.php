<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_runs', function (Blueprint $table) {
            $table->id();
            $table->string('trigger', 20)->default('scheduled');
            $table->string('status', 20)->default('queued');
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->json('contents')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Only "local" storage is implemented; move any other stored choice onto it.
        if (Schema::hasTable('backup_settings')) {
            DB::table('backup_settings')->where('backup_location', '!=', 'local')->update(['backup_location' => 'local']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_runs');
    }
};
