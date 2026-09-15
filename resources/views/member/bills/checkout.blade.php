@extends('member.layouts.app')

@section('title', 'Pay Online')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Pay Online</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('member.bills.index') }}">My Bills</a>
                <span class="breadcrumb-separator">/</span>
                <span>Checkout</span>
            </div>
        </div>
        <a href="{{ $returnUrl }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

@include('society.collections._checkout-card', ['order' => $order, 'checkout' => $checkout, 'returnUrl' => $returnUrl, 'receiptUrl' => $receiptUrl, 'webhookUrl' => $webhookUrl])
@endsection
