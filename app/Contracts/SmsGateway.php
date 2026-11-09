<?php

namespace App\Contracts;

interface SmsGateway
{
    /**
     * Send a text message to a Bangladeshi mobile number (01XXXXXXXXX).
     *
     * @throws \RuntimeException when the provider rejects the message
     */
    public function send(string $mobile, string $message): void;
}
