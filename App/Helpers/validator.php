<?php

namespace App\Helpers;

class Validator
{
    public static function required(string $value): bool
    {
        return trim($value) !== '';
    }

    public static function min(string $value, int $length): bool
    {
        return mb_strlen(trim($value)) >= $length;
    }
}
