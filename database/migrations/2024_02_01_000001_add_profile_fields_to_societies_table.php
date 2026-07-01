<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('societies', function (Blueprint $table) {
            $table->string('society_code')->nullable()->after('name');
            $table->string('rera_number')->nullable()->after('registration_number');
            $table->string('building_type')->nullable()->after('society_type_id');
            $table->integer('year_established')->nullable()->after('registration_date');
            $table->integer('wings_count')->nullable()->after('year_established');
            $table->integer('blocks_count')->nullable()->after('wings_count');
            $table->integer('total_units')->nullable()->after('blocks_count');
            $table->string('logo_path')->nullable()->after('website');
            $table->string('photo_path')->nullable()->after('logo_path');
            $table->string('management_type')->nullable()->after('photo_path');
            $table->integer('committee_members_count')->nullable()->after('management_type');
            $table->string('audit_type')->nullable()->after('committee_members_count');
            $table->string('financial_year')->nullable()->after('audit_type');
            $table->string('maintenance_collection_day')->nullable()->after('financial_year');
            $table->string('bank_name')->nullable()->after('maintenance_collection_day');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('ifsc_code')->nullable()->after('account_number');
            $table->string('gst_number')->nullable()->after('ifsc_code');
            $table->string('office_timings')->nullable()->after('gst_number');
            $table->json('amenities')->nullable()->after('office_timings');
            $table->text('about')->nullable()->after('amenities');
        });
    }

    public function down(): void
    {
        Schema::table('societies', function (Blueprint $table) {
            $table->dropColumn([
                'society_code', 'rera_number', 'building_type', 'year_established',
                'wings_count', 'blocks_count', 'total_units', 'logo_path', 'photo_path',
                'management_type', 'committee_members_count', 'audit_type', 'financial_year',
                'maintenance_collection_day', 'bank_name', 'account_number', 'ifsc_code',
                'gst_number', 'office_timings', 'amenities', 'about',
            ]);
        });
    }
};
