<?php

namespace App\Support;

/**
 * One wa.me builder for the office number and a listing's contact, so both
 * accept the same input and agree on when a number is too short to use.
 */
class WhatsApp
{
    /**
     * Stored as 08…; wa.me wants 62…. Null when the digits cannot be a phone
     * number, so a blank or a stray "123" never becomes a broken link.
     */
    public static function number(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return strlen($digits) >= 10 ? $digits : null;
    }

    public static function url(?string $value, ?string $message = null): ?string
    {
        $number = self::number($value);

        if ($number === null) {
            return null;
        }

        return 'https://wa.me/'.$number.($message === null ? '' : '?text='.rawurlencode($message));
    }
}
