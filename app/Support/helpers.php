<?php

use App\Models\BillSetting;

if (! function_exists('format_inr')) {
    /**
     * Format a numeric value as Indian Rupees, e.g. "₹ 3,500.00".
     * Decimal places default to the Bill Settings value (falls back to 2).
     */
    function format_inr(int|float|string|null $value, ?int $decimals = null): string
    {
        $decimals ??= (int) (optional(BillSetting::first())->amount_decimal_places ?? 2);

        return '₹ '.number_format((float) $value, $decimals);
    }
}

if (! function_exists('amount_in_words_inr')) {
    /**
     * Convert a numeric amount into Indian-system words with a trailing " Only".
     * Example: 2850 → "Rupees Two Thousand Eight Hundred Fifty Only".
     */
    function amount_in_words_inr(int|float|string|null $value): string
    {
        $value = (float) $value;
        $rupees = (int) floor($value);
        $paise = (int) round(($value - $rupees) * 100);

        $words = 'Rupees '.indian_number_to_words($rupees);

        if ($paise > 0) {
            $words .= ' and '.indian_number_to_words($paise).' Paise';
        }

        return $words.' Only';
    }
}

if (! function_exists('indian_number_to_words')) {
    /**
     * Convert an integer to words using the Indian numbering system
     * (Thousand, Lakh, Crore).
     */
    function indian_number_to_words(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $ones = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen',
        ];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $twoDigits = function (int $n) use ($ones, $tens): string {
            if ($n < 20) {
                return $ones[$n];
            }

            return trim($tens[intdiv($n, 10)].' '.$ones[$n % 10]);
        };

        $threeDigits = function (int $n) use ($ones, $twoDigits): string {
            $parts = [];
            if ($n >= 100) {
                $parts[] = $ones[intdiv($n, 100)].' Hundred';
                $n %= 100;
            }
            if ($n > 0) {
                $parts[] = $twoDigits($n);
            }

            return implode(' ', $parts);
        };

        $parts = [];

        $crore = intdiv($number, 10000000);
        $number %= 10000000;
        if ($crore > 0) {
            $parts[] = indian_number_to_words($crore).' Crore';
        }

        $lakh = intdiv($number, 100000);
        $number %= 100000;
        if ($lakh > 0) {
            $parts[] = $twoDigits($lakh).' Lakh';
        }

        $thousand = intdiv($number, 1000);
        $number %= 1000;
        if ($thousand > 0) {
            $parts[] = $twoDigits($thousand).' Thousand';
        }

        if ($number > 0) {
            $parts[] = $threeDigits($number);
        }

        return trim(implode(' ', $parts));
    }
}
