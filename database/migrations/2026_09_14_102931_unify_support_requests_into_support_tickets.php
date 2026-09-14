<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->change();
            $table->string('raised_by_type')->default('staff_admin')->after('description');
            $table->foreignId('member_id')->nullable()->after('raised_by_type')->constrained('members')->nullOnDelete();
            $table->string('raised_by_name')->nullable()->after('member_id');
            $table->string('flat_no')->nullable()->after('raised_by_name');
            $table->string('mobile')->nullable()->after('flat_no');
            $table->string('email')->nullable()->after('mobile');
            $table->string('preferred_contact')->nullable()->after('email');
            $table->string('location')->nullable()->after('preferred_contact');
            $table->string('attachment_path')->nullable()->after('location');
            $table->text('notes')->nullable()->after('attachment_path');
            $table->dateTime('raised_at')->nullable()->after('notes');
            $table->timestamp('last_reply_at')->nullable()->after('raised_at');
        });

        if (Schema::hasTable('support_requests')) {
            DB::table('support_requests')->orderBy('id')->chunk(200, function ($rows) {
                $insert = [];
                foreach ($rows as $row) {
                    $insert[] = [
                        'ticket_number' => $row->request_id,
                        'subject' => $row->subject,
                        'description' => $row->description,
                        'category' => $row->category,
                        'priority' => $row->priority,
                        'status' => $row->status,
                        'created_by' => null,
                        'assigned_to' => null,
                        'society_id' => $row->society_id,
                        'raised_by_type' => $row->raised_by_type,
                        'member_id' => $row->member_id,
                        'raised_by_name' => $row->raised_by_name,
                        'flat_no' => $row->flat_no,
                        'mobile' => $row->mobile,
                        'email' => $row->email,
                        'preferred_contact' => $row->preferred_contact,
                        'location' => $row->location,
                        'attachment_path' => $row->attachment_path,
                        'notes' => $row->notes,
                        'raised_at' => $row->raised_at,
                        'resolved_at' => in_array($row->status, ['resolved', 'closed'], true) ? $row->updated_at : null,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ];
                }
                DB::table('support_tickets')->insert($insert);
            });

            Schema::drop('support_requests');
        }
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_id');
            $table->dropColumn([
                'raised_by_type', 'raised_by_name', 'flat_no', 'mobile', 'email', 'preferred_contact',
                'location', 'attachment_path', 'notes', 'raised_at', 'last_reply_at',
            ]);
        });
    }
};
