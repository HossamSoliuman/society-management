@extends('society.layouts.app')

@section('title', 'Add Journal Entry')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Journal Entry</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.index') }}">Accounting</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.accounting.journal-entries') }}">Journal Entries</a>
                <span class="breadcrumb-separator">/</span>
                <span>Add Journal Entry</span>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('society.accounting.journal-entries.store') }}" id="journalForm">
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
                    <div class="section-title" style="font-size: 16px; margin-bottom: 20px;">Journal Entry</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date <span class="required">*</span></label>
                            <input type="date" name="date" value="{{ old('date', '2025-05-30') }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Narration</label>
                            <input type="text" name="narration" value="{{ old('narration') }}" class="form-control" placeholder="Description of the entry">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table" id="linesTable">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">Account</th>
                                    <th class="num" style="text-align: right;">Debit (&#8377;)</th>
                                    <th class="num" style="text-align: right;">Credit (&#8377;)</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="linesBody">
                                {{-- rows injected by JS --}}
                            </tbody>
                            <tfoot>
                                <tr class="fin-total-row">
                                    <td style="font-weight: 700;">Total</td>
                                    <td class="num" style="text-align: right; font-weight: 700;" id="totalDebit">0.00</td>
                                    <td class="num" style="text-align: right; font-weight: 700;" id="totalCredit">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="addLine" style="margin-top: 12px;"><i class="fas fa-plus"></i> Add Line</button>

                    <div id="balanceMsg" style="margin-top: 12px; font-size: 13px;"></div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 16px;">
                        <a href="{{ route('society.accounting.journal-entries') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Post Entry</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="info-box">
                <i class="fas fa-circle-info"></i>
                <span><strong>Double-entry</strong> — Total debit must equal total credit before the entry can be posted.</span>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const accounts = @json($accounts->map(fn ($a) => ['id' => $a->id, 'label' => $a->code.' - '.$a->name])->values());
    const body = document.getElementById('linesBody');
    let idx = 0;

    function optionsHtml() {
        let html = '<option value="">Select Account</option>';
        accounts.forEach((a) => { html += `<option value="${a.id}">${a.label}</option>`; });
        return html;
    }

    function addRow() {
        const i = idx++;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><select name="lines[${i}][account_id]" class="form-control" required>${optionsHtml()}</select></td>
            <td><input type="number" step="0.01" min="0" name="lines[${i}][debit]" class="form-control ln-debit" style="text-align: right;" placeholder="0.00"></td>
            <td><input type="number" step="0.01" min="0" name="lines[${i}][credit]" class="form-control ln-credit" style="text-align: right;" placeholder="0.00"></td>
            <td><button type="button" class="action-btn delete ln-remove" title="Remove"><i class="fas fa-trash"></i></button></td>`;
        body.appendChild(tr);
        recompute();
    }

    function recompute() {
        let d = 0, c = 0;
        body.querySelectorAll('.ln-debit').forEach((el) => d += parseFloat(el.value) || 0);
        body.querySelectorAll('.ln-credit').forEach((el) => c += parseFloat(el.value) || 0);
        document.getElementById('totalDebit').textContent = d.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('totalCredit').textContent = c.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const msg = document.getElementById('balanceMsg');
        const balanced = Math.abs(d - c) < 0.01 && d > 0;
        msg.innerHTML = balanced
            ? '<span style="color: var(--success);"><i class="fas fa-circle-check"></i> Entry is balanced.</span>'
            : '<span style="color: var(--danger);"><i class="fas fa-circle-exclamation"></i> Debit and credit totals must match.</span>';
        document.getElementById('submitBtn').disabled = !balanced;
    }

    body.addEventListener('input', recompute);
    body.addEventListener('click', function (e) {
        if (e.target.closest('.ln-remove')) {
            e.target.closest('tr').remove();
            recompute();
        }
    });
    document.getElementById('addLine').addEventListener('click', addRow);

    addRow();
    addRow();
})();
</script>
@endpush
@endsection
