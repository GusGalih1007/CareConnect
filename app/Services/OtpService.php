<?php

namespace App\Services;

use App\Enum\OtpType;
use App\Models\OtpCode;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public function generate(
        Authenticatable $user,
        OtpType $otpType
    ): string {
        $otp = (string) random_int(100000, 999999);

        OtpCode::where('user_id', $user->getAuthIdentifier())
            ->where('code_type', $otpType)
            ->whereNull('used_at')
            ->delete();

        OtpCode::create([
            'user_id' => $user->getAuthIdentifier(),
            'code_type' => $otpType,
            'code_hash' => Hash::make($otp),
            'expires_at' => now()->addSeconds($otpType->ttl()),
        ]);

        return $otp;
    }

    public function verify(
        Authenticatable $user,
        OtpType $otpType,
        string $inputOtp
    ): bool {
        $otp = OtpCode::where('user_id', $user->getAuthIdentifier())
            ->where('code_type', $otpType)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otp) {
            return false;
        }

        if ($otp->isExpired()) {
            return false;
        }

        if ($otp->attempts >= $otpType->maxAttempts()) {
            return false;
        }

        $otp->increment('attempts');

        if (!Hash::check($inputOtp, $otp->code_hash)) {
            return false;
        }

        $otp->update([
            'used_at' => now(),
        ]);

        return true;
    }
}
