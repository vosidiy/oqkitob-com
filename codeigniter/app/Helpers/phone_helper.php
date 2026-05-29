<?php

if (! function_exists('normalize_phone_e164')) {
    /**
     * Normalize user input into canonical E.164 storage format.
     *
     * We accept light formatting such as spaces, dashes, and parentheses, but
     * require an international number that starts with "+".
     */
    function normalize_phone_e164(string $value): ?string
    {
        $phone = trim($value);

        if ($phone === '') {
            return null;
        }

        if (! preg_match('/^\+[0-9\s\-\(\)]+$/', $phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (! is_string($digits) || strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        if ($digits[0] === '0') {
            return null;
        }

        return '+' . $digits;
    }
}
