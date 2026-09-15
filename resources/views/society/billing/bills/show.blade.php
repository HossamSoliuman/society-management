@extends('society.layouts.app')

@section('title', 'Bill '.$billModel->bill_number)

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $billModel->bill_number }}</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.billing.bills.index') }}">Maintenance Billing</a>
                <span class="breadcrumb-separator">/</span>
                <span>{{ $billModel->bill_number }}</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('society.billing.bills.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Bills</a>
            @if($billModel->outstanding_amount > 0 && $billModel->status !== 'cancelled')
                <a href="{{ route('society.collections.create', ['bill' => $billModel->id]) }}" class="btn btn-outline-secondary"><i class="fas fa-indian-rupee-sign"></i> Record Payment</a>
                <form method="POST" action="{{ route('society.collections.online.bill', $billModel) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-credit-card"></i> Pay Online</button>
                </form>
            @endif
            <a href="{{ route('society.billing.bills.pdf', $billModel) }}" class="btn btn-outline-secondary"><i class="fas fa-file-pdf"></i> PDF</a>
            <form method="POST" action="{{ route('society.billing.bills.send', $billModel) }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-paper-plane"></i> Email</button>
            </form>
            <a href="{{ route('society.billing.bills.print', $billModel) }}" target="_blank" class="btn btn-primary"><i class="fas fa-print"></i> Print Bill</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="receipt-preview">
            @include('society.billing._bill-template', ['design' => $design, 'bill' => $bill])
        </div>
    </div>
</div>
@endsection
