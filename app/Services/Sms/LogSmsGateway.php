<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;

/**
 * Default driver: writes outbound SMS to the log so flows can be exercised
 * without a provider account. Bind a real driver in AppServiceProvider.
 */
class LogSmsGateway implements SmsGateway
{
    public function send(string $mobile, string $message): bool
    {
        Log::channel(config('services.sms.log_channel', config('logging.default')))
            ->info('SMS to '.$mobile.': '.$message);

        return true;
    }
}
