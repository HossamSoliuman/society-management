<?php

use App\Contracts\SmsGateway;
use App\Jobs\SendMaintenanceBill;
use App\Models\BillSetting;
use App\Models\ChargeHead;
use App\Models\LateFeeSetting;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\Society;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\MaintenanceBillIssued;
use App\Services\BillingService;
use App\Services\Sms\FakeSmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->society = Society::create([
        'name' => 'Green View Residency', 'prefix' => 'GVR', 'status' => 'active',
        'bank_name' => 'HDFC Bank', 'account_number' => '1234567890', 'ifsc_code' => 'HDFC0001',
    ]);
    linkSocietyAdmin($this->user, $this->society);

    BillSetting::create(['society_id' => $this->society->id, 'due_date_days' => 15, 'include_previous_dues' => true, 'auto_email_bill' => false, 'auto_sms_bill' => false, 'upi_id' => 'greenview@upi']);

    $this->maintenance = ChargeHead::factory()->create(['society_id' => $this->society->id, 'name' => 'Maintenance', 'calculation_type' => 'per_flat', 'default_amount' => 2000, 'status' => 'active', 'type' => 'recurring', 'sort_order' => 1]);
    $this->sinking = ChargeHead::factory()->create(['society_id' => $this->society->id, 'name' => 'Sinking Fund', 'calculation_type' => 'per_sqft', 'default_amount' => 1, 'status' => 'active', 'type' => 'recurring', 'sort_order' => 2]);
    Tax::factory()->create(['society_id' => $this->society->id, 'name' => 'GST', 'tax_type' => 'percentage', 'rate' => 18, 'status' => 'active', 'slab_from' => null, 'slab_to' => null]);

    $this->members = collect();
    $this->units = collect();
    foreach ([['A-101', 1000], ['A-102', 500], ['B-201', 1500]] as [$flat, $sqft]) {
        $member = Member::factory()->create(['society_id' => $this->society->id, 'flat_unit' => $flat, 'email' => strtolower(str_replace('-', '', $flat)).'@example.test', 'mobile' => '9'.random_int(100000000, 999999999)]);
        $unit = Unit::factory()->create(['society_id' => $this->society->id, 'unit_number' => $flat, 'building' => substr($flat, 0, 1), 'area_sqft' => $sqft, 'status' => 'occupied', 'member_id' => $member->id]);
        $this->members->push($member);
        $this->units->push($unit);
    }
    Unit::factory()->create(['society_id' => $this->society->id, 'unit_number' => 'C-301', 'status' => 'vacant']);
});

it('generates one bill per occupied unit with charge heads, sqft pricing and tax', function () {
    Queue::fake();

    $bills = app(BillingService::class)->generateForPeriod($this->society, 'June 2026', [$this->maintenance->id, $this->sinking->id], [
        'bill_date' => '2026-06-01', 'due_date' => '2026-06-15',
    ]);

    expect($bills)->toHaveCount(3);

    // A-101: 2000 + 1000 sqft × 1 = 3000, GST 18% = 540 → 3540
    $a101 = $bills->firstWhere('flat_number', 'A-101');
    expect((float) $a101->sub_total)->toBe(3000.0)
        ->and((float) $a101->tax_amount)->toBe(540.0)
        ->and((float) $a101->total_amount)->toBe(3540.0)
        ->and($a101->items)->toHaveCount(2)
        ->and($a101->member_name)->toBe($this->members[0]->name)
        ->and($a101->bill_number)->toStartWith('MB-');

    // A-102: 2000 + 500 = 2500 → +450 = 2950 ; B-201: 2000 + 1500 = 3500 → +630 = 4130
    expect((float) $bills->sum('total_amount'))->toBe(3540.0 + 2950.0 + 4130.0);

    // Re-running for the same month skips already billed units.
    $again = app(BillingService::class)->generateForPeriod($this->society, 'June 2026', [$this->maintenance->id]);
    expect($again)->toHaveCount(0)
        ->and(MaintenanceBill::count())->toBe(3);
});

it('carries previous outstanding dues into the next period', function () {
    $service = app(BillingService::class);
    $june = $service->generateForPeriod($this->society, 'June 2026', [$this->maintenance->id], ['bill_date' => '2026-06-01', 'due_date' => '2026-06-15']);
    $july = $service->generateForPeriod($this->society, 'July 2026', [$this->maintenance->id], ['bill_date' => '2026-07-01', 'due_date' => '2026-07-15']);

    $julyA101 = $july->firstWhere('flat_number', 'A-101');
    expect((float) $julyA101->previous_dues)->toBe((float) $june->firstWhere('flat_number', 'A-101')->total_amount)
        ->and((float) $julyA101->total_amount)->toBe(2360.0 + 2360.0);
});

it('previews, then confirms bulk generation through the screen', function () {
    Queue::fake();

    $this->actingAs($this->user)->get(route('society.billing.bills.generate'))->assertOk()->assertSee('Generate Bills');

    $payload = [
        'bill_month' => 'August 2026', 'bill_date' => '2026-08-01', 'due_date' => '2026-08-15',
        'charge_heads' => [$this->maintenance->id], 'confirm' => 0,
    ];
    $this->actingAs($this->user)->post(route('society.billing.bills.generate.store'), $payload)
        ->assertOk()
        ->assertSee('Bills to create')
        ->assertSee('Confirm &amp; Generate 3 Bill(s)', false);

    expect(MaintenanceBill::count())->toBe(0);

    $this->actingAs($this->user)->post(route('society.billing.bills.generate.store'), ['confirm' => 1] + $payload)
        ->assertRedirect(route('society.billing.bills.index', ['month' => 'August 2026']));

    expect(MaintenanceBill::where('bill_month', 'August 2026')->count())->toBe(3);
});

it('shows real KPI aggregates on the bill list', function () {
    MaintenanceBill::factory()->create(['society_id' => $this->society->id, 'status' => 'paid', 'total_amount' => 1000, 'collected_amount' => 1000, 'outstanding_amount' => 0]);
    MaintenanceBill::factory()->create(['society_id' => $this->society->id, 'status' => 'pending', 'total_amount' => 2000, 'collected_amount' => 0, 'outstanding_amount' => 2000]);
    MaintenanceBill::factory()->create(['society_id' => $this->society->id, 'status' => 'overdue', 'total_amount' => 3000, 'collected_amount' => 500, 'outstanding_amount' => 2500]);

    $this->actingAs($this->user)->get(route('society.billing.bills.index'))
        ->assertOk()
        ->assertSee('6,000')   // total amount
        ->assertSee('2,500');  // overdue outstanding
});

it('imports a CSV through preview and confirm', function () {
    Queue::fake();
    $csv = implode("\n", [
        'Flat No,Member Mobile,Bill Month,Bill Date,Due Date,Charge Head,Amount,Notes',
        'A-101,,September 2026,01/09/2026,15/09/2026,Maintenance,2500,Monthly',
        'A-101,,September 2026,01/09/2026,15/09/2026,Sinking Fund,300,',
        'Z-999,,September 2026,01/09/2026,15/09/2026,Maintenance,100,',
        'B-201,,September 2026,01/09/2026,15/09/2026,Unknown Head,100,',
    ]);
    $file = UploadedFile::fake()->createWithContent('bills.csv', $csv);

    $this->actingAs($this->user)->post(route('society.billing.bills.bulk.store'), ['file' => $file])
        ->assertRedirect(route('society.billing.bills.bulk'));

    $this->actingAs($this->user)->get(route('society.billing.bills.bulk'))
        ->assertOk()
        ->assertSee('2 valid')
        ->assertSee('2 with errors')
        ->assertSee('Flat Z-999 does not exist')
        ->assertSee('is not configured');

    $this->actingAs($this->user)->post(route('society.billing.bills.bulk.confirm'))
        ->assertRedirect(route('society.billing.bills.index'));

    $bill = MaintenanceBill::where('flat_number', 'A-101')->where('bill_month', 'September 2026')->firstOrFail();
    expect($bill->items)->toHaveCount(2)
        ->and((float) $bill->sub_total)->toBe(2800.0)
        ->and($bill->member_id)->toBe($this->members[0]->id)
        ->and($bill->unit_id)->toBe($this->units[0]->id);
});

it('marks bills overdue and applies late fees once after the grace period', function () {
    LateFeeSetting::create(['society_id' => $this->society->id, 'enable_late_fee' => true, 'grace_period_days' => 5, 'late_fee_type' => 'percentage', 'late_fee_percent' => 10, 'max_late_fee_cap' => 500]);
    $bill = MaintenanceBill::factory()->create(['society_id' => $this->society->id, 'status' => 'pending', 'total_amount' => 2000, 'collected_amount' => 0, 'outstanding_amount' => 2000, 'late_fee' => 0, 'due_date' => Carbon::today()->addDays(2), 'bill_date' => Carbon::today()]);

    $this->artisan('bills:mark-overdue')->assertSuccessful();
    expect($bill->fresh()->status)->toBe('pending');

    $this->travel(3)->days();
    $this->artisan('bills:mark-overdue');
    expect($bill->fresh()->status)->toBe('overdue');

    $this->artisan('bills:apply-late-fees');
    expect((float) $bill->fresh()->late_fee)->toBe(0.0); // still inside grace

    $this->travel(6)->days();
    $this->artisan('bills:apply-late-fees')->assertSuccessful();
    $bill->refresh();
    expect((float) $bill->late_fee)->toBe(200.0)
        ->and((float) $bill->total_amount)->toBe(2200.0)
        ->and((float) $bill->outstanding_amount)->toBe(2200.0)
        ->and($bill->late_fee_applied_at)->not->toBeNull();

    $this->travel(30)->days();
    $this->artisan('bills:apply-late-fees');
    expect((float) $bill->fresh()->late_fee)->toBe(200.0); // never twice
});

it('downloads the bill as a PDF with society bank details', function () {
    $bill = app(BillingService::class)->generateForPeriod($this->society, 'June 2026', [$this->maintenance->id])->first();

    $response = $this->actingAs($this->user)->get(route('society.billing.bills.pdf', $bill));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf')
        ->and($response->headers->get('content-disposition'))->toContain('.pdf');

    $this->actingAs($this->user)->get(route('society.billing.bills.show', $bill))
        ->assertOk()
        ->assertSee('greenview@upi')
        ->assertSee('HDFC Bank')
        ->assertDontSee('Ramesh Sharma');
});

it('emails the bill with a PDF attachment and texts the member', function () {
    Notification::fake();
    $this->app->instance(SmsGateway::class, new FakeSmsGateway);

    $bill = app(BillingService::class)->generateForPeriod($this->society, 'June 2026', [$this->maintenance->id], [
        'unit_ids' => [$this->units[0]->id], 'send_email' => true, 'send_sms' => true,
    ])->first();

    (new SendMaintenanceBill($bill))->handle();

    Notification::assertSentOnDemand(MaintenanceBillIssued::class, function ($notification, $channels, $notifiable) use ($bill) {
        return $notifiable->routes['mail'] === $this->members[0]->email
            && $notification->bill->is($bill)
            && in_array('mail', $channels, true);
    });
    expect($bill->fresh()->delivered_at)->not->toBeNull();
});

it('renders the bill email with the configured template and a PDF attachment', function () {
    $bill = app(BillingService::class)->generateForPeriod($this->society, 'June 2026', [$this->maintenance->id], ['unit_ids' => [$this->units[0]->id]])->first();

    $mail = (new MaintenanceBillIssued($bill))->toMail(Notification::route('mail', 'x@example.test'));

    expect($mail->subject)->toContain($bill->bill_number)
        ->and($mail->rawAttachments)->toHaveCount(1)
        ->and($mail->rawAttachments[0]['name'])->toEndWith('.pdf')
        ->and($mail->introLines[0])->toContain($bill->bill_number);
});

it('saves notification settings and sends reminders on the configured days', function () {
    Queue::fake();

    $this->actingAs($this->user)->get(route('society.billing.settings.notifications'))->assertOk()->assertSee('Reminder Schedule');

    $this->actingAs($this->user)->put(route('society.billing.settings.notifications.update'), [
        'reminder_days_before_due' => '3',
        'reminder_days_after_due' => '2',
        'events' => [
            'payment_reminder' => ['email' => 1, 'template' => 'Hi {member_name}, {bill_no} is due {due_date}.'],
            'overdue_reminder' => ['sms' => 1],
        ],
    ])->assertRedirect(route('society.billing.settings.notifications'));

    $settings = BillSetting::where('society_id', $this->society->id)->first();
    expect($settings->reminderDaysBeforeDue())->toBe([3])
        ->and($settings->reminderDaysAfterDue())->toBe([2])
        ->and($settings->notificationEvent('payment_reminder')['channels']['email'])->toBeTrue()
        ->and($settings->notificationEvent('payment_reminder')['template'])->toContain('is due');

    $dueSoon = MaintenanceBill::factory()->create(['society_id' => $this->society->id, 'status' => 'pending', 'outstanding_amount' => 500, 'due_date' => Carbon::today()->addDays(3)]);
    $overdue = MaintenanceBill::factory()->create(['society_id' => $this->society->id, 'status' => 'overdue', 'outstanding_amount' => 500, 'due_date' => Carbon::today()->subDays(2)]);
    MaintenanceBill::factory()->create(['society_id' => $this->society->id, 'status' => 'pending', 'outstanding_amount' => 500, 'due_date' => Carbon::today()->addDays(10)]);

    $this->artisan('bills:send-reminders')->assertSuccessful();

    Queue::assertPushed(SendMaintenanceBill::class, 2);
    Queue::assertPushed(SendMaintenanceBill::class, fn ($job) => $job->bill->is($dueSoon) && $job->event === 'payment_reminder');
    Queue::assertPushed(SendMaintenanceBill::class, fn ($job) => $job->bill->is($overdue) && $job->event === 'overdue_reminder');
});
