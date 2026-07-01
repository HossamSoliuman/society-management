<?php

it('converts amounts to Indian-system words', function (float $value, string $expected) {
    expect(amount_in_words_inr($value))->toBe($expected);
})->with([
    [2850, 'Rupees Two Thousand Eight Hundred Fifty Only'],
    [3500, 'Rupees Three Thousand Five Hundred Only'],
    [100, 'Rupees One Hundred Only'],
    [150000, 'Rupees One Lakh Fifty Thousand Only'],
    [12500000, 'Rupees One Crore Twenty Five Lakh Only'],
    [0, 'Rupees Zero Only'],
]);

it('includes paise when present', function () {
    expect(amount_in_words_inr(2850.50))->toBe('Rupees Two Thousand Eight Hundred Fifty and Fifty Paise Only');
});
