<?php
declare(strict_types=1);

function slugify(string $text): string {
    $map = [
        'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o',
        'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u', 'ä' => 'a', 'Ä' => 'a', 'å' => 'a', 'Å' => 'a',
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'í' => 'i',
        'î' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'û' => 'u', 'ñ' => 'n', 'ß' => 'ss',
        'æ' => 'ae', 'Æ' => 'ae', 'ø' => 'o', 'Ø' => 'o', 'œ' => 'oe', 'Œ' => 'oe',
    ];
    $text = strtolower(strtr($text, $map));
    $text = (string) preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim(substr($text, 0, 80), '-');
}
