<?php

namespace App\Services;

use App\Contracts\SmsGateway;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public const TTL_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public function __construct(private readonly SmsGateway $sms) {}

    public function send(string $mobile, string $purpose): void
    {
        OtpCode::where('mobile', $mobile)->where('purpose', $purpose)->whereNull('used_at')->delete();

        $code = (string) random_int(100000, 999999);

        OtpCode::create([
            'mobile' => $mobile,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        $this->sms->send($mobile, "RoktoDoot: apnar verification code {$code}. ".self::TTL_MINUTES.' minute por meyad shesh hobe.');
    }

    /** Returns true and consumes the code when it matches. */
    public function verify(string $mobile, string $purpose, string $code): bool
    {
        $otp = OtpCode::where('mobile', $mobile)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp || $otp->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            return false;
        }

        $otp->update(['used_at' => now()]);

        return true;
    }
}
