<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('document_type', ['member_app', 'web_portal', 'other_documents'])->default('member_app');
            $table->string('applies_to')->default('All Societies');
            $table->string('version')->default('1.0');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('registration_number')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('login_banner')->nullable();
            $table->timestamps();
        });

        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('smtp_host');
            $table->integer('smtp_port')->default(587);
            $table->string('encryption')->default('STARTTLS');
            $table->string('authentication')->default('Login');
            $table->string('smtp_username');
            $table->string('smtp_password');
            $table->string('from_email');
            $table->string('from_name');
            $table->string('reply_to_email')->nullable();
            $table->boolean('enable_ssl_tls')->default(true);
            $table->boolean('enable_email_logging')->default(true);
            $table->boolean('send_test_email')->default(false);
            $table->timestamps();
        });

        Schema::create('backup_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('backup_frequency', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->time('backup_time')->default('02:00:00');
            $table->enum('retention_period', ['7_days', '14_days', '30_days', '60_days', '90_days'])->default('30_days');
            $table->enum('backup_location', ['local', 'aws_s3', 'google_drive'])->default('local');
            $table->boolean('email_notification')->default(true);
            $table->boolean('backup_database')->default(true);
            $table->boolean('backup_user_data')->default(true);
            $table->boolean('backup_files')->default(true);
            $table->boolean('backup_logs')->default(true);
            $table->boolean('backup_settings')->default(true);
            $table->timestamps();
        });

        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('min_password_length')->default(8);
            $table->enum('password_expiry', ['30_days', '60_days', '90_days', '180_days', 'never'])->default('90_days');
            $table->boolean('require_uppercase')->default(true);
            $table->boolean('require_lowercase')->default(true);
            $table->boolean('require_number')->default(true);
            $table->boolean('require_special')->default(true);
            $table->boolean('enable_2fa')->default(false);
            $table->enum('2fa_for_super_admin', ['required', 'optional'])->default('required');
            $table->enum('2fa_for_others', ['required', 'optional'])->default('optional');
            $table->enum('auto_logout', ['15_minutes', '30_minutes', '1_hour', '2_hours'])->default('30_minutes');
            $table->integer('login_attempts_limit')->default(5);
            $table->enum('account_lock_duration', ['15_minutes', '30_minutes', '1_hour', '24_hours'])->default('30_minutes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_settings');
        Schema::dropIfExists('backup_settings');
        Schema::dropIfExists('smtp_settings');
        Schema::dropIfExists('company_profiles');
        Schema::dropIfExists('terms_conditions');
    }
};
