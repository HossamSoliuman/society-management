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
            SocietyProfileSeeder::class,
            UnitSeeder::class,
            MemberSeeder::class,
            BillUploadSeeder::class,
            Phase2SettingsSeeder::class,
            MaintenanceBillSeeder::class,
            CollectionPaymentSeeder::class,
            ExpenseCategorySeeder::class,
            VendorSeeder::class,
            ExpenseSeeder::class,
            AssetCategorySeeder::class,
            AssetSeeder::class,
            SupportRequestSeeder::class,
            AccountGroupSeeder::class,
            AccountSeeder::class,
            TransactionSeeder::class,
            ReceiptSeeder::class,
            AccountingPaymentSeeder::class,
            JournalEntrySeeder::class,
            ServiceVendorSeeder::class,
            AmcCategorySeeder::class,
            AmcContractSeeder::class,
            TenderSeeder::class,
            DocumentCategorySeeder::class,
            DocumentSeeder::class,
            NoticeSeeder::class,
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
