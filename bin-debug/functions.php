<?php
declare(strict_types=1);

use Ramsey\Uuid\Uuid;

function multi_strrchr(string $haystack, array $needles): string
{
    foreach ($needles as $needle) {
        $string = strrchr($haystack, $needle);
        if (is_string($string)) {
            return $string;
        }
    }

    return '';
}

function str_trim(string $string): string
{
    $pattern     = '/[\s\t\n\r\0\x0B]+/m';
    $replacement = ' ';

    $string = preg_replace($pattern, $replacement, $string);
    $string = trim($string);

    return $string;
}

function uuid(string $quote): string
{
    $name = strtolower($quote);
    $name = preg_replace('/[^a-z]/', null, $name);

    $uuid5 = Uuid::uuid5(Uuid::NIL, $name);

    return strtolower($uuid5->toString());
}
