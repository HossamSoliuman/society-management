@extends('society.layouts.app')

@section('title', 'Notifications')

@section('content')
<form method="POST" action="{{ route('society.billing.settings.notifications.update') }}" id="notificationSettingsForm">
    @csrf
    @method('PUT')

    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Notifications</h1>
                <div class="breadcrumb" style="margin-top: 6px;">
                    <a href="{{ route('society.dashboard') }}">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="{{ route('society.billing.settings.general') }}">Maintenance Billing</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="{{ route('society.billing.settings.general') }}">Bill Settings</a>
                    <span class="breadcrumb-separator">/</span>
                    <span>Notifications</span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
        </div>
    </div>

    @include('society.billing.settings._tabs')

    @if($errors->any())
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="card-title" style="font-size: 16px;">Reminder Schedule</div>
            <div class="card-subtitle">Days on which the daily reminder job emails / texts members with unpaid bills.</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Days before due date</label>
                    <input type="text" name="reminder_days_before_due" class="form-control" value="{{ old('reminder_days_before_due', implode(', ', $settings->reminderDaysBeforeDue())) }}" placeholder="e.g. 7, 3, 1">
                    <div class="form-text">Comma-separated. Uses the "Payment Reminder" channels and template.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Days after due date</label>
                    <input type="text" name="reminder_days_after_due" class="form-control" value="{{ old('reminder_days_after_due', implode(', ', $settings->reminderDaysAfterDue())) }}" placeholder="e.g. 1, 7, 15">
                    <div class="form-text">Comma-separated. Uses the "Overdue Reminder" channels and template.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="card-title" style="font-size: 16px;">Notification Settings</div>
            <div class="card-subtitle">Choose which channels are used for each billing event and customise the message templates.</div>

            @foreach($events as $key => $event)
                <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                        <div>
                            <div style="font-weight: 700;">{{ $event['title'] }}</div>
                            <div class="form-text">{{ $event['desc'] }}</div>
                        </div>
                        <div style="display: flex; gap: 20px;">
                            @foreach(['email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'] as $channel => $label)
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="events[{{ $key }}][{{ $channel }}]" value="1" @checked(old("events.$key.$channel", $event['channels'][$channel]))>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span style="font-size: 12px; color: var(--text-secondary);">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 14px; margin-bottom: 0;">
                        <label class="form-label">Message Template</label>
                        <textarea name="events[{{ $key }}][template]" class="form-control" rows="2">{{ old("events.$key.template", $event['template']) }}</textarea>
                        <div class="form-text">Available tags: {member_name}, {bill_no}, {amount}, {bill_month}, {due_date}, {society}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</form>
@endsection
