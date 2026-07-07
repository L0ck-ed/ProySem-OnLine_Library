<?php

namespace App\Helpers;

class Sanitizer
{
    public static function text(?string $value): string
    {
        return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
