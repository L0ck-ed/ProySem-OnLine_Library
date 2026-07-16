<?php

declare(strict_types=1);

/* Compatibilidad mínima cuando la extensión mbstring no está habilitada. */
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $string, ?string $encoding = null): int
    {
        return strlen($string);
    }
}
