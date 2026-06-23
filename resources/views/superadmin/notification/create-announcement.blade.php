@extends('superadmin.layouts.app')

@section('title', 'Send Announcement')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Send Announcement</h1>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('superadmin.notification.announcements') }}">Notifications</a>
        <span class="breadcrumb-separator">/</span>
        <span>Send Announcement</span>
    </div>
</div>

<form action="{{ route('superadmin.notification.announcements.store') }}" method="POST">
    @csrf
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-bell"></i> Compose Announcement</div>

                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Enter announcement title" value="{{ old('title') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Message <span class="required">*</span></label>
                    <textarea name="message" class="form-control" rows="6" placeholder="Enter announcement message..." required>{{ old('message') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Category (Optional)</label>
                    <input type="text" name="category" class="form-control" placeholder="e.g. Maintenance, Meeting, General" value="{{ old('category') }}">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Priority <span class="required">*</span></label>
                        <select name="priority" class="form-control" required>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Send Type <span class="required">*</span></label>
                        <select name="send_type" class="form-control" required>
                            <option value="now" selected>Send Now</option>
                            <option value="scheduled">Schedule for Later</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header"><div class="card-title"><i class="fas fa-users" style="color: var(--primary);"></i> Recipients</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Recipient Type <span class="required">*</span></label>
                        <select name="recipient_type" class="form-control" required>
                            <option value="all_members" selected>All Members</option>
                            <option value="all_residents">All Residents</option>
                            <option value="all_staff">All Staff</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <span>This announcement will be sent to approximately <strong>320 recipients</strong>.</span>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header"><div class="card-title"><i class="fas fa-paper-plane" style="color: var(--primary);"></i> Delivery Channel</div></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Delivery Channel <span class="required">*</span></label>
                        <select name="delivery_channel" class="form-control" required>
                            <option value="all" selected>All Channels (In-App + Email + SMS)</option>
                            <option value="in_app">In-App Only</option>
                            <option value="email">Email Only</option>
                            <option value="sms">SMS Only</option>
                        </select>
                    </div>
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <span>Members can control their notification preferences in their profile settings.</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body" style="display: flex; justify-content: space-between; gap: 12px;">
                    <a href="{{ route('superadmin.notification.announcements') }}" class="btn btn-secondary" style="flex: 1;">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="fas fa-paper-plane"></i> Send Announcement</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
