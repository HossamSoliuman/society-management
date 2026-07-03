{{-- @param string $action; array $departments; array $tenderTypes; array $statusOptions --}}
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ $action }}">
            <div style="display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr 1.2fr auto auto; gap: 12px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Search</label>
                    <div class="header-search" style="max-width: none;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search tender title, reference no., department...">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        @foreach(($statusOptions ?? []) as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Tender Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($tenderTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Published Date</label>
                    <div style="display: flex; gap: 6px;">
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ $action }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
            </div>
        </form>
    </div>
</div>
