<?php

namespace App\Controllers\Api;

trait NormalizesPhoneNumbers
{
    protected function normalizeInternationalPhone(string $value): ?string
    {
        $phone = trim($value);

        if ($phone === '' || preg_match('/\p{L}/u', $phone)) {
            return null;
        }

        $hasLeadingPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/\D+/', '', $phone);

        if (! is_string($digits) || $digits === '') {
            return null;
        }

        return $hasLeadingPlus ? '+' . $digits : $digits;
    }
}
