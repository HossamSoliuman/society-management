{{-- Plan cards. Expects $plans (active SubscriptionPlan collection with modules). --}}
@php
    $moduleCount = count(\App\Models\SubscriptionPlan::MODULES);
@endphp
<div class="mk-plans">
    @foreach($plans as $plan)
        @php
            $featured = $plan->badge && in_array(strtolower($plan->badge), ['popular', 'most popular', 'recommended'], true);
            $enabled = $plan->enabledModuleKeys();
            $perUnit = $plan->monthlyCostPerUnit();
        @endphp
        <article class="mk-plan {{ $featured ? 'is-featured' : '' }}">
            @if($plan->badge)
                <span class="mk-plan-badge">{{ $plan->badge }}</span>
            @endif
            <h3 class="mk-plan-name">{{ $plan->name }}</h3>
            <p class="mk-plan-desc">{{ $plan->description }}</p>

            <div class="mk-price">
                @if($plan->isCustomPriced())
                    <strong>Custom</strong>
                    <span>quoted for your society</span>
                @else
                    <strong>₹{{ number_format((float) $plan->amount) }}</strong>
                    <span>/ {{ $plan->cycleSuffix() }}</span>
                @endif
            </div>
            @if($perUnit)
                <div class="mk-per-flat">≈ ₹{{ number_format($perUnit, $perUnit < 10 ? 2 : 0) }} per flat a month</div>
            @endif

            <div class="mk-plan-limit">
                <i class="fas fa-building" aria-hidden="true"></i>
                {{ $plan->hasUnlimitedUnits() ? 'Any number of flats' : 'Up to '.number_format((int) $plan->max_units).' flats' }}
            </div>

            <ul class="mk-checks">
                @if(count($enabled) === $moduleCount)
                    <li><i class="fas fa-check" aria-hidden="true"></i>All {{ $moduleCount }} modules included</li>
                @else
                    @foreach($enabled as $key)
                        <li><i class="fas fa-check" aria-hidden="true"></i>{{ \App\Models\SubscriptionPlan::MODULES[$key]['name'] ?? $key }}</li>
                    @endforeach
                @endif
                <li><i class="fas fa-check" aria-hidden="true"></i>Resident portal with online payments</li>
                <li><i class="fas fa-check" aria-hidden="true"></i>Society set up by our team</li>
            </ul>

            <a href="{{ route('demo.create', ['plan' => $plan->code]) }}" class="mk-btn {{ $featured ? 'mk-btn-primary' : 'mk-btn-ghost' }} mk-btn-block">
                {{ $plan->isCustomPriced() ? 'Talk to us' : 'Request a demo' }}
            </a>
            @if((int) $plan->trial_period_days > 0)
                <p class="mk-plan-trial">{{ $plan->trial_period_days }}-day free trial</p>
            @endif
        </article>
    @endforeach
</div>
