<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;

/**
 * Development driver: writes messages to the log instead of sending them.
 * Production must set SMS_DRIVER=http with real provider credentials.
 */
class LogSmsGateway implements SmsGateway
{
    public function send(string $mobile, string $message): void
    {
        Log::channel(config('logging.default'))->info('SMS (log driver)', [
            'to' => $mobile,
            'message' => $message,
        ]);
    }
}
