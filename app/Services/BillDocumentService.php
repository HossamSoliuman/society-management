<?php

namespace App\Services;

use App\Models\BillSetting;
use App\Models\MaintenanceBill;
use App\Models\Society;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;

/**
 * Builds the data consumed by the bill template and renders it to PDF.
 */
class BillDocumentService
{
    public function design(Society|int|null $society): BillSetting
    {
        $societyId = $society instanceof Society ? $society->id : $society;

        return BillSetting::query()
            ->when($societyId, fn ($q) => $q->where('society_id', $societyId))
            ->first() ?? new BillSetting;
    }

    /**
     * Array shape consumed by `society.billing._bill-template` and the PDF view.
     * Society/bank details come from the society row (design overrides win),
     * never from literals.
     *
     * @return array<string, mixed>
     */
    public function templateData(MaintenanceBill $bill): array
    {
        $bill->loadMissing(['items', 'society']);
        $society = $bill->society;
        $design = $this->design($society);
        $flat = collect([$bill->flat_number, $bill->tower_wing, $bill->floor])->filter()->implode(', ');

        $address = collect([$society?->address_line_1, $society?->address_line_2, $society?->city, $society?->state, $society?->pincode])
            ->filter()->implode(', ');

        return [
            'number' => $bill->bill_number,
            'date' => $bill->bill_date?->format('d M Y'),
            'due_date' => $bill->due_date?->format('d M Y'),
            'to_name' => $bill->member_name ?: '—',
            'to_flat' => $flat ?: '—',
            'to_society' => $design->society_name ?: ($society?->name ?? '—'),
            'society_name' => $design->society_name ?: ($society?->name ?? '—'),
            'society_address' => $design->address ?: ($address ?: '—'),
            'society_phone' => $design->phone ?: ($society?->primary_mobile ?? '—'),
            'society_email' => $design->email ?: ($society?->primary_email ?? '—'),
            'society_website' => $design->website ?: ($society?->website ?? ''),
            'gst_number' => $society?->gst_number,
            'month' => $bill->bill_month,
            'type' => $bill->billing_type,
            'cycle' => $bill->bill_cycle,
            'status' => $bill->status,
            'items' => $bill->items->map(fn ($item) => [
                'name' => $item->charge_head_name,
                'description' => $item->description,
                'amount' => (float) $item->amount,
            ])->all(),
            'subtotal' => (float) $bill->sub_total,
            'discount' => (float) $bill->discount,
            'late_fee' => (float) $bill->late_fee,
            'tax' => (float) $bill->tax_amount,
            'previous_dues' => (float) $bill->previous_dues,
            'total' => (float) $bill->total_amount,
            'total_payable' => (float) $bill->total_amount,
            'collected' => (float) $bill->collected_amount,
            'outstanding' => (float) $bill->outstanding_amount,
            'amount_in_words' => amount_in_words_inr($bill->total_amount),
            'upi_id' => $design->upi_id ?: null,
            'bank_name' => $society?->bank_name,
            'account_number' => $society?->account_number,
            'ifsc_code' => $society?->ifsc_code,
            'collection_account' => $bill->collection_account ?: $design->default_collection_account,
            'terms' => $design->terms_conditions,
            'footer_note' => $design->footer_note,
        ];
    }

    public function renderPdf(MaintenanceBill $bill): PdfDocument
    {
        return Pdf::loadView('society.billing.bills.pdf', [
            'design' => $this->design($bill->society_id),
            'bill' => $this->templateData($bill),
        ])->setPaper('a4');
    }

    public function fileName(MaintenanceBill $bill): string
    {
        return str_replace(['/', '\\'], '-', $bill->bill_number).'.pdf';
    }
}
