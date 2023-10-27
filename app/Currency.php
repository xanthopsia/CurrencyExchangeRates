<?php

namespace App;
class Currency
{
    private string $isoCode;

    public function __construct(string $isoCode)
    {
        $this->isoCode = $isoCode;
    }

    public static function isValidIsoCode(string $isoCode, array &$isoCodes): bool
    {
        return array_key_exists($isoCode, $isoCodes);
    }

    public function getIsoCode(): string
    {
        return $this->isoCode;
    }

}
