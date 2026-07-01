<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Society\BillSettingController;
use App\Http\Controllers\Society\BulkUploadController;
use App\Http\Controllers\Society\ChargeHeadController;
use App\Http\Controllers\Society\DashboardController as SocietyDashboardController;
use App\Http\Controllers\Society\MemberController;
use App\Http\Controllers\Society\NumberingSeriesController;
use App\Http\Controllers\Society\PlaceholderController;
use App\Http\Controllers\Society\ProfileController as SocietyProfileController;
use App\Http\Controllers\Society\TaxController;
use App\Http\Controllers\Society\UnitController;
use App\Http\Controllers\SuperAdmin\AccountController;
use App\Http\Controllers\SuperAdmin\ActivityLogController;
use App\Http\Controllers\SuperAdmin\BillingController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\MasterController;
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
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->prefix('superadmin')->name('superadmin.')->group(function () {

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
    Route::get('/subscription/renewals', [SubscriptionController::class, 'renewals'])->name('subscription.renewals');

    Route::get('/billing/overview', [BillingController::class, 'overview'])->name('billing.overview');
    Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
    Route::get('/billing/payments', [BillingController::class, 'payments'])->name('billing.payments');
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

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/society', [ReportController::class, 'societyReport'])->name('reports.society');
    Route::get('/reports/revenue', [ReportController::class, 'revenueReport'])->name('reports.revenue');
    Route::get('/reports/subscription', [ReportController::class, 'subscriptionReport'])->name('reports.subscription');
    Route::get('/reports/payment', [ReportController::class, 'paymentReport'])->name('reports.payment');

    Route::get('/notifications/announcements', [NotificationController::class, 'announcements'])->name('notification.announcements');
    Route::get('/notifications/announcements/create', [NotificationController::class, 'createAnnouncement'])->name('notification.announcements.create');
    Route::post('/notifications/announcements', [NotificationController::class, 'storeAnnouncement'])->name('notification.announcements.store');
    Route::get('/notifications/renewals', [NotificationController::class, 'renewalAlerts'])->name('notification.renewals');

    Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
    Route::put('/tickets/{ticket}/status', [SupportTicketController::class, 'updateStatus'])->name('tickets.status');

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

Route::middleware(['auth'])->prefix('society')->name('society.')->group(function () {
    Route::get('/dashboard', [SocietyDashboardController::class, 'index'])->name('dashboard');

    // Society Profile
    Route::get('/profile', [SocietyProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [SocietyProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [SocietyProfileController::class, 'update'])->name('profile.update');

    // Members
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');

    // Units
    Route::get('/units', [UnitController::class, 'index'])->name('units.index');
    Route::get('/units/create', [UnitController::class, 'create'])->name('units.create');
    Route::post('/units', [UnitController::class, 'store'])->name('units.store');
    Route::get('/units/import', [UnitController::class, 'importForm'])->name('units.import');
    Route::post('/units/import', [UnitController::class, 'import'])->name('units.import.store');
    Route::get('/units/{unit}', [UnitController::class, 'show'])->name('units.show');
    Route::get('/units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
    Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
    Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

    // Maintenance Billing -> Bulk Upload (Phase 1: step 1 + upload)
    Route::get('/billing/bulk-upload', [BulkUploadController::class, 'index'])->name('billing.bulk-upload');
    Route::post('/billing/bulk-upload', [BulkUploadController::class, 'upload'])->name('billing.bulk-upload.store');
    Route::get('/billing/bulk-upload/sample', [BulkUploadController::class, 'sample'])->name('billing.bulk-upload.sample');

    // Maintenance Billing -> Bill Settings (Phase 2A)
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
    Route::get('billing/settings/numbering', [NumberingSeriesController::class, 'index'])->name('billing.settings.numbering');
    Route::post('billing/settings/numbering', [NumberingSeriesController::class, 'store'])->name('billing.settings.numbering.store');
    Route::put('billing/settings/numbering/{series}', [NumberingSeriesController::class, 'update'])->name('billing.settings.numbering.update');
    Route::delete('billing/settings/numbering/{series}', [NumberingSeriesController::class, 'destroy'])->name('billing.settings.numbering.destroy');

    // Placeholder for not-yet-built pages
    Route::get('/coming-soon/{page?}', [PlaceholderController::class, 'index'])->name('placeholder');
});
