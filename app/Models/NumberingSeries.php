<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class NumberingSeries extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'numbering_series';

    protected $fillable = [
        'society_id', 'document_type', 'is_default', 'prefix', 'format',
        'next_number', 'reset_frequency', 'financial_year', 'start_date',
        'description', 'status',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'next_number' => 'integer',
            'start_date' => 'date',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * Build the next document number, then advance the sequence.
     */
    public function generateNext(): string
    {
        $number = $this->buildNumber($this->next_number);
        $this->increment('next_number');

        return $number;
    }

    /**
     * Preview of the next number without advancing the sequence.
     */
    public function sampleNumber(): string
    {
        return $this->buildNumber($this->next_number);
    }

    /**
     * The most recently generated number (one below the next sequence).
     */
    public function lastGeneratedNumber(): string
    {
        return $this->buildNumber(max(1, $this->next_number - 1));
    }

    /**
     * Reset the sequence back to 1.
     */
    public function resetSequence(): void
    {
        $this->update(['next_number' => 1]);
    }

    public function documentTypeLabel(): string
    {
        return match ($this->document_type) {
            'maintenance_bill' => 'Maintenance Bill',
            'receipt' => 'Receipt',
            'credit_note' => 'Credit Note',
            'debit_note' => 'Debit Note',
            'refund' => 'Refund',
            default => ucwords(str_replace('_', ' ', $this->document_type)),
        };
    }

    public function resetFrequencyLabel(): string
    {
        return match ($this->reset_frequency) {
            'yearly' => 'Yearly (Apr)',
            'monthly' => 'Monthly',
            'daily' => 'Daily',
            'never' => 'Never',
            default => ucfirst($this->reset_frequency),
        };
    }

    /**
     * Zero-padded representation of the next number (min 6 digits, per design).
     */
    public function formattedNextNumber(): string
    {
        return str_pad((string) $this->next_number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Apply the format tags (YYYY/YY/MM/DD/#####) and prepend the prefix.
     */
    protected function buildNumber(int $sequence): string
    {
        $now = Carbon::now();
        $year = $this->financialYearStart() ?? $now->year;

        $result = strtr($this->format ?? '', [
            'YYYY' => (string) $year,
            'YY' => substr((string) $year, -2),
            'MM' => $now->format('m'),
            'DD' => $now->format('d'),
        ]);

        $result = preg_replace_callback('/#+/', function (array $matches) use ($sequence): string {
            $width = max(strlen($matches[0]), 6);

            return str_pad((string) $sequence, $width, '0', STR_PAD_LEFT);
        }, $result);

        return ($this->prefix ?? '').$result;
    }

    /**
     * Start year parsed from a "2025-2026" financial year string.
     */
    protected function financialYearStart(): ?int
    {
        if ($this->financial_year && preg_match('/(\d{4})/', $this->financial_year, $m)) {
            return (int) $m[1];
        }

        return null;
    }
}
