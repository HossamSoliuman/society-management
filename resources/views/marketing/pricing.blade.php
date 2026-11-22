@extends('marketing.layout')

@section('title', 'Pricing')
@section('description', 'Plans for housing societies of every size, priced by the number of flats. Every plan includes billing, accounts, visitor management and the resident portal.')

@section('content')
@php
    $modules = \App\Models\SubscriptionPlan::MODULES;
    $everyModuleShared = $differingModules === [];
@endphp

<section class="mk-dusk on-dusk mk-page-hero" aria-labelledby="pricing-title">
    <div class="mk-container">
        <h1 class="mk-display" id="pricing-title">
            @if($everyModuleShared)
                Priced by the number of flats, <em>not by features</em>
            @else
                A plan for every size of <em>society</em>
            @endif
        </h1>
        <p class="mk-lead">Every plan comes with the resident portal and online payments. Choose the one that covers the number of flats in your society.</p>
    </div>
</section>

<section class="mk-section" aria-label="Plans">
    <div class="mk-container">
        @if($plans->isNotEmpty())
            @include('marketing.partials.plans', ['plans' => $plans])
        @else
            <div class="mk-included" style="margin-top: 0; display: block; text-align: center;">
                <h2 class="mk-h3">Plans are being updated</h2>
                <p style="margin: 10px 0 24px; color: var(--muted);">Tell us about your society and we will send you a quote.</p>
                <a href="{{ route('demo.create') }}" class="mk-btn mk-btn-primary">Request a quote</a>
            </div>
        @endif

        @if($sharedModules !== [])
            <div class="mk-included">
                <div>
                    <span class="mk-label">In every plan</span>
                    <h2 class="mk-h2" style="font-size: clamp(1.6rem, 2.4vw, 2.1rem); margin-top: 14px;">{{ $everyModuleShared ? 'The same software for a small building and a large township' : 'Included whichever plan you choose' }}</h2>
                </div>
                <ul class="mk-included-list">
                    @foreach($sharedModules as $key)
                        <li>
                            <i class="fas {{ $modules[$key]['icon'] }}" aria-hidden="true"></i>
                            <div>
                                <strong>{{ $modules[$key]['name'] }}</strong>
                                <span>{{ $modules[$key]['description'] }}</span>
                            </div>
                        </li>
                    @endforeach
                    <li>
                        <i class="fas fa-mobile-screen" aria-hidden="true"></i>
                        <div>
                            <strong>Resident portal</strong>
                            <span>Bills, online payments, bookings and requests</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-scale-balanced" aria-hidden="true"></i>
                        <div>
                            <strong>Society accounting</strong>
                            <span>Ledger, bank reconciliation and final accounts</span>
                        </div>
                    </li>
                </ul>
            </div>
        @endif

        @if($differingModules !== [] && $plans->isNotEmpty())
            <div style="margin-top: 56px; overflow-x: auto;">
                <h2 class="mk-h3">Compare modules</h2>
                <table class="mk-compare">
                    <thead>
                        <tr>
                            <th scope="col">Module</th>
                            @foreach($plans as $plan)
                                <th scope="col">{{ $plan->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($differingModules as $key)
                            <tr>
                                <th scope="row" style="font-weight: 600; color: var(--ink);">{{ $modules[$key]['name'] }}</th>
                                @foreach($plans as $plan)
                                    <td>
                                        @if(in_array($key, $plan->enabledModuleKeys(), true))
                                            <i class="fas fa-check" aria-label="Included"></i>
                                        @else
                                            <i class="fas fa-minus" aria-label="Not included"></i>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

<section class="mk-section mk-section-white" aria-labelledby="pricing-faq-title">
    <div class="mk-container mk-faq">
        <div class="mk-section-head">
            <span class="mk-label">Questions</span>
            <h2 class="mk-h2" id="pricing-faq-title">About plans and billing</h2>
        </div>
        <div class="mk-faq-list">
            <details>
                <summary>How do we choose a plan?</summary>
                <p>Pick the plan whose flat limit covers your society. If you are between two sizes, tell us when you request a demo and we will suggest the one that fits.</p>
            </details>
            <details>
                <summary>What does the per-flat figure mean?</summary>
                <p>It is the plan price spread across the plan's full flat limit and the months it covers. If your society has fewer flats than the limit, your cost per flat is higher.</p>
            </details>
            <details>
                <summary>How are we billed?</summary>
                <p>Each plan is billed for the period shown on it, and your society receives an invoice for every period from inside the app.</p>
            </details>
            <details>
                <summary>Can we move to another plan later?</summary>
                <p>Yes. When your society grows past its flat limit, or you need a different plan, we move your subscription over.</p>
            </details>
            <details>
                <summary>Is setup included?</summary>
                <p>Yes. We set up your wings, flats, members and charge heads with your committee before the first bills go out.</p>
            </details>
        </div>
    </div>
</section>

@include('marketing.partials.cta')
@endsection
