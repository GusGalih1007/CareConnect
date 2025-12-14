<?php

namespace App\Enum;

enum OtpType:string
{
    case Login = 'login';
    case Register = 'register';
    case ResetPassword = 'reset_password';
    case SensitiveAction = 'sensitive_action';

    public function ttl(): int
    {
        return match ($this) {
            self::Login => 300,
            self::Register => 600,
            self::ResetPassword => 300,
            self::SensitiveAction => 180,
        };
    }

    public function maxAttempts(): int
    {
        return match ($this) {
            self::Login => 5,
            self::Register => 5,
            self::ResetPassword => 3,
            self::SensitiveAction => 3,
        };
    }
}
