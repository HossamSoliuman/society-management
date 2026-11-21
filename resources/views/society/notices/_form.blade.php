@php
    /**
     * @var \App\Models\Notice $notice
     * @var array<string, array{label: string, roles: array<int, string>|null}> $audiences
     * @var string $formAction
     * @var bool $isEdit
     */
    $isEdit = $isEdit ?? false;
    $published = $isEdit && $notice->status === 'published';
@endphp
<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

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
                    <div class="section-title"><i class="fas fa-pen"></i> Notice Details</div>

                    <div class="form-group">
                        <label class="form-label">Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $notice->title) }}" maxlength="255" required>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Type <span class="required">*</span></label>
                            <select name="notice_type" class="form-control" required>
                                @foreach(['general' => 'General', 'maintenance' => 'Maintenance', 'billing' => 'Billing', 'event' => 'Event'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('notice_type', $notice->notice_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Priority <span class="required">*</span></label>
                            <select name="priority" class="form-control" required>
                                @foreach(['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('priority', $notice->priority) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Audience <span class="required">*</span></label>
                            <select name="audience" class="form-control" required>
                                @foreach($audiences as $key => $audience)
                                    <option value="{{ $key }}" @selected(old('audience', $isEdit ? $notice->societyAudience() : 'everyone') === $key)>{{ $audience['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Summary</label>
                        <input type="text" name="short_description" class="form-control" value="{{ old('short_description', $notice->short_description) }}" maxlength="500" placeholder="One line shown in lists and notifications">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message <span class="required">*</span></label>
                        <textarea name="content" class="form-control" rows="10" required>{{ old('content', $notice->content) }}</textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        @if($notice->attach_path)
                            <div style="display: flex; align-items: center; gap: 12px; margin-top: 8px; font-size: 13px;">
                                <a href="{{ Storage::disk('public')->url($notice->attach_path) }}" target="_blank" rel="noopener"><i class="fas fa-paperclip"></i> {{ basename($notice->attach_path) }}</a>
                                <label style="display: inline-flex; align-items: center; gap: 6px; color: var(--text-secondary);">
                                    <input type="checkbox" name="remove_attachment" value="1"> Remove
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-gear"></i> Delivery</div>
                    <div class="form-row-3">
                        <label class="setting-toggle-row" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; cursor: pointer;">
                            <span class="setting-text">
                                <span class="label"><i class="fas fa-envelope"></i> Email</span>
                            </span>
                            <input type="checkbox" name="send_email" value="1" @checked(old('send_email', $notice->send_email))>
                        </label>
                        <label class="setting-toggle-row" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; cursor: pointer;">
                            <span class="setting-text">
                                <span class="label"><i class="fas fa-comment"></i> SMS</span>
                            </span>
                            <input type="checkbox" name="send_sms" value="1" @checked(old('send_sms', $notice->send_sms))>
                        </label>
                        <label class="setting-toggle-row" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; cursor: pointer;">
                            <span class="setting-text">
                                <span class="label"><i class="fas fa-circle-check"></i> Require acknowledgement</span>
                            </span>
                            <input type="checkbox" name="require_acknowledgement" value="1" @checked(old('require_acknowledgement', $notice->require_acknowledgement))>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-body">
                    <div class="section-title"><i class="fas fa-calendar-days"></i> Schedule</div>

                    <div class="form-group">
                        <label class="form-label">Publish On</label>
                        <input type="datetime-local" name="publish_at" class="form-control" value="{{ old('publish_at', optional($notice->publish_at)->format('Y-m-d\TH:i')) }}" @disabled($published)>
                        @if($published)
                            <input type="hidden" name="publish_at" value="{{ $notice->publish_at->format('Y-m-d\TH:i') }}">
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label">Expires On</label>
                        <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at', optional($notice->expires_at)->format('Y-m-d\TH:i')) }}">
                    </div>

                    <div class="setting-toggle-row" style="border-bottom: none;">
                        <span class="setting-text">
                            <span class="label">Pin to dashboard</span>
                        </span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="pin_to_dashboard" value="1" @checked(old('pin_to_dashboard', $notice->pin_to_dashboard))>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body" style="display: flex; flex-direction: column; gap: 10px;">
                    <button type="submit" name="action" value="publish" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> {{ $published ? 'Save Changes' : 'Publish' }}
                    </button>
                    @unless($published)
                        <button type="submit" name="action" value="draft" class="btn btn-secondary"><i class="fas fa-floppy-disk"></i> Save as Draft</button>
                    @endunless
                    <a href="{{ route('society.notices.manage') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
