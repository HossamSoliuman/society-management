<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;

/**
 * In-memory driver for tests: records every message instead of sending.
 */
class FakeSmsGateway implements SmsGateway
{
    /** @var array<int, array{mobile: string, message: string}> */
    public array $sent = [];

    public function send(string $mobile, string $message): bool
    {
        $this->sent[] = ['mobile' => $mobile, 'message' => $message];

        return true;
    }

    public function sentTo(string $mobile): bool
    {
        return collect($this->sent)->contains(fn (array $sms) => $sms['mobile'] === $mobile);
    }
}
