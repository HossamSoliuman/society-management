@extends('society.layouts.app')

@section('title', 'Add Account')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Account</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.chart-of-accounts') }}">Chart of Accounts</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add Account</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('society.accounting.chart-of-accounts.store') }}">
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
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Account Details</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Account Code <span class="required">*</span></label>
                            <input type="text" name="code" value="{{ old('code') }}" class="form-control" placeholder="e.g. 1100" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Name <span class="required">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Enter account name" required>
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Account Group <span class="required">*</span></label>
                            <select name="group_id" class="form-control" required>
                                <option value="">Select Group</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ (string) old('group_id') === (string) $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Parent Account</label>
                            <select name="parent_id" class="form-control">
                                <option value="">None (Top Level)</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ (string) old('parent_id') === (string) $parent->id ? 'selected' : '' }}>{{ $parent->code }} - {{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Type <span class="required">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="detail" {{ old('type', 'detail') === 'detail' ? 'selected' : '' }}>Detail</option>
                                <option value="group" {{ old('type') === 'group' ? 'selected' : '' }}>Group</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Opening Balance (&#8377;)</label>
                            <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', '0') }}" class="form-control" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
                        <a href="{{ route('society.accounting.chart-of-accounts') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Account</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="info-box">
                <i class="fas fa-circle-info"></i>
                <span><strong>Tip</strong> — Use <strong>Group</strong> accounts to organise the tree and <strong>Detail</strong> accounts for posting transactions.</span>
            </div>
        </div>
    </div>
</form>
@endsection
