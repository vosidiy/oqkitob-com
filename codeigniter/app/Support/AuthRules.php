<?php

namespace App\Support;

final class AuthRules
{
    public const MIN_PASSWORD_LENGTH = 5;

    public static function validateNewPassword(string $password, ?string $confirmation = null): ?string
    {
        if ($password === '') {
            return 'Password is required.';
        }

        if (mb_strlen($password) > 255) {
            return 'Password must be 255 characters or fewer.';
        }

        if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return sprintf('Password must be at least %d characters.', self::MIN_PASSWORD_LENGTH);
        }

        if ($confirmation !== null && $password !== $confirmation) {
            return 'Password confirmation does not match.';
        }

        return null;
    }
}
