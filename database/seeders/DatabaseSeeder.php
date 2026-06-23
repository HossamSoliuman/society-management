<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SocietyTypeSeeder::class,
            UnitTypeSeeder::class,
            PaymentModeSeeder::class,
            SubscriptionPlanSeeder::class,
            SocietySeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
            RefundSeeder::class,
            TermsConditionSeeder::class,
            CompanyProfileSeeder::class,
            SmtpSettingSeeder::class,
            BackupSettingSeeder::class,
            SecuritySettingSeeder::class,
            ActivityLogSeeder::class,
            SystemLogSeeder::class,
            AnnouncementSeeder::class,
            SupportTicketSeeder::class,
            PrefixSettingSeeder::class,
        ]);
    }
}
