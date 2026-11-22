@extends('marketing.layout')

@section('title', 'Housing society management software')
@section('description', 'Maintenance billing, receipts, society accounts, visitor logs, amenity bookings and a resident portal with online payments, in one system for housing societies and RWAs.')

@section('content')
@php
    $floors = range(8, 1);
    $flatAmounts = [1 => 3150, 2 => 2450, 3 => 2450, 4 => 3150];
    $billed = count($floors) * array_sum($flatAmounts);
@endphp

<section class="mk-dusk on-dusk mk-hero" aria-labelledby="hero-title">
    <div class="mk-container mk-hero-grid">
        <div class="mk-hero-copy">
            <h1 class="mk-display" id="hero-title">Every flat paid up. Every rupee <em>accounted for.</em></h1>
            <p class="mk-lead">Maintenance bills, receipts, society accounts, the gate register and resident complaints in one place, with a portal where every resident pays online.</p>
            <div class="mk-hero-actions">
                <a href="{{ route('demo.create') }}" class="mk-btn mk-btn-primary">Request a demo <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                <a href="{{ route('features') }}" class="mk-btn mk-btn-ghost">See all features</a>
            </div>
            <p class="mk-hero-note"><i class="fas fa-building" aria-hidden="true"></i> For cooperative housing societies, RWAs and apartment associations</p>
        </div>

        <div class="mk-scene" data-tower role="img" aria-label="Illustration: a residential tower whose windows light up as each flat pays its maintenance, with a running total of the month's collection">
            <div class="mk-skyline s1" aria-hidden="true"></div>
            <div class="mk-skyline s2" aria-hidden="true"></div>
            <div class="mk-skyline s3" aria-hidden="true"></div>

            <div class="mk-tower" aria-hidden="true">
                <div class="mk-roof">
                    <span class="mk-tank"></span>
                    <span class="mk-liftroom"></span>
                    <span class="mk-tank"></span>
                </div>
                <div class="mk-floors">
                    @foreach($floors as $floor)
                        @foreach($flatAmounts as $flat => $amount)
                            @php
                                $flatNumber = 'B-'.$floor.'0'.$flat;
                                $isPaid = ($floor * 7 + $flat * 3) % 5 < 3;
                            @endphp
                            <div class="mk-flat {{ $isPaid ? 'is-paid' : '' }}" data-flat="{{ $flatNumber }}" data-amount="{{ $amount }}"><span>{{ $flatNumber }}</span></div>
                        @endforeach
                    @endforeach
                </div>
                <div class="mk-base">
                    <span class="mk-sign">Gulmohar Heights · Wing B</span>
                </div>
            </div>

            <div class="mk-collect" aria-hidden="true">
                <div class="mk-collect-top">
                    <span class="mk-collect-label">Maintenance collected</span>
                    <span class="mk-collect-month">{{ now()->format('M Y') }}</span>
                </div>
                <div class="mk-collect-amount" data-collected>₹0</div>
                <div class="mk-collect-of">of ₹{{ number_format($billed) }} billed</div>
                <div class="mk-bar"><span data-bar></span></div>
                <div class="mk-collect-flats" data-count></div>
            </div>

            <div class="mk-toast" data-toast aria-hidden="true">
                <span class="mk-toast-icon"><i class="fas fa-indian-rupee-sign"></i></span>
                <span>
                    <span class="mk-toast-title" data-toast-title></span>
                    <span class="mk-toast-meta" data-toast-meta></span>
                </span>
            </div>
        </div>
    </div>
</section>

<section class="mk-section mk-section-white" id="billing" aria-labelledby="billing-title">
    <div class="mk-container mk-cycle">
        <div>
            <div class="mk-section-head" style="margin-bottom: 36px;">
                <span class="mk-label">Maintenance billing</span>
                <h2 class="mk-h2" id="billing-title">The monthly billing routine, <em>handled</em></h2>
                <p class="mk-lead">Set up your charge heads once. After that, every month runs the same way for every flat.</p>
            </div>
            <ol class="mk-steps">
                <li>
                    <span class="mk-when">Bill date</span>
                    <h3 class="mk-h3">Bills go out for every flat</h3>
                    <p>One run creates the month's bills with your charge heads, taxes and numbering, and emails each resident.</p>
                </li>
                <li>
                    <span class="mk-when">Before and after the due date</span>
                    <h3 class="mk-h3">Reminders go to flats with dues</h3>
                    <p>Residents are reminded of the amount and due date on the schedule you choose, without anyone making calls.</p>
                </li>
                <li>
                    <span class="mk-when">As residents pay</span>
                    <h3 class="mk-h3">Payments arrive with receipts</h3>
                    <p>Residents pay online from the portal; cash and cheques are recorded at the office. Either way the receipt is emailed and the books are updated.</p>
                </li>
                <li>
                    <span class="mk-when">After the due date</span>
                    <h3 class="mk-h3">Late fees and the defaulter list update</h3>
                    <p>Penalties apply on the rules you set, and the pending dues report shows who still owes what.</p>
                </li>
            </ol>
        </div>
        <div>
            @include('marketing.partials.shot', ['src' => 'billing.webp', 'path' => 'society/billing/bills', 'alt' => 'Maintenance bill list with paid, pending and overdue totals for the month'])
        </div>
    </div>
</section>

<section class="mk-section" id="roles" aria-labelledby="roles-title">
    <div class="mk-container">
        <div class="mk-section-head">
            <span class="mk-label">Who uses it</span>
            <h2 class="mk-h2" id="roles-title">One system for the office, the treasurer, the gate and <em>every flat</em></h2>
        </div>

        <div data-tabs>
            <div class="mk-tabs" role="tablist" aria-label="Roles">
                <button type="button" class="mk-tab" role="tab" id="tab-office" aria-controls="panel-office" aria-selected="true"><i class="fas fa-briefcase" aria-hidden="true"></i> Committee & office</button>
                <button type="button" class="mk-tab" role="tab" id="tab-treasurer" aria-controls="panel-treasurer" aria-selected="false" tabindex="-1"><i class="fas fa-scale-balanced" aria-hidden="true"></i> Treasurer</button>
                <button type="button" class="mk-tab" role="tab" id="tab-gate" aria-controls="panel-gate" aria-selected="false" tabindex="-1"><i class="fas fa-shield-halved" aria-hidden="true"></i> Security gate</button>
                <button type="button" class="mk-tab" role="tab" id="tab-residents" aria-controls="panel-residents" aria-selected="false" tabindex="-1"><i class="fas fa-house-user" aria-hidden="true"></i> Residents</button>
            </div>

            <div class="mk-panel" role="tabpanel" id="panel-office" aria-labelledby="tab-office">
                <div>
                    <h3 class="mk-h3">The whole society on one screen</h3>
                    <p>Collections, pending dues, occupancy and open complaints are on the dashboard the moment the office opens.</p>
                    <ul class="mk-checks">
                        <li><i class="fas fa-check" aria-hidden="true"></i>Members, family, owners, tenants and occupancy for every flat</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Join requests from new residents through your society's link</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Notices to residents, with a record of who has read them</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Separate logins for manager, accountant and staff with only the access they need</li>
                    </ul>
                </div>
                @include('marketing.partials.shot', ['src' => 'dashboard.webp', 'path' => 'society/dashboard', 'alt' => 'Society dashboard showing members, units, collections, pending dues and a monthly revenue chart'])
            </div>

            <div class="mk-panel" role="tabpanel" id="panel-treasurer" aria-labelledby="tab-treasurer" hidden>
                <div>
                    <h3 class="mk-h3">Books that are ready when the auditor asks</h3>
                    <p>Collections and expenses post to the ledger as they are recorded, so the accounts stay current without double entry.</p>
                    <ul class="mk-checks">
                        <li><i class="fas fa-check" aria-hidden="true"></i>Chart of accounts with opening balances</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Receipts, payments and journal entries</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Bank reconciliation against your statement</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Trial balance, profit &amp; loss and balance sheet</li>
                    </ul>
                </div>
                @include('marketing.partials.shot', ['src' => 'accounting.webp', 'path' => 'society/accounting', 'alt' => 'Accounting dashboard with balances, bank accounts, receivables and a cash flow chart'])
            </div>

            <div class="mk-panel" role="tabpanel" id="panel-gate" aria-labelledby="tab-gate" hidden>
                <div>
                    <h3 class="mk-h3">A gate register that never runs out of pages</h3>
                    <p>Guards check visitors in and out from any phone or desk, and the office can see who is inside right now.</p>
                    <ul class="mk-checks">
                        <li><i class="fas fa-check" aria-hidden="true"></i>Visitor entries with purpose, flat, people and vehicle number</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Guests pre-approved by residents enter with a 6-digit code</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Vehicles registered per flat and parking slot allotments</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Staff records and daily attendance</li>
                    </ul>
                </div>
                @include('marketing.partials.shot', ['src' => 'visitors.webp', 'path' => 'society/visitors', 'alt' => 'Visitor log listing visitors inside now, with check-out buttons and pass code check-in', 'height' => 1050])
            </div>

            <div class="mk-panel" role="tabpanel" id="panel-residents" aria-labelledby="tab-residents" hidden>
                <div>
                    <h3 class="mk-h3">A portal every resident can use</h3>
                    <p>Residents get their own login by email. Everything about their flat is in one place, on any phone.</p>
                    <ul class="mk-checks">
                        <li><i class="fas fa-check" aria-hidden="true"></i>See bills, pay dues online and keep every receipt</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Book the clubhouse, courts or guest room</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Pre-approve guests and register family members and vehicles</li>
                        <li><i class="fas fa-check" aria-hidden="true"></i>Raise complaints and follow them until they are closed</li>
                    </ul>
                </div>
                @include('marketing.partials.phone')
            </div>
        </div>
    </div>
</section>

<section class="mk-section mk-section-white" aria-labelledby="modules-title">
    <div class="mk-container">
        <div class="mk-section-head">
            <span class="mk-label">Also included</span>
            <h2 class="mk-h2" id="modules-title">Everything else the society office <em>keeps track of</em></h2>
        </div>
        <div class="mk-modules">
            @foreach([
                ['fa-file-signature', 'Tenants & agreements', 'Rental agreements on file, with alerts before they expire.'],
                ['fa-calendar-check', 'Amenity booking', 'Free or paid slots. Paid bookings are added to the resident\'s bill.'],
                ['fa-headset', 'Complaint desk', 'Residents raise issues; the office assigns, tracks and closes them.'],
                ['fa-bullhorn', 'Notices', 'Publish notices and announcements, now or on a schedule.'],
                ['fa-receipt', 'Expenses & vendors', 'Expenses by category and vendor, against your budget.'],
                ['fa-toolbox', 'Assets', 'Lifts, pumps and generators on one register.'],
                ['fa-rotate', 'AMC & renewals', 'Service contracts tracked, with alerts before they lapse.'],
                ['fa-gavel', 'Tenders', 'Draft, publish and award work to vendors.'],
                ['fa-square-parking', 'Parking', 'Slots, allotments and the vehicles parked in them.'],
                ['fa-folder-open', 'Documents', 'Bye-laws, minutes and certificates in one library.'],
                ['fa-chart-column', 'Reports', 'Collection, expense and defaulter reports in PDF or Excel.'],
                ['fa-clock-rotate-left', 'Activity log', 'Every change recorded with who made it and when.'],
            ] as [$icon, $title, $text])
                <div class="mk-module">
                    <div class="mk-module-icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></div>
                    <h3>{{ $title }}</h3>
                    <p>{{ $text }}</p>
                </div>
            @endforeach
        </div>
        <p style="margin-top: 32px;"><a href="{{ route('features') }}" class="mk-link">See every feature <i class="fas fa-arrow-right" aria-hidden="true"></i></a></p>
    </div>
</section>

<section class="mk-section" aria-labelledby="start-title">
    <div class="mk-container">
        <div class="mk-section-head">
            <span class="mk-label">Getting started</span>
            <h2 class="mk-h2" id="start-title">From first call to <em>first bills</em></h2>
        </div>
        <ol class="mk-start">
            <li>
                <h3 class="mk-h3">Request a demo</h3>
                <p>Tell us about your society and we arrange a walkthrough for your committee.</p>
            </li>
            <li>
                <h3 class="mk-h3">Your society is set up</h3>
                <p>We add your wings, flats, members and charge heads with your committee.</p>
            </li>
            <li>
                <h3 class="mk-h3">Residents get their logins</h3>
                <p>Each resident is invited by email to the portal for their flat.</p>
            </li>
            <li>
                <h3 class="mk-h3">Your first bills go out</h3>
                <p>Generate the month's bills and start collecting online.</p>
            </li>
        </ol>
    </div>
</section>

@if($plans->isNotEmpty())
<section class="mk-section mk-section-white" aria-labelledby="pricing-title">
    <div class="mk-container">
        <div class="mk-section-head">
            <span class="mk-label">Pricing</span>
            <h2 class="mk-h2" id="pricing-title">Priced by the size of <em>your society</em></h2>
        </div>
        @include('marketing.partials.plans', ['plans' => $plans])
        <p style="margin-top: 32px;"><a href="{{ route('pricing') }}" class="mk-link">Compare plans <i class="fas fa-arrow-right" aria-hidden="true"></i></a></p>
    </div>
</section>
@endif

<section class="mk-section" aria-labelledby="faq-title">
    <div class="mk-container mk-faq">
        <div class="mk-section-head">
            <span class="mk-label">Questions</span>
            <h2 class="mk-h2" id="faq-title">What committees ask us</h2>
        </div>
        <div class="mk-faq-list">
            <details>
                <summary>Can residents pay maintenance online?</summary>
                <p>Yes. Residents pay from the portal, the payment is matched to their bill and a receipt is emailed. Cash and cheque payments are recorded by the office and get the same receipt.</p>
            </details>
            <details>
                <summary>Do we still need separate accounting software?</summary>
                <p>Collections and expenses post to the society's ledger as they are recorded. Journal entries, bank reconciliation, trial balance, profit &amp; loss and balance sheet are built in.</p>
            </details>
            <details>
                <summary>Who in the committee can see what?</summary>
                <p>Everyone signs in with their own login and role, such as admin, manager, accountant or staff. The role decides which modules they can open.</p>
            </details>
            <details>
                <summary>Can we bring in our existing bills?</summary>
                <p>Yes. Bills can be uploaded from an Excel sheet, and we add your wings, flats and members during setup.</p>
            </details>
            <details>
                <summary>Is our society's data kept apart from other societies?</summary>
                <p>Yes. Each society's members, bills and accounts are visible only to that society's own logins.</p>
            </details>
        </div>
    </div>
</section>

@include('marketing.partials.cta')
@endsection
