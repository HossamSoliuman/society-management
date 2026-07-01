@extends('society.layouts.app')

@section('title', 'Notifications')

@php
    $events = [
        ['key' => 'bill_generated', 'title' => 'Bill Generated', 'desc' => 'Sent to members when a new bill is generated.', 'channels' => ['email' => true, 'sms' => true, 'whatsapp' => false], 'template' => 'Dear {member_name}, your maintenance bill {bill_no} of {amount} for {bill_month} has been generated. Due date: {due_date}.'],
        ['key' => 'payment_received', 'title' => 'Payment Received', 'desc' => 'Sent when a payment is recorded against a bill.', 'channels' => ['email' => true, 'sms' => true, 'whatsapp' => true], 'template' => 'Dear {member_name}, we have received your payment of {amount} against bill {bill_no}. Thank you!'],
        ['key' => 'payment_reminder', 'title' => 'Payment Reminder', 'desc' => 'Sent before the due date as a friendly reminder.', 'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false], 'template' => 'Dear {member_name}, this is a reminder that bill {bill_no} of {amount} is due on {due_date}. Please pay on time to avoid late fee.'],
        ['key' => 'overdue_reminder', 'title' => 'Overdue Reminder', 'desc' => 'Sent after the due date for unpaid bills.', 'channels' => ['email' => true, 'sms' => true, 'whatsapp' => false], 'template' => 'Dear {member_name}, bill {bill_no} of {amount} is overdue. A late fee may now apply. Please clear your dues at the earliest.'],
    ];
@endphp

@section('content')
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
        <button type="button" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
    </div>
</div>

@include('society.billing.settings._tabs')

<div class="card">
    <div class="card-body">
        <div class="card-title" style="font-size: 16px;">Notification Settings</div>
        <div class="card-subtitle">Choose which channels are used for each billing event and customise the message templates.</div>

        @foreach($events as $event)
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
                                    <input type="checkbox" name="{{ $event['key'] }}_{{ $channel }}" value="1" {{ $event['channels'][$channel] ? 'checked' : '' }}>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span style="font-size: 12px; color: var(--text-secondary);">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="form-group" style="margin-top: 14px; margin-bottom: 0;">
                    <label class="form-label">Message Template</label>
                    <textarea name="{{ $event['key'] }}_template" class="form-control" rows="2">{{ $event['template'] }}</textarea>
                    <div class="form-text">Available tags: {member_name}, {bill_no}, {amount}, {bill_month}, {due_date}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
