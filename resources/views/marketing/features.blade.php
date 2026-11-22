@extends('marketing.layout')

@section('title', 'Features')
@section('description', 'Maintenance billing, online collections, society accounting, visitor management, amenity booking, complaints, notices and a resident portal for housing societies.')

@section('content')
@php
    $areas = [
        'billing' => [
            'icon' => 'fa-file-invoice',
            'nav' => 'Billing',
            'label' => 'Billing & collections',
            'title' => 'Bills out on time, dues collected without chasing',
            'intro' => 'Set your charges once and every month\'s bills, reminders, late fees and receipts follow from them.',
            'shot' => ['src' => 'billing.webp', 'path' => 'society/billing/bills', 'alt' => 'Maintenance bill list with paid, pending and overdue totals'],
            'caps' => [
                ['fa-layer-group', 'Bills for every flat in one run', 'Generate the month\'s bills for all flats, or raise a one-off bill for a single flat.'],
                ['fa-list-check', 'Charge heads', 'Maintenance, sinking fund, repair fund, water, parking or any head your society uses.'],
                ['fa-percent', 'Taxes', 'Taxes applied per charge head, shown line by line on the bill.'],
                ['fa-hourglass-end', 'Late fees and penalties', 'Applied automatically after the due date on the rules you set.'],
                ['fa-bell', 'Reminders', 'Sent before and after the due date on the schedule you choose.'],
                ['fa-file-excel', 'Excel upload', 'Bring in existing bills from a spreadsheet when you move over.'],
                ['fa-credit-card', 'Online payments', 'Residents pay from the portal and the payment is matched to their bill.'],
                ['fa-cash-register', 'Office collections', 'Record cash, cheque and bank transfers, with a receipt for each.'],
                ['fa-hashtag', 'Your numbering', 'Your own series for bills and receipts, restarting each financial year.'],
                ['fa-user-clock', 'Pending dues', 'See who owes what, flat by flat, and how long it has been due.'],
            ],
        ],
        'accounting' => [
            'icon' => 'fa-scale-balanced',
            'nav' => 'Accounts',
            'label' => 'Accounts & expenses',
            'title' => 'Society accounts that keep themselves current',
            'intro' => 'Collections and expenses post to the ledger the moment they are recorded, so the treasurer starts each month with books that already balance.',
            'shot' => ['src' => 'accounting.webp', 'path' => 'society/accounting', 'alt' => 'Accounting dashboard with balances, bank accounts and cash flow'],
            'caps' => [
                ['fa-sitemap', 'Chart of accounts', 'Ready-made account groups you can extend, with opening balances.'],
                ['fa-arrows-rotate', 'Automatic posting', 'Collections and expenses reach the ledger without double entry.'],
                ['fa-book', 'Receipts, payments and journals', 'Record any other entry the treasurer needs.'],
                ['fa-building-columns', 'Bank reconciliation', 'Import the bank statement and match each line to the books.'],
                ['fa-scale-balanced', 'Final accounts', 'Trial balance, profit & loss and balance sheet, compared with an earlier period.'],
                ['fa-receipt', 'Expenses and budgets', 'Expenses by category and vendor, tracked against the budget.'],
            ],
        ],
        'residents' => [
            'icon' => 'fa-users',
            'nav' => 'Residents',
            'label' => 'Residents & flats',
            'title' => 'Know who lives in every flat',
            'intro' => 'Owners, tenants, family members and vehicles, kept up to date by the office and by residents themselves.',
            'shot' => ['src' => 'dashboard.webp', 'path' => 'society/dashboard', 'alt' => 'Society dashboard with members, units, collections and recent activity'],
            'caps' => [
                ['fa-users', 'Members and family', 'Owners, family members and contact details for each flat.'],
                ['fa-building', 'Units and occupancy', 'Which flats are owner-occupied, rented or vacant.'],
                ['fa-file-signature', 'Tenants and agreements', 'Rental agreements on file, with alerts before they expire.'],
                ['fa-link', 'Join requests', 'New residents apply through your society\'s link and the office approves them.'],
                ['fa-envelope-open-text', 'Portal invitations', 'Residents get an email to set up their own login.'],
                ['fa-car', 'Vehicles', 'Vehicles registered against each flat.'],
            ],
        ],
        'gate' => [
            'icon' => 'fa-shield-halved',
            'nav' => 'Gate',
            'label' => 'Gate & security',
            'title' => 'The gate register, without the register',
            'intro' => 'Guards log every entry from a phone or desk, and the office can see who is inside right now.',
            'shot' => ['src' => 'visitors.webp', 'path' => 'society/visitors', 'alt' => 'Visitor log with visitors inside now and pass code check-in', 'height' => 1050],
            'caps' => [
                ['fa-door-open', 'Check-in and check-out', 'Name, phone, purpose, flat, number of people and vehicle.'],
                ['fa-key', 'Pre-approved guests', 'Residents pre-approve guests, who enter with a 6-digit code.'],
                ['fa-box', 'Deliveries', 'Deliveries logged on their own, so the day\'s count is clear.'],
                ['fa-eye', 'Inside now', 'A live list of visitors who have not checked out yet.'],
                ['fa-square-parking', 'Parking', 'Parking slots and their allotments by flat.'],
                ['fa-id-badge', 'Staff and attendance', 'Guards, housekeeping and other staff, with daily attendance.'],
            ],
        ],
        'facilities' => [
            'icon' => 'fa-calendar-check',
            'nav' => 'Facilities',
            'label' => 'Facilities & upkeep',
            'title' => 'Clubhouse bookings and contracts in one calendar',
            'intro' => 'Residents book amenities themselves, and the office keeps assets, service contracts and tenders on record.',
            'shot' => ['src' => 'amenities.webp', 'path' => 'society/amenities/bookings', 'alt' => 'Amenity bookings for the party hall, clubhouse, courts and guest room'],
            'caps' => [
                ['fa-calendar-check', 'Amenity booking', 'Clubhouse, party hall, courts and guest rooms, by time slot.'],
                ['fa-indian-rupee-sign', 'Paid bookings billed', 'Charges for paid slots are added to the resident\'s bill.'],
                ['fa-circle-check', 'Approvals', 'Bookings that need approval wait for the office.'],
                ['fa-toolbox', 'Asset register', 'Lifts, pumps, generators and other assets by category, with photos.'],
                ['fa-rotate', 'AMC & renewals', 'Service contracts tracked, with alerts before they lapse.'],
                ['fa-gavel', 'Tenders and vendors', 'Draft, publish and award tenders, and keep vendors in one directory.'],
            ],
        ],
        'portal' => [
            'icon' => 'fa-mobile-screen',
            'nav' => 'Resident portal',
            'label' => 'Resident portal',
            'title' => 'Everything about their flat, on any phone',
            'intro' => 'Each resident signs in to their own portal to pay, book, pre-approve guests and raise requests, so fewer of them need to visit the office.',
            'phone' => true,
            'caps' => [
                ['fa-file-invoice', 'Bills and receipts', 'Every bill, payment and receipt for their flat.'],
                ['fa-credit-card', 'Pay dues online', 'Outstanding dues paid in a few taps.'],
                ['fa-calendar-check', 'Book amenities', 'Available slots with the charge shown upfront.'],
                ['fa-user-check', 'Pre-approve visitors', 'A pass code for guests to show at the gate.'],
                ['fa-people-roof', 'Family and vehicles', 'Household members and vehicles kept up to date.'],
                ['fa-headset', 'Complaints and requests', 'Raised from the portal and followed until closed.'],
            ],
        ],
        'communication' => [
            'icon' => 'fa-bullhorn',
            'nav' => 'Notices',
            'label' => 'Notices & helpdesk',
            'title' => 'Fewer messages lost in the group chat',
            'intro' => 'Notices, announcements and complaints have one home, with a record of who read what and what was done.',
            'caps' => [
                ['fa-bullhorn', 'Notices with read tracking', 'Publish notices by priority and see who has acknowledged them.'],
                ['fa-clock', 'Scheduled announcements', 'Write now and send at the time you choose.'],
                ['fa-headset', 'Complaint desk', 'Residents raise complaints; the office assigns, tracks and closes them.'],
                ['fa-folder-open', 'Document library', 'Bye-laws, minutes, certificates and circulars in one place.'],
                ['fa-bell', 'Notifications', 'In-app and email alerts for bills, receipts, bookings and complaints.'],
                ['fa-life-ring', 'Priority support', 'Raise a ticket with our team from inside the app.'],
            ],
        ],
        'control' => [
            'icon' => 'fa-user-shield',
            'nav' => 'Reports & access',
            'label' => 'Reports & access',
            'title' => 'Clear numbers for the committee, the right access for everyone',
            'intro' => 'Reports for meetings and audits, and logins that only open what each person needs.',
            'caps' => [
                ['fa-chart-column', 'Reports', 'Collection, expense and defaulter reports for any period.'],
                ['fa-file-export', 'PDF and Excel export', 'Lists and reports ready for meetings and auditors.'],
                ['fa-user-shield', 'Roles and permissions', 'Admin, manager, accountant and staff roles with their own access.'],
                ['fa-clock-rotate-left', 'Activity log', 'Every change recorded with who made it and when.'],
                ['fa-sliders', 'Society settings', 'Financial year, bill rules and numbering, set once.'],
                ['fa-lock', 'Separate data per society', 'Your records are visible only to your society\'s logins.'],
            ],
        ],
    ];
@endphp

<section class="mk-dusk on-dusk mk-page-hero" aria-labelledby="features-title">
    <div class="mk-container">
        <h1 class="mk-display" id="features-title">Everything the society office does, <em>in one place</em></h1>
        <p class="mk-lead">From the first bill of the month to the balance sheet at year end, for the committee, the treasurer, the gate and every resident.</p>
        <nav class="mk-jump" aria-label="Feature areas">
            @foreach($areas as $id => $area)
                <a href="#{{ $id }}"><i class="fas {{ $area['icon'] }}" aria-hidden="true"></i>{{ $area['nav'] }}</a>
            @endforeach
        </nav>
    </div>
</section>

@foreach($areas as $id => $area)
    <section class="mk-area" id="{{ $id }}" aria-labelledby="{{ $id }}-title">
        <div class="mk-container mk-area-grid">
            <div class="mk-area-intro">
                <span class="mk-label">{{ $area['label'] }}</span>
                <h2 class="mk-h2" id="{{ $id }}-title">{{ $area['title'] }}</h2>
                <p>{{ $area['intro'] }}</p>
            </div>
            <div>
                @if(isset($area['shot']))
                    <div class="mk-area-shot">
                        @include('marketing.partials.shot', $area['shot'])
                    </div>
                @elseif(! empty($area['phone']))
                    <div class="mk-area-shot">
                        @include('marketing.partials.phone')
                    </div>
                @endif
                <ul class="mk-caps">
                    @foreach($area['caps'] as [$icon, $title, $text])
                        <li>
                            <span class="mk-module-icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></span>
                            <div>
                                <h3>{{ $title }}</h3>
                                <p>{{ $text }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
@endforeach

@include('marketing.partials.cta')
@endsection
