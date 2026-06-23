@extends('superadmin.layouts.app')

@section('title', 'Masters')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Masters</h1>
            <p class="page-subtitle">Manage society types, unit types, payment modes, and other master data.</p>
        </div>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('superadmin.dashboard') }}">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Masters</span>
    </div>
</div>

<div class="tabs">
    <a href="#" class="tab active">Society Types</a>
    <a href="#" class="tab">Unit Types</a>
    <a href="#" class="tab">Payment Modes</a>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title"><i class="fas fa-building" style="color: var(--primary); margin-right: 8px;"></i>Society Types</div>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('addSocietyType').style.display='block'"><i class="fas fa-plus"></i> Add Society Type</button>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">#</th>
                        <th>Society Type</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($societyTypes as $idx => $type)
                    <tr>
                        <td style="padding-left: 20px;">{{ $idx + 1 }}</td>
                        <td>{{ $type->name }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $type->description }}</td>
                        <td><span class="status-badge {{ $type->status }}">{{ ucfirst($type->status) }}</span></td>
                        <td style="font-size: 12px;">{{ $type->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <button class="action-btn edit"><i class="fas fa-pen"></i></button>
                                <form action="{{ route('superadmin.masters.society-types.destroy', $type) }}" method="POST" style="display: inline;" data-confirm="Delete this type?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @include('superadmin.components.pagination', ['items' => $societyTypes])
        </div>
    </div>

    <div id="addSocietyType" style="display: none;">
        <div class="card">
            <div class="card-header"><div class="card-title">Add Society Type</div></div>
            <div class="card-body">
                <form action="{{ route('superadmin.masters.society-types.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Society Type Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter society type name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-control">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary">Save Society Type</button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addSocietyType').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-top: 20px;">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title"><i class="fas fa-th-large" style="color: var(--success); margin-right: 8px;"></i>Unit Types</div>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('addUnitType').style.display='block'"><i class="fas fa-plus"></i> Add Unit Type</button>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">#</th>
                        <th>Unit Type</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unitTypes as $idx => $type)
                    <tr>
                        <td style="padding-left: 20px;">{{ $idx + 1 }}</td>
                        <td>{{ $type->name }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $type->description }}</td>
                        <td><span class="status-badge {{ $type->status }}">{{ ucfirst($type->status) }}</span></td>
                        <td style="font-size: 12px;">{{ $type->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <button class="action-btn edit"><i class="fas fa-pen"></i></button>
                                <form action="{{ route('superadmin.masters.unit-types.destroy', $type) }}" method="POST" style="display: inline;" data-confirm="Delete this type?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="addUnitType" style="display: none;">
        <div class="card">
            <div class="card-header"><div class="card-title">Add Unit Type</div></div>
            <div class="card-body">
                <form action="{{ route('superadmin.masters.unit-types.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Unit Type Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter unit type name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-control">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary">Save Unit Type</button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addUnitType').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-top: 20px;">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title"><i class="fas fa-credit-card" style="color: var(--purple); margin-right: 8px;"></i>Payment Modes</div>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('addPaymentMode').style.display='block'"><i class="fas fa-plus"></i> Add Payment Mode</button>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">#</th>
                        <th>Payment Mode</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentModes as $idx => $mode)
                    <tr>
                        <td style="padding-left: 20px;">{{ $idx + 1 }}</td>
                        <td>{{ $mode->name }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $mode->description }}</td>
                        <td><span class="status-badge {{ $mode->status }}">{{ ucfirst($mode->status) }}</span></td>
                        <td style="font-size: 12px;">{{ $mode->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <button class="action-btn edit"><i class="fas fa-pen"></i></button>
                                <form action="{{ route('superadmin.masters.payment-modes.destroy', $mode) }}" method="POST" style="display: inline;" data-confirm="Delete this mode?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="addPaymentMode" style="display: none;">
        <div class="card">
            <div class="card-header"><div class="card-title">Add Payment Mode</div></div>
            <div class="card-body">
                <form action="{{ route('superadmin.masters.payment-modes.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Payment Mode Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter payment mode name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-control">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary">Save Payment Mode</button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addPaymentMode').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
