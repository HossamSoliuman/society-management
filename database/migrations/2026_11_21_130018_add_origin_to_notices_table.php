<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `platform` notices come from the super admin; `society` notices are
     * written by a society's own office for its residents and team.
     */
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->string('origin', 20)->default('platform')->after('society_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->dropIndex(['origin']);
            $table->dropColumn('origin');
        });
    }
};
