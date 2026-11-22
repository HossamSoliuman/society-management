@extends('marketing.layout')

@section('title', 'Request a demo')
@section('description', 'Book a walkthrough of billing, accounts, visitor management and the resident portal for your housing society.')

@section('content')
<section class="mk-dusk on-dusk mk-page-hero" aria-labelledby="demo-title">
    <div class="mk-container mk-demo">
        <div class="mk-demo-side">
            <h1 class="mk-display" id="demo-title">See it working for <em>your society</em></h1>
            <p class="mk-lead">Tell us a little about your society and we will set up a walkthrough for your committee.</p>

            <ol class="mk-steps">
                <li>
                    <h3 class="mk-h3">We call you</h3>
                    <p>Someone from our team calls on the number you give us to understand how your society runs today.</p>
                </li>
                <li>
                    <h3 class="mk-h3">A walkthrough for your committee</h3>
                    <p>We show billing, accounts, the gate and the resident portal, set up like your society.</p>
                </li>
                <li>
                    <h3 class="mk-h3">Your society goes live</h3>
                    <p>Once you choose a plan, we add your wings, flats and members and invite residents to the portal.</p>
                </li>
            </ol>

            @if($company?->email || $company?->phone)
                <div class="mk-contact">
                    @if($company->phone)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company->phone) }}"><i class="fas fa-phone" aria-hidden="true"></i>{{ $company->phone }}</a>
                    @endif
                    @if($company->email)
                        <a href="mailto:{{ $company->email }}"><i class="fas fa-envelope" aria-hidden="true"></i>{{ $company->email }}</a>
                    @endif
                </div>
            @endif
        </div>

        <div class="mk-form-card" id="demo-form">
            @if(session('demo_requested'))
                <div class="mk-success" role="status">
                    <div class="mk-success-icon"><i class="fas fa-check" aria-hidden="true"></i></div>
                    <h2 class="mk-h3" style="margin-bottom: 0;">Request received</h2>
                    <p>Thank you. We will call you on the number you gave to arrange the walkthrough.</p>
                    <div class="mk-hero-actions">
                        <a href="{{ route('features') }}" class="mk-btn mk-btn-ghost">Explore features</a>
                        <a href="{{ route('home') }}" class="mk-btn mk-btn-primary">Back to home</a>
                    </div>
                </div>
            @else
                <h2 class="mk-h3">Request a demo</h2>

                @if($errors->any())
                    <div class="mk-alert" role="alert">Check the highlighted fields and try again.</div>
                @endif

                <form method="POST" action="{{ route('demo.store') }}" novalidate>
                    @csrf
                    <div class="mk-hp" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="mk-form-grid">
                        <div class="mk-field">
                            <label for="name">Your name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="mk-input @error('name') is-invalid @enderror" autocomplete="name" required>
                            @error('name')<span class="mk-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="mk-field">
                            <label for="role">Your role</label>
                            <select id="role" name="role" class="mk-input @error('role') is-invalid @enderror" required>
                                <option value="" disabled @selected(! old('role'))>Select your role</option>
                                @foreach($roles as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role')<span class="mk-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="mk-field is-wide">
                            <label for="society_name">Society name</label>
                            <input type="text" id="society_name" name="society_name" value="{{ old('society_name') }}" class="mk-input @error('society_name') is-invalid @enderror" autocomplete="organization" required>
                            @error('society_name')<span class="mk-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="mk-field">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}" class="mk-input @error('city') is-invalid @enderror" autocomplete="address-level2" required>
                            @error('city')<span class="mk-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="mk-field">
                            <label for="units">Number of flats <small>(optional)</small></label>
                            <input type="number" id="units" name="units" value="{{ old('units') }}" min="1" inputmode="numeric" class="mk-input @error('units') is-invalid @enderror">
                            @error('units')<span class="mk-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="mk-field">
                            <label for="phone">Phone number</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="mk-input @error('phone') is-invalid @enderror" autocomplete="tel" required>
                            @error('phone')<span class="mk-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="mk-field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="mk-input @error('email') is-invalid @enderror" autocomplete="email" required>
                            @error('email')<span class="mk-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="mk-field is-wide">
                            <label for="message">Anything we should know? <small>(optional)</small></label>
                            <textarea id="message" name="message" class="mk-input @error('message') is-invalid @enderror" rows="4">{{ old('message', $plan ? 'We are interested in the '.$plan->name.'.' : '') }}</textarea>
                            @error('message')<span class="mk-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="mk-form-foot">
                        <button type="submit" class="mk-btn mk-btn-primary mk-btn-block">Request a demo</button>
                        <p class="mk-form-note">We use these details only to contact you about your demo.</p>
                    </div>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
