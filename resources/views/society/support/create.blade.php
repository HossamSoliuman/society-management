@extends('society.layouts.app')

@section('title', 'Raise New Request')

@php
    $priorityDots = ['high' => 'var(--danger)', 'medium' => 'var(--warning)', 'low' => 'var(--success)'];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Raise New Request</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.support.index') }}">Priority Support</a>
                <span class="breadcrumb-separator">/</span>
                <span>Raise New Request</span>
            </div>
        </div>
        <a href="{{ route('society.support.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Requests</a>
    </div>
</div>

<form method="POST" action="{{ route('society.support.store') }}" enctype="multipart/form-data" id="supportForm">
    @csrf

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="content-grid">
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Request Information</div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Request Category <span class="required">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Priority <span class="required">*</span></label>
                            <select name="priority" class="form-control" required>
                                @foreach($priorities as $val => $label)
                                    <option value="{{ $val }}" {{ old('priority', 'high') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subject <span class="required">*</span></label>
                            <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" placeholder="Enter a short summary of the issue" required>
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Raised By <span class="required">*</span></label>
                            <div class="radio-card-group" style="grid-template-columns: 1fr 1fr;">
                                <label class="radio-card" style="display: flex; align-items: center; gap: 10px; padding: 12px;">
                                    <input type="radio" name="raised_by_type" value="member" {{ old('raised_by_type', 'member') === 'member' ? 'checked' : '' }} style="position: static; opacity: 1; accent-color: var(--primary);">
                                    <span style="font-weight: 600;">Member</span>
                                </label>
                                <label class="radio-card" style="display: flex; align-items: center; gap: 10px; padding: 12px;">
                                    <input type="radio" name="raised_by_type" value="staff_admin" {{ old('raised_by_type') === 'staff_admin' ? 'checked' : '' }} style="position: static; opacity: 1; accent-color: var(--primary);">
                                    <span style="font-weight: 600;">Staff / Admin</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Select Member <span class="required">*</span></label>
                            <select name="member_id" class="form-control">
                                <option value="">Select Member</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ (string) old('member_id') === (string) $member->id ? 'selected' : '' }}>{{ $member->name }}{{ $member->flat_unit ? ' — '.$member->flat_unit : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile" value="{{ old('mobile') }}" class="form-control" placeholder="Enter mobile number">
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Unit / Flat No.</label>
                            <select name="flat_no" class="form-control">
                                <option value="">Select Unit / Flat</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->unit_number }}" {{ old('flat_no') === $unit->unit_number ? 'selected' : '' }}>{{ $unit->unit_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email ID</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Enter email address">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Preferred Contact Method</label>
                            <select name="preferred_contact" class="form-control">
                                <option value="">Select Preferred Method</option>
                                @foreach($contactMethods as $method)
                                    <option value="{{ $method }}" {{ old('preferred_contact') === $method ? 'selected' : '' }}>{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Problem Description <span class="required">*</span></label>
                        <div class="form-text" style="margin-top: -2px; margin-bottom: 6px;">Provide as much detail as possible about the issue.</div>
                        <textarea name="description" id="fldDescription" class="form-control" rows="4" maxlength="1000" placeholder="Describe the issue in detail..." required>{{ old('description') }}</textarea>
                        <div style="text-align: right; font-size: 11px; color: var(--text-muted); margin-top: 4px;"><span id="descCount">{{ mb_strlen((string) old('description')) }}</span> / 1000</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Location <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span></label>
                        <input type="text" name="location" value="{{ old('location') }}" class="form-control" placeholder="Mention the exact location (e.g., Building A, Parking Area, Lift 2, etc.)">
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Attachment <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span></label>
                            <label class="file-upload" for="attachment">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <div class="file-upload-text">Click to upload or drag and drop</div>
                                <div class="file-upload-hint">JPG, PNG, PDF (Max. 5MB)</div>
                                <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                            </label>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Additional Notes <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span></label>
                            <textarea name="notes" id="fldNotes" class="form-control" rows="4" maxlength="500" placeholder="Enter any additional notes">{{ old('notes') }}</textarea>
                            <div style="text-align: right; font-size: 11px; color: var(--text-muted); margin-top: 4px;"><span id="notesCount">{{ mb_strlen((string) old('notes')) }}</span> / 500</div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                        <a href="{{ route('society.support.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right rail --}}
        <div>
            {{-- What happens next --}}
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">What happens next?</div>
                    <div class="timeline">
                        <div class="timeline-item">
                            <span class="timeline-dot" style="background: var(--success);"></span>
                            <div class="timeline-content">
                                <div style="font-weight: 600;">Request Submitted</div>
                                <div style="color: var(--text-muted);">Your request will be logged and assigned.</div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <span class="timeline-dot" style="background: var(--info);"></span>
                            <div class="timeline-content">
                                <div style="font-weight: 600;">Assigned to Team</div>
                                <div style="color: var(--text-muted);">Our support team will review and take action.</div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <span class="timeline-dot" style="background: var(--orange);"></span>
                            <div class="timeline-content">
                                <div style="font-weight: 600;">In Progress</div>
                                <div style="color: var(--text-muted);">We will keep you updated on the progress.</div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <span class="timeline-dot" style="background: var(--purple);"></span>
                            <div class="timeline-content">
                                <div style="font-weight: 600;">Resolved</div>
                                <div style="color: var(--text-muted);">Once resolved, the request will be closed.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Need immediate help --}}
            <div class="info-box">
                <i class="fas fa-circle-info"></i>
                <div>
                    <div style="font-weight: 700; margin-bottom: 4px;">Need immediate help?</div>
                    <div style="margin-bottom: 6px;">For urgent matters, please call or email our support team.</div>
                    <div><a href="tel:+919876543210" style="color: var(--info); font-weight: 600;">+91 98765 43210</a></div>
                    <div><a href="mailto:support@society.com" style="color: var(--info); font-weight: 600;">support@society.com</a></div>
                </div>
            </div>

            {{-- Guidelines --}}
            <div class="tips-card warn">
                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px;">Guidelines</div>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <li><i class="fas fa-circle-check"></i><span>Provide clear and accurate information.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Include photos if applicable.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>Choose the correct category for faster resolution.</span></li>
                    <li><i class="fas fa-circle-check"></i><span>We aim to respond within 24 business hours.</span></li>
                </ul>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    $('fldDescription').addEventListener('input', function () { $('descCount').textContent = this.value.length; });
    $('fldNotes').addEventListener('input', function () { $('notesCount').textContent = this.value.length; });
    $('attachment').addEventListener('change', function () {
        if (this.files.length) {
            this.closest('.file-upload').querySelector('.file-upload-text').textContent = this.files[0].name;
        }
    });
})();
</script>
@endpush
