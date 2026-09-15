<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Member\BillController as MemberBillController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\FamilyMemberController as MemberFamilyController;
use App\Http\Controllers\Member\NoticeController as MemberNoticeController;
use App\Http\Controllers\Member\PaymentController as MemberPaymentController;
use App\Http\Controllers\Member\ProfileController as MemberProfileController;
use App\Http\Controllers\Member\SupportController as MemberSupportController;
use App\Http\Controllers\Member\VehicleController as MemberVehicleController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\Society\AccountingController;
use App\Http\Controllers\Society\AmcController;
use App\Http\Controllers\Society\AssetCategoryController;
use App\Http\Controllers\Society\AssetController;
use App\Http\Controllers\Society\BillController;
use App\Http\Controllers\Society\BillSettingController;
use App\Http\Controllers\Society\BulkUploadController;
use App\Http\Controllers\Society\ChargeHeadController;
use App\Http\Controllers\Society\CollectionController;
use App\Http\Controllers\Society\DashboardController as SocietyDashboardController;
use App\Http\Controllers\Society\DocumentController;
use App\Http\Controllers\Society\ExpenseCategoryController;
use App\Http\Controllers\Society\ExpenseController;
use App\Http\Controllers\Society\MemberController;
use App\Http\Controllers\Society\NoticeController as SocietyNoticeController;
use App\Http\Controllers\Society\NumberingSeriesController;
use App\Http\Controllers\Society\OnlinePaymentController;
use App\Http\Controllers\Society\PaymentReceiptController;
use App\Http\Controllers\Society\PlaceholderController;
use App\Http\Controllers\Society\ProfileController as SocietyProfileController;
use App\Http\Controllers\Society\ReportController as SocietyReportController;
use App\Http\Controllers\Society\ServiceVendorController;
use App\Http\Controllers\Society\SubscriptionController as SocietySubscriptionController;
use App\Http\Controllers\Society\SupportController;
use App\Http\Controllers\Society\TaxController;
use App\Http\Controllers\Society\TenderController;
use App\Http\Controllers\Society\UnitController;
use App\Http\Controllers\Society\UserController as SocietyUserController;
use App\Http\Controllers\Society\VendorController;
use App\Http\Controllers\SuperAdmin\AccountController;
use App\Http\Controllers\SuperAdmin\ActivityLogController;
use App\Http\Controllers\SuperAdmin\BillingController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\MasterController;
use App\Http\Controllers\SuperAdmin\NoticeController;
use App\Http\Controllers\SuperAdmin\NotificationController;
use App\Http\Controllers\SuperAdmin\ReportController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\SuperAdmin\SettingController;
use App\Http\Controllers\SuperAdmin\SocietyController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\SuperAdmin\SupportTicketController;
use App\Http\Controllers\SuperAdmin\TermsConditionController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Payment-provider callbacks: signature-verified inside the gateway driver.
Route::post('/webhooks/payments', PaymentWebhookController::class)->name('webhooks.payments');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'update'])->name('password.update');
});

Route::middleware(['auth', 'active', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/societies', [SocietyController::class, 'index'])->name('societies.index');
    Route::get('/societies/create', [SocietyController::class, 'create'])->name('societies.create');
    Route::post('/societies', [SocietyController::class, 'store'])->name('societies.store');
    Route::get('/societies/{society}', [SocietyController::class, 'show'])->name('societies.show');
    Route::get('/societies/{society}/edit', [SocietyController::class, 'edit'])->name('societies.edit');
    Route::put('/societies/{society}', [SocietyController::class, 'update'])->name('societies.update');
    Route::delete('/societies/{society}', [SocietyController::class, 'destroy'])->name('societies.destroy');

    Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
    Route::get('/subscription/plans/create', [SubscriptionController::class, 'createPlan'])->name('subscription.plans.create');
    Route::post('/subscription/plans', [SubscriptionController::class, 'storePlan'])->name('subscription.plans.store');
    Route::get('/subscription/subscriptions', [SubscriptionController::class, 'subscriptions'])->name('subscription.subscriptions');
    Route::get('/subscription/subscriptions/create', [SubscriptionController::class, 'createSubscription'])->name('subscription.subscriptions.create');
    Route::post('/subscription/subscriptions', [SubscriptionController::class, 'storeSubscription'])->name('subscription.subscriptions.store');
    Route::get('/subscription/subscriptions/{subscription}/renew', [SubscriptionController::class, 'renewForm'])->name('subscription.subscriptions.renew');
    Route::post('/subscription/subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscription.subscriptions.renew.store');
    Route::post('/subscription/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.subscriptions.cancel');
    Route::get('/subscription/renewals', [SubscriptionController::class, 'renewals'])->name('subscription.renewals');

    Route::get('/billing/overview', [BillingController::class, 'overview'])->name('billing.overview');
    Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
    Route::get('/billing/invoices/create', [BillingController::class, 'createInvoice'])->name('billing.invoices.create');
    Route::post('/billing/invoices', [BillingController::class, 'storeInvoice'])->name('billing.invoices.store');
    Route::get('/billing/payments', [BillingController::class, 'payments'])->name('billing.payments');
    Route::get('/billing/payments/create', [BillingController::class, 'createPayment'])->name('billing.payments.create');
    Route::post('/billing/payments', [BillingController::class, 'recordPayment'])->name('billing.payments.store');
    Route::get('/billing/refunds/create', [BillingController::class, 'createRefund'])->name('billing.refunds.create');
    Route::post('/billing/refunds', [BillingController::class, 'storeRefund'])->name('billing.refunds.store');
    Route::get('/billing/receipts', [BillingController::class, 'receipts'])->name('billing.receipts');
    Route::get('/billing/outstanding', [BillingController::class, 'outstanding'])->name('billing.outstanding');
    Route::get('/billing/overdue', [BillingController::class, 'overdue'])->name('billing.overdue');
    Route::get('/billing/refunds', [BillingController::class, 'refunds'])->name('billing.refunds');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('/users/login-activity', [UserController::class, 'loginActivity'])->name('users.login-activity');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/resend-invitation', [UserController::class, 'resendInvitation'])->name('users.resend-invitation');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/society', [ReportController::class, 'societyReport'])->name('reports.society');
    Route::get('/reports/revenue', [ReportController::class, 'revenueReport'])->name('reports.revenue');
    Route::get('/reports/subscription', [ReportController::class, 'subscriptionReport'])->name('reports.subscription');
    Route::get('/reports/payment', [ReportController::class, 'paymentReport'])->name('reports.payment');

    Route::get('/notifications/announcements', [NotificationController::class, 'announcements'])->name('notification.announcements');
    Route::get('/notifications/announcements/create', [NotificationController::class, 'createAnnouncement'])->name('notification.announcements.create');
    Route::post('/notifications/announcements', [NotificationController::class, 'storeAnnouncement'])->name('notification.announcements.store');
    Route::get('/notifications/announcements/estimate', [NotificationController::class, 'estimateRecipients'])->name('notification.announcements.estimate');
    Route::get('/notifications/renewals', [NotificationController::class, 'renewalAlerts'])->name('notification.renewals');

    Route::get('/notices', [NoticeController::class, 'index'])->name('notices.index');
    Route::get('/notices/create', [NoticeController::class, 'create'])->name('notices.create');
    Route::post('/notices', [NoticeController::class, 'store'])->name('notices.store');

    Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
    Route::put('/tickets/{ticket}/status', [SupportTicketController::class, 'updateStatus'])->name('tickets.status');
    Route::post('/tickets/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('tickets.reply');

    Route::get('/logs/user-activities', [ActivityLogController::class, 'userActivities'])->name('logs.user-activities');
    Route::get('/logs/system-logs', [ActivityLogController::class, 'systemLogs'])->name('logs.system-logs');
    Route::get('/logs/audit-trail', [ActivityLogController::class, 'auditTrail'])->name('logs.audit-trail');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');

    Route::get('/masters', [MasterController::class, 'index'])->name('masters.index');
    Route::post('/masters/society-types', [MasterController::class, 'storeSocietyType'])->name('masters.society-types.store');
    Route::put('/masters/society-types/{societyType}', [MasterController::class, 'updateSocietyType'])->name('masters.society-types.update');
    Route::delete('/masters/society-types/{societyType}', [MasterController::class, 'destroySocietyType'])->name('masters.society-types.destroy');
    Route::post('/masters/unit-types', [MasterController::class, 'storeUnitType'])->name('masters.unit-types.store');
    Route::put('/masters/unit-types/{unitType}', [MasterController::class, 'updateUnitType'])->name('masters.unit-types.update');
    Route::delete('/masters/unit-types/{unitType}', [MasterController::class, 'destroyUnitType'])->name('masters.unit-types.destroy');
    Route::post('/masters/payment-modes', [MasterController::class, 'storePaymentMode'])->name('masters.payment-modes.store');
    Route::put('/masters/payment-modes/{paymentMode}', [MasterController::class, 'updatePaymentMode'])->name('masters.payment-modes.update');
    Route::delete('/masters/payment-modes/{paymentMode}', [MasterController::class, 'destroyPaymentMode'])->name('masters.payment-modes.destroy');

    Route::get('/terms', [TermsConditionController::class, 'index'])->name('terms.index');
    Route::get('/terms/create', [TermsConditionController::class, 'create'])->name('terms.create');
    Route::post('/terms', [TermsConditionController::class, 'store'])->name('terms.store');
    Route::get('/terms/{term}/edit', [TermsConditionController::class, 'edit'])->name('terms.edit');
    Route::put('/terms/{term}', [TermsConditionController::class, 'update'])->name('terms.update');
    Route::delete('/terms/{term}', [TermsConditionController::class, 'destroy'])->name('terms.destroy');

    Route::get('/settings/company-profile', [SettingController::class, 'companyProfile'])->name('settings.company-profile');
    Route::put('/settings/company-profile', [SettingController::class, 'updateCompanyProfile'])->name('settings.company-profile.update');
    Route::get('/settings/prefix', [SettingController::class, 'prefixSettings'])->name('settings.prefix');
    Route::post('/settings/prefix', [SettingController::class, 'storePrefix'])->name('settings.prefix.store');
    Route::get('/settings/smtp', [SettingController::class, 'smtpSettings'])->name('settings.smtp');
    Route::put('/settings/smtp', [SettingController::class, 'updateSmtp'])->name('settings.smtp.update');
    Route::get('/settings/backup', [SettingController::class, 'backupSettings'])->name('settings.backup');
    Route::put('/settings/backup', [SettingController::class, 'updateBackup'])->name('settings.backup.update');
    Route::get('/settings/security', [SettingController::class, 'securitySettings'])->name('settings.security');
    Route::put('/settings/security', [SettingController::class, 'updateSecurity'])->name('settings.security.update');
});

Route::middleware(['auth', 'active', 'role:society_admin,manager,staff,accountant', 'society.access'])->prefix('society')->name('society.')->group(function () {
    Route::get('/dashboard', [SocietyDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [SocietyDashboardController::class, 'data'])->name('dashboard.data');

    // Society Profile (every society role)
    Route::get('/profile', [SocietyProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [SocietyProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [SocietyProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [SocietyProfileController::class, 'updatePassword'])->name('profile.password');

    // My Subscription (read-only, every society role)
    Route::get('/subscription', [SocietySubscriptionController::class, 'index'])->name('subscription.index');

    // Notices targeted at this society (every society role)
    Route::get('/notices', [SocietyNoticeController::class, 'index'])->name('notices.index');
    Route::get('/notices/{notice}', [SocietyNoticeController::class, 'show'])->name('notices.show');
    Route::post('/notices/{notice}/acknowledge', [SocietyNoticeController::class, 'acknowledge'])->name('notices.acknowledge');

    // Members & Units
    Route::middleware('permission:members.manage')->group(function () {
        Route::get('/members', [MemberController::class, 'index'])->name('members.index');
        Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
        Route::post('/members', [MemberController::class, 'store'])->name('members.store');
        Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
        Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
        Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
        Route::post('/members/{member}/invite', [MemberController::class, 'invite'])->name('members.invite');

        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
        Route::get('/units/create', [UnitController::class, 'create'])->name('units.create');
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        Route::get('/units/import', [UnitController::class, 'importForm'])->name('units.import');
        Route::post('/units/import', [UnitController::class, 'import'])->name('units.import.store');
        Route::get('/units/{unit}', [UnitController::class, 'show'])->name('units.show');
        Route::get('/units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
        Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
        Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
    });

    // Maintenance Billing & Collections
    Route::middleware('permission:billing.manage')->group(function () {
        Route::get('/billing/bulk-upload', [BulkUploadController::class, 'index'])->name('billing.bulk-upload');
        Route::post('/billing/bulk-upload', [BulkUploadController::class, 'upload'])->name('billing.bulk-upload.store');
        Route::get('/billing/bulk-upload/sample', [BulkUploadController::class, 'sample'])->name('billing.bulk-upload.sample');

        // Static routes precede the {bill} wildcard.
        Route::get('billing/bills', [BillController::class, 'index'])->name('billing.bills.index');
        Route::get('billing/bills/create', [BillController::class, 'create'])->name('billing.bills.create');
        Route::post('billing/bills', [BillController::class, 'store'])->name('billing.bills.store');
        Route::get('billing/bills/bulk-upload', [BillController::class, 'bulkUpload'])->name('billing.bills.bulk');
        Route::post('billing/bills/bulk-upload', [BillController::class, 'bulkStore'])->name('billing.bills.bulk.store');
        Route::post('billing/bills/bulk-upload/confirm', [BillController::class, 'bulkConfirm'])->name('billing.bills.bulk.confirm');
        Route::post('billing/bills/bulk-upload/discard', [BillController::class, 'bulkDiscard'])->name('billing.bills.bulk.discard');
        Route::get('billing/bills/generate', [BillController::class, 'generate'])->name('billing.bills.generate');
        Route::post('billing/bills/generate', [BillController::class, 'storeGenerate'])->name('billing.bills.generate.store');
        Route::get('billing/bills/{bill}', [BillController::class, 'show'])->name('billing.bills.show');
        Route::get('billing/bills/{bill}/print', [BillController::class, 'print'])->name('billing.bills.print');
        Route::get('billing/bills/{bill}/pdf', [BillController::class, 'pdf'])->name('billing.bills.pdf');
        Route::post('billing/bills/{bill}/send', [BillController::class, 'send'])->name('billing.bills.send');

        Route::get('billing/settings/general', [BillSettingController::class, 'general'])->name('billing.settings.general');
        Route::put('billing/settings/general', [BillSettingController::class, 'updateGeneral'])->name('billing.settings.general.update');
        Route::get('billing/settings/charge-heads', [ChargeHeadController::class, 'index'])->name('billing.settings.charge-heads');
        Route::post('billing/settings/charge-heads', [ChargeHeadController::class, 'store'])->name('billing.settings.charge-heads.store');
        Route::put('billing/settings/charge-heads/{chargeHead}', [ChargeHeadController::class, 'update'])->name('billing.settings.charge-heads.update');
        Route::delete('billing/settings/charge-heads/{chargeHead}', [ChargeHeadController::class, 'destroy'])->name('billing.settings.charge-heads.destroy');
        Route::get('billing/settings/design', [BillSettingController::class, 'design'])->name('billing.settings.design');
        Route::put('billing/settings/design', [BillSettingController::class, 'updateDesign'])->name('billing.settings.design.update');
        Route::get('billing/settings/late-fee', [BillSettingController::class, 'lateFee'])->name('billing.settings.late-fee');
        Route::put('billing/settings/late-fee', [BillSettingController::class, 'updateLateFee'])->name('billing.settings.late-fee.update');
        Route::get('billing/settings/taxes', [TaxController::class, 'index'])->name('billing.settings.taxes');
        Route::post('billing/settings/taxes', [TaxController::class, 'store'])->name('billing.settings.taxes.store');
        Route::put('billing/settings/taxes/{tax}', [TaxController::class, 'update'])->name('billing.settings.taxes.update');
        Route::delete('billing/settings/taxes/{tax}', [TaxController::class, 'destroy'])->name('billing.settings.taxes.destroy');
        Route::get('billing/settings/notifications', [BillSettingController::class, 'notifications'])->name('billing.settings.notifications');
        Route::put('billing/settings/notifications', [BillSettingController::class, 'updateNotifications'])->name('billing.settings.notifications.update');
        Route::get('billing/settings/numbering', [NumberingSeriesController::class, 'index'])->name('billing.settings.numbering');
        Route::post('billing/settings/numbering', [NumberingSeriesController::class, 'store'])->name('billing.settings.numbering.store');
        Route::put('billing/settings/numbering/{series}', [NumberingSeriesController::class, 'update'])->name('billing.settings.numbering.update');
        Route::delete('billing/settings/numbering/{series}', [NumberingSeriesController::class, 'destroy'])->name('billing.settings.numbering.destroy');

        // Collections. Static routes precede the {payment} wildcard.
        Route::get('collections', [CollectionController::class, 'index'])->name('collections.index');
        Route::get('collections/online', [CollectionController::class, 'online'])->name('collections.online');
        Route::get('collections/pending-dues', [CollectionController::class, 'pendingDues'])->name('collections.pending-dues');
        Route::get('collections/record', [CollectionController::class, 'create'])->name('collections.create');
        Route::post('collections', [CollectionController::class, 'store'])->name('collections.store');
        Route::get('collections/receipts', [PaymentReceiptController::class, 'index'])->name('collections.receipts.index');
        Route::get('collections/receipts/{payment}', [PaymentReceiptController::class, 'show'])->name('collections.receipts.show');
        Route::post('collections/online/bills/{bill}', [OnlinePaymentController::class, 'createForBill'])->name('collections.online.bill');
        Route::get('collections/online/checkout/{order}', [OnlinePaymentController::class, 'checkout'])->name('collections.online.checkout');
        Route::get('collections/receipts/{payment}/pdf', [PaymentReceiptController::class, 'pdf'])->name('collections.receipts.pdf');
        Route::post('collections/receipts/{payment}/email', [PaymentReceiptController::class, 'email'])->name('collections.receipts.email');
    });

    // Expenses. Static segments precede the {expense} wildcard so they aren't captured by binding.
    Route::middleware('permission:expenses.manage')->group(function () {
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::get('expenses/reports', [ExpenseController::class, 'reports'])->name('expenses.reports');

        Route::get('expenses/categories', [ExpenseCategoryController::class, 'index'])->name('expenses.categories.index');
        Route::get('expenses/categories/create', [ExpenseCategoryController::class, 'create'])->name('expenses.categories.create');
        Route::post('expenses/categories', [ExpenseCategoryController::class, 'store'])->name('expenses.categories.store');
        Route::get('expenses/categories/{category}/edit', [ExpenseCategoryController::class, 'edit'])->name('expenses.categories.edit');
        Route::put('expenses/categories/{category}', [ExpenseCategoryController::class, 'update'])->name('expenses.categories.update');
        Route::delete('expenses/categories/{category}', [ExpenseCategoryController::class, 'destroy'])->name('expenses.categories.destroy');

        Route::get('expenses/vendors', [VendorController::class, 'index'])->name('expenses.vendors.index');
        Route::get('expenses/vendors/create', [VendorController::class, 'create'])->name('expenses.vendors.create');
        Route::post('expenses/vendors', [VendorController::class, 'store'])->name('expenses.vendors.store');
        Route::get('expenses/vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('expenses.vendors.edit');
        Route::put('expenses/vendors/{vendor}', [VendorController::class, 'update'])->name('expenses.vendors.update');
        Route::delete('expenses/vendors/{vendor}', [VendorController::class, 'destroy'])->name('expenses.vendors.destroy');

        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // Assets. Static segments precede the {asset} wildcard so they aren't captured by binding.
    Route::middleware('permission:assets.manage')->group(function () {
        Route::get('assets/create', [AssetController::class, 'create'])->name('assets.create');
        Route::post('assets/import', [AssetController::class, 'import'])->name('assets.import');
        Route::get('assets/import/sample', [AssetController::class, 'importSample'])->name('assets.import.sample');

        Route::get('assets/categories', [AssetCategoryController::class, 'index'])->name('assets.categories.index');
        Route::get('assets/categories/create', [AssetCategoryController::class, 'create'])->name('assets.categories.create');
        Route::post('assets/categories', [AssetCategoryController::class, 'store'])->name('assets.categories.store');
        Route::get('assets/categories/{category}/edit', [AssetCategoryController::class, 'edit'])->name('assets.categories.edit');
        Route::put('assets/categories/{category}', [AssetCategoryController::class, 'update'])->name('assets.categories.update');
        Route::delete('assets/categories/{category}', [AssetCategoryController::class, 'destroy'])->name('assets.categories.destroy');

        Route::get('assets', [AssetController::class, 'index'])->name('assets.index');
        Route::post('assets', [AssetController::class, 'store'])->name('assets.store');
        Route::get('assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    });

    // Priority Support. Static segment precedes the {request} wildcard.
    Route::middleware('permission:support.manage')->group(function () {
        Route::get('support/create', [SupportController::class, 'create'])->name('support.create');
        Route::get('support', [SupportController::class, 'index'])->name('support.index');
        Route::post('support', [SupportController::class, 'store'])->name('support.store');
        Route::get('support/{request}', [SupportController::class, 'show'])->name('support.show');
        Route::post('support/{request}/reply', [SupportController::class, 'reply'])->name('support.reply');
    });

    // Accounting. In-page tabs + Chart of Accounts / Opening Balances pages.
    Route::middleware('permission:accounting.manage')->group(function () {
        Route::get('accounting', [AccountingController::class, 'index'])->name('accounting.index');
        Route::get('accounting/transactions', [AccountingController::class, 'transactions'])->name('accounting.transactions');
        Route::get('accounting/receipts', [AccountingController::class, 'receipts'])->name('accounting.receipts');
        Route::get('accounting/receipts/create', [AccountingController::class, 'createReceipt'])->name('accounting.receipts.create');
        Route::post('accounting/receipts', [AccountingController::class, 'storeReceipt'])->name('accounting.receipts.store');
        Route::get('accounting/payments', [AccountingController::class, 'payments'])->name('accounting.payments');
        Route::get('accounting/payments/create', [AccountingController::class, 'createPayment'])->name('accounting.payments.create');
        Route::post('accounting/payments', [AccountingController::class, 'storePayment'])->name('accounting.payments.store');
        Route::get('accounting/journal-entries', [AccountingController::class, 'journalEntries'])->name('accounting.journal-entries');
        Route::get('accounting/journal-entries/create', [AccountingController::class, 'createJournalEntry'])->name('accounting.journal-entries.create');
        Route::post('accounting/journal-entries', [AccountingController::class, 'storeJournalEntry'])->name('accounting.journal-entries.store');
        Route::get('accounting/bank-reconciliation', [AccountingController::class, 'bankReconciliation'])->name('accounting.bank-reconciliation');
        Route::post('accounting/bank-reconciliation/import', [AccountingController::class, 'importBankStatement'])->name('accounting.bank-reconciliation.import');
        Route::post('accounting/bank-reconciliation/{line}/match', [AccountingController::class, 'matchBankLine'])->name('accounting.bank-reconciliation.match');
        Route::post('accounting/bank-reconciliation/{line}/unmatch', [AccountingController::class, 'unmatchBankLine'])->name('accounting.bank-reconciliation.unmatch');
        Route::get('accounting/trial-balance', [AccountingController::class, 'trialBalance'])->name('accounting.trial-balance');
        Route::get('accounting/profit-loss', [AccountingController::class, 'profitLoss'])->name('accounting.profit-loss');
        Route::get('accounting/balance-sheet', [AccountingController::class, 'balanceSheet'])->name('accounting.balance-sheet');
        Route::get('accounting/chart-of-accounts', [AccountingController::class, 'chartOfAccounts'])->name('accounting.chart-of-accounts');
        Route::get('accounting/chart-of-accounts/create', [AccountingController::class, 'createAccount'])->name('accounting.chart-of-accounts.create');
        Route::post('accounting/chart-of-accounts', [AccountingController::class, 'storeAccount'])->name('accounting.chart-of-accounts.store');
        Route::get('accounting/opening-balances', [AccountingController::class, 'openingBalances'])->name('accounting.opening-balances');
        Route::put('accounting/opening-balances', [AccountingController::class, 'updateOpeningBalances'])->name('accounting.opening-balances.update');
    });

    // Vendor Management, AMC & Tenders
    Route::middleware('permission:vendors.manage')->group(function () {
        Route::get('vendors', [ServiceVendorController::class, 'index'])->name('vendors.index');
        Route::get('vendors/create', [ServiceVendorController::class, 'create'])->name('vendors.create');
        Route::post('vendors', [ServiceVendorController::class, 'store'])->name('vendors.store');
        Route::get('vendors/{vendor}/edit', [ServiceVendorController::class, 'edit'])->name('vendors.edit');
        Route::put('vendors/{vendor}', [ServiceVendorController::class, 'update'])->name('vendors.update');
        Route::delete('vendors/{vendor}', [ServiceVendorController::class, 'destroy'])->name('vendors.destroy');

        Route::get('amc', [AmcController::class, 'index'])->name('amc.index');
        Route::get('amc/create', [AmcController::class, 'create'])->name('amc.create');
        Route::post('amc', [AmcController::class, 'store'])->name('amc.store');
        Route::get('amc/categories', [AmcController::class, 'categories'])->name('amc.categories');
        Route::get('amc/categories/create', [AmcController::class, 'createCategory'])->name('amc.categories.create');
        Route::post('amc/categories', [AmcController::class, 'storeCategory'])->name('amc.categories.store');

        Route::get('tenders/active', [TenderController::class, 'active'])->name('tenders.active');
        Route::get('tenders/draft', [TenderController::class, 'draft'])->name('tenders.draft');
        Route::get('tenders/awarded', [TenderController::class, 'awarded'])->name('tenders.awarded');
        Route::get('tenders/closed', [TenderController::class, 'closed'])->name('tenders.closed');
        Route::get('tenders/create', [TenderController::class, 'create'])->name('tenders.create');
        Route::post('tenders', [TenderController::class, 'store'])->name('tenders.store');
        Route::get('tenders/reports', [TenderController::class, 'reports'])->name('tenders.reports');
        Route::get('tenders/{tender}', [TenderController::class, 'show'])->name('tenders.show');
        Route::get('tenders/{tender}/edit', [TenderController::class, 'edit'])->name('tenders.edit');
        Route::put('tenders/{tender}', [TenderController::class, 'update'])->name('tenders.update');
        Route::post('tenders/{tender}/publish', [TenderController::class, 'publish'])->name('tenders.publish');
        Route::post('tenders/{tender}/award', [TenderController::class, 'award'])->name('tenders.award');
        Route::post('tenders/{tender}/close', [TenderController::class, 'close'])->name('tenders.close');
    });

    // Document Management. Static segments precede the {document} wildcard.
    Route::middleware('permission:documents.manage')->group(function () {
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/upload', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/categories', [DocumentController::class, 'categories'])->name('documents.categories');
        Route::post('documents/categories', [DocumentController::class, 'storeCategory'])->name('documents.categories.store');
        Route::put('documents/categories/{category}', [DocumentController::class, 'updateCategory'])->name('documents.categories.update');
        Route::delete('documents/categories/{category}', [DocumentController::class, 'destroyCategory'])->name('documents.categories.destroy');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::get('documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });

    // Reports (collection / expense / defaulter) with Excel + PDF export
    Route::get('reports/{report}', [SocietyReportController::class, 'show'])
        ->middleware('permission:reports.view')
        ->where('report', 'collection|expense|defaulter')
        ->name('reports.show');

    // Settings -> Users & Roles (society team)
    Route::middleware('permission:team.manage')->group(function () {
        Route::get('settings/users', [SocietyUserController::class, 'index'])->name('settings.users.index');
        Route::get('settings/users/create', [SocietyUserController::class, 'create'])->name('settings.users.create');
        Route::post('settings/users', [SocietyUserController::class, 'store'])->name('settings.users.store');
        Route::get('settings/users/{teamUser}/edit', [SocietyUserController::class, 'edit'])->name('settings.users.edit');
        Route::put('settings/users/{teamUser}', [SocietyUserController::class, 'update'])->name('settings.users.update');
        Route::put('settings/users/{teamUser}/status', [SocietyUserController::class, 'toggleStatus'])->name('settings.users.status');
        Route::post('settings/users/{teamUser}/resend-invitation', [SocietyUserController::class, 'resendInvitation'])->name('settings.users.resend-invitation');
    });

    // Placeholder for not-yet-built pages
    Route::get('/coming-soon/{page?}', [PlaceholderController::class, 'index'])->name('placeholder');
});

/*
|--------------------------------------------------------------------------
| Member portal (residents). Every row is checked against the signed-in
| member record, on top of the society-level route-binding scope.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:member', 'member.access'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');

    Route::get('/bills', [MemberBillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}', [MemberBillController::class, 'show'])->name('bills.show');
    Route::get('/bills/{bill}/pdf', [MemberBillController::class, 'pdf'])->name('bills.pdf');
    Route::post('/bills/{bill}/pay', [MemberBillController::class, 'pay'])->name('bills.pay');
    Route::get('/checkout/{order}', [MemberBillController::class, 'checkout'])->name('bills.checkout');

    Route::get('/payments', [MemberPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [MemberPaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/pdf', [MemberPaymentController::class, 'pdf'])->name('payments.pdf');

    Route::get('/support/create', [MemberSupportController::class, 'create'])->name('support.create');
    Route::get('/support', [MemberSupportController::class, 'index'])->name('support.index');
    Route::post('/support', [MemberSupportController::class, 'store'])->name('support.store');
    Route::get('/support/{ticket}', [MemberSupportController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [MemberSupportController::class, 'reply'])->name('support.reply');

    Route::get('/family', [MemberFamilyController::class, 'index'])->name('family.index');
    Route::get('/family/create', [MemberFamilyController::class, 'create'])->name('family.create');
    Route::post('/family', [MemberFamilyController::class, 'store'])->name('family.store');
    Route::get('/family/{familyMember}/edit', [MemberFamilyController::class, 'edit'])->name('family.edit');
    Route::put('/family/{familyMember}', [MemberFamilyController::class, 'update'])->name('family.update');
    Route::delete('/family/{familyMember}', [MemberFamilyController::class, 'destroy'])->name('family.destroy');

    Route::get('/vehicles', [MemberVehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [MemberVehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [MemberVehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{vehicle}/edit', [MemberVehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{vehicle}', [MemberVehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [MemberVehicleController::class, 'destroy'])->name('vehicles.destroy');

    Route::get('/notices', [MemberNoticeController::class, 'index'])->name('notices.index');
    Route::get('/notices/{notice}', [MemberNoticeController::class, 'show'])->name('notices.show');
    Route::post('/notices/{notice}/acknowledge', [MemberNoticeController::class, 'acknowledge'])->name('notices.acknowledge');

    Route::get('/profile', [MemberProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [MemberProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [MemberProfileController::class, 'updatePassword'])->name('profile.password');
});
