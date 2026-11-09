<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Generic HTTP SMS driver for Bangladeshi bulk-SMS providers that accept
 * api_key / senderid / number / message parameters (the common BD format).
 * Adjust the payload keys here if your provider differs.
 */
class HttpSmsGateway implements SmsGateway
{
    public function __construct(
        private readonly string $url,
        private readonly string $apiKey,
        private readonly string $senderId,
    ) {}

    public function send(string $mobile, string $message): void
    {
        $response = Http::asForm()->timeout(10)->post($this->url, [
            'api_key' => $this->apiKey,
            'senderid' => $this->senderId,
            'number' => '88'.$mobile,
            'message' => $message,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('SMS provider rejected the message: HTTP '.$response->status());
        }
    }
}
